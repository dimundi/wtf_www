(() => {
  'use strict';
  const config = window.wtfCookiesConfig;
  if (!config) return;
  const keys = Object.keys(config.categories);
  const services = new Map();
  const empty = () => Object.fromEntries(keys.map(key => [key, false]));
  let state = empty();
  let chosen = false;
  let banner, dialog, returnFocus;
  let storageError = false;

  function readChoice() {
    try {
      const raw = document.cookie.split('; ').find(row => row.startsWith(config.cookie + '='));
      if (!raw) return null;
      const value = JSON.parse(decodeURIComponent(raw.slice(config.cookie.length + 1)));
      const age = Date.now() - value.at;
      if (value.revision !== config.revision || !Number.isFinite(value.at) || age < 0 || age > config.days * 86400000
          || !value.categories || keys.some(key => typeof value.categories[key] !== 'boolean')) return null;
      return Object.fromEntries(keys.map(key => [key, value.categories[key]]));
    } catch { return null; }
  }

  const stored = readChoice();
  if (stored) { state = stored; chosen = true; }

  function syncMedia() {
    document.querySelectorAll('[data-wtfc-media]').forEach(host => {
      const frame = host.querySelector('iframe');
      const notice = host.querySelector('.wtfc-media-notice');
      if (state.media && !frame) {
        const url = new URL(host.dataset.wtfcSrc, location.href);
        if (url.origin !== 'https://www.youtube-nocookie.com' || !/^\/embed\/[\w-]+$/.test(url.pathname)) return;
        const element = document.createElement('iframe');
        element.src = url.href;
        element.title = host.dataset.wtfcTitle || 'Film YouTube';
        element.loading = 'lazy';
        element.allow = 'encrypted-media; picture-in-picture; fullscreen';
        element.allowFullscreen = true;
        element.referrerPolicy = 'strict-origin-when-cross-origin';
        host.append(element);
      } else if (!state.media && frame) frame.remove();
      if (notice) notice.hidden = !!state.media;
    });
  }

  function apply(previous) {
    syncMedia();
    services.forEach(service => {
      const enabled = !!state[service.category];
      if (enabled === service.active) return;
      service.active = enabled;
      try { if (enabled) service.start(); else service.stop(); }
      catch (error) { console.error('WTF Cookies: błąd integracji', error); }
    });
    window.dispatchEvent(new CustomEvent('wtf:cookies-change', {detail: {categories: {...state}, previous: {...previous}}}));
  }

  function choose(next) {
    const previous = {...state};
    state = Object.fromEntries(keys.map(key => [key, next[key] === true]));
    chosen = true;
    try {
      const value = encodeURIComponent(JSON.stringify({revision:config.revision, at:Date.now(), categories:state}));
      document.cookie = `${config.cookie}=${value}; Max-Age=${config.days * 86400}; Path=/; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
      storageError = readChoice() === null;
    } catch { storageError = true; }
    if (banner) banner.hidden = !storageError;
    const error = document.getElementById('wtfc-storage-error');
    if (error) error.hidden = !storageError;
    closeSettings();
    apply(previous);
  }

  function openSettings(trigger) {
    if (!dialog) return;
    returnFocus = trigger instanceof HTMLElement ? trigger : document.activeElement;
    dialog.querySelectorAll('[data-wtfc-category]').forEach(input => { input.checked = !!state[input.dataset.wtfcCategory]; });
    if (!dialog.open) dialog.showModal();
  }

  function closeSettings() {
    if (!dialog?.open) return;
    dialog.close();
  }

  // Public extension API. A service must stop its own timers/listeners and remove its cookies on withdrawal.
  window.wtfCookies = Object.freeze({
    hasConsent: category => state[category] === true,
    getConsent: () => ({chosen, categories:{...state}, revision:config.revision}),
    open: () => openSettings(),
    registerService(id, service) {
      if (!keys.includes(service.category) || typeof service.start !== 'function' || typeof service.stop !== 'function' || services.has(id)) throw new Error('Nieprawidłowa integracja WTF Cookies');
      const entry = {...service, active:false};
      services.set(id, entry);
      if (state[entry.category]) { entry.active = true; entry.start(); }
    },
  });

  let inpostLoading;
  function loadInpost() {
    if (customElements.get('inpost-geowidget')) return Promise.resolve();
    if (inpostLoading) return inpostLoading;
    inpostLoading = new Promise((resolve, reject) => {
      const source = document.querySelector('[data-wtfc-inpost-src]');
      if (!source) { reject(new Error('Brak SDK mapy')); return; }
      document.querySelectorAll('[data-wtfc-inpost-css]').forEach(template => {
        if (template.dataset.loaded) return;
        const link = document.createElement('link');
        link.rel = 'stylesheet'; link.href = template.dataset.wtfcInpostCss;
        document.head.append(link); template.dataset.loaded = '1';
      });
      const script = document.createElement('script');
      script.src = source.dataset.wtfcInpostSrc;
      const timer = setTimeout(() => { script.remove(); reject(new Error('Przekroczono czas ładowania mapy')); }, 20000);
      script.onload = () => { clearTimeout(timer); resolve(); };
      script.onerror = () => { clearTimeout(timer); script.remove(); reject(new Error('Nie udało się pobrać mapy')); };
      document.head.append(script);
    }).catch(error => { inpostLoading = null; throw error; });
    return inpostLoading;
  }

  document.addEventListener('click', event => {
    const target = event.target instanceof Element ? event.target.closest('button, a') : null;
    if (!target) return;
    if (target.id === 'wtf-open-locker-map' && !target.dataset.wtfcReady && document.querySelector('[data-wtfc-inpost-src]')) {
      event.preventDefault(); event.stopImmediatePropagation();
      target.disabled = true;
      const label = target.textContent;
      target.textContent = 'Wczytywanie mapy…';
      loadInpost().then(() => {
        target.dataset.wtfcReady = '1'; target.disabled = false; target.textContent = label; target.click();
      }).catch(() => {
        target.disabled = false; target.textContent = label;
        let message = document.getElementById('wtfc-map-error');
        if (!message) { message = document.createElement('p'); message.id = 'wtfc-map-error'; message.setAttribute('role', 'alert'); target.after(message); }
        message.textContent = 'Nie udało się wczytać mapy. Spróbuj ponownie.';
      });
      return;
    }
    if (target.matches('[data-wtfc-open]')) { event.preventDefault(); openSettings(target); }
    else if (target.matches('[data-wtfc-close]')) closeSettings();
    else if (target.matches('[data-wtfc-accept]')) choose(Object.fromEntries(keys.map(key => [key, true])));
    else if (target.matches('[data-wtfc-reject]')) choose(empty());
    else if (target.matches('[data-wtfc-save]')) {
      const next = empty();
      dialog.querySelectorAll('[data-wtfc-category]').forEach(input => { next[input.dataset.wtfcCategory] = input.checked; });
      choose(next);
    } else if (target.matches('[data-wtfc-media-allow]')) choose({...state, media:true});
  }, true);

  function ready() {
    banner = document.getElementById('wtfc-banner');
    dialog = document.getElementById('wtfc-settings');
    if (banner) banner.hidden = chosen;
    dialog?.addEventListener('close', () => {
      if (returnFocus?.isConnected && !returnFocus.closest('[hidden]')) returnFocus.focus();
    });
    if (!document.querySelector('.wtfc-footer-link')) {
      const button = document.createElement('button');
      button.type = 'button'; button.className = 'wtfc-footer-link'; button.dataset.wtfcOpen = '';
      button.textContent = 'Ustawienia cookies';
      (document.querySelector('footer') || document.body).append(button);
    }
    syncMedia();
    new MutationObserver(records => {
      if (records.some(record => [...record.addedNodes].some(node => node instanceof Element && (node.matches('[data-wtfc-media]') || node.querySelector('[data-wtfc-media]'))))) syncMedia();
    }).observe(document.body, {childList:true, subtree:true});
    window.dispatchEvent(new CustomEvent('wtf:cookies-ready', {detail: window.wtfCookies.getConsent()}));
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready, {once:true});
  else ready();
  // Recheck after restoring a page from the back/forward cache; withdrawn consent must not resurrect a frame.
  function refreshChoice() {
    const choice = readChoice();
    if (choice || chosen) {
      const previous = {...state}; state = choice || empty(); chosen = !!choice;
      if (banner) banner.hidden = chosen;
      apply(previous);
    }
  }
  window.addEventListener('pageshow', refreshChoice);
  window.addEventListener('focus', refreshChoice);
})();
