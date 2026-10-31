// Na telefonach ilustracja i logo zamykają pierwszy ekran.
(() => {
  const opening = document.querySelector('.dust-opening');
  const logo = opening?.querySelector('.dust-logo');
  const art = opening?.querySelector('.dust-opening-art');
  if (!logo || !art) return;
  const mobile = matchMedia('(max-width: 650px)');
  const logoPlace = document.createComment('logo position');
  const artPlace = document.createComment('art position');
  logo.before(logoPlace); art.before(artPlace);
  const stage = document.createElement('div');
  stage.className = 'dust-mobile-brand-stage';
  function measure() {
    if (!stage.isConnected) return;
    const top = stage.getBoundingClientRect().top + window.scrollY;
    stage.style.setProperty('--dust-stage-top', `${Math.max(0, top)}px`);
  }
  function sync() {
    if (mobile.matches) {
      if (!stage.isConnected) { opening.prepend(stage); stage.append(art, logo); }
      measure();
    } else {
      logoPlace.after(logo); artPlace.after(art); stage.remove();
    }
  }
  mobile.addEventListener('change', sync);
  window.addEventListener('resize', measure, { passive: true });
  window.addEventListener('load', measure, { once: true });
  if (document.fonts) document.fonts.ready.then(measure);
  sync();
})();

// Menu sekcji Kurzołaków: lista rozwijana i powrót na górę na telefonach.
(() => {
  const menu = document.querySelector('.dust-local-menu');
  if (!menu) return;
  const sidebar = document.createElement('div'); sidebar.className='dust-local-menu-shell'; menu.before(sidebar);
  const compact = window.matchMedia('(max-width: 700px)');
  const details = sidebar.querySelector('.dust-section-menu') || document.createElement('details');
  details.className = 'dust-section-menu';
  const summary = details.querySelector('summary') || document.createElement('summary');
  summary.textContent = document.documentElement.lang.startsWith('en') ? 'Explore Dustlings' : 'Odkrywaj Kurzołaki';
  if (!details.parentElement) sidebar.append(details);
  details.append(summary, menu);
  const top = sidebar.querySelector('.dust-back-to-top') || document.createElement('button');
  top.type = 'button';
  top.className = 'dust-back-to-top';
  top.setAttribute('aria-label', document.documentElement.lang.startsWith('en') ? 'Back to top' : 'Wróć na górę');
  top.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m6 12 6-6 6 6M12 6v13"></path></svg>';
  sidebar.prepend(top);
  const sync = () => { details.open = !compact.matches; };
  compact.addEventListener('change', sync);
  sync();
  details.classList.add('is-ready');
  top.addEventListener('click', () => {
    details.open = false;
    window.scrollTo({top: 0, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'});
  });
  menu.addEventListener('click', event => {
    const link = event.target.closest('a[href^="#"]');
    if (!compact.matches || !link) return;
    const target = document.querySelector(link.getAttribute('href'));
    if (!target) return;
    event.preventDefault();
    summary.focus({preventScroll: true});
    details.open = false;
    target.scrollIntoView({behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'});
    history.replaceState(null, '', link.getAttribute('href'));
  });
  document.addEventListener('click', event => {
    if (compact.matches && !sidebar.contains(event.target)) details.open = false;
  });
  document.addEventListener('keydown', event => {
    if (compact.matches && event.key === 'Escape') { details.open = false; summary.focus(); }
  });
  const links = [...menu.querySelectorAll('a[href^="#"]')];
  const targets = links.map(link => document.querySelector(link.getAttribute('href')));
  let scheduled = false;
  function update() {
    scheduled = false;
    let active = 0;
    targets.forEach((target, index) => {
      if (target && target.getBoundingClientRect().top <= 160) active = index;
    });
    links.forEach((link, index) => {
      if (index === active) link.setAttribute('aria-current', 'location');
      else link.removeAttribute('aria-current');
    });
  }
  window.addEventListener('scroll', () => {
    if (!scheduled) { scheduled = true; requestAnimationFrame(update); }
  }, { passive: true });
  update();
})();

