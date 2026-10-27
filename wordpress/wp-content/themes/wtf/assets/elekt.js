(() => {
  const sectionMenu = document.querySelector('.elekt-section-menu, .tsu-section-menu');
  document.querySelector('.elekt-back-to-top, .tsu-back-to-top')?.addEventListener('click', () => {
    if (sectionMenu && matchMedia('(max-width: 1100px)').matches) sectionMenu.open = false;
    window.scrollTo({top:0, behavior:matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'});
  });
  if (sectionMenu) {
    sectionMenu.querySelectorAll('.tsu-submenu-toggle, .elekt-submenu-toggle').forEach(button => {
      const submenu = document.getElementById(button.getAttribute('aria-controls'));
      button.addEventListener('click', () => {
        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!expanded));
        submenu.hidden = expanded;
      });
    });
    const compactMenu = window.matchMedia('(max-width: 1100px)');
    const sidebar = sectionMenu.closest('.elekt-sidebar');
    const layout = sidebar?.closest('.elekt-reading-layout');
    const spacer = document.createElement('div');
    spacer.hidden = true;
    spacer.setAttribute('aria-hidden', 'true');
    if (sidebar) sidebar.before(spacer);
    const updatePinnedMenu = () => {
      if (!sidebar || !layout) return;
      const pinned = sidebar.classList.contains('is-mobile-pinned');
      const anchor = (pinned ? spacer : sidebar).getBoundingClientRect();
      const shouldPin = compactMenu.matches && anchor.top <= 8 && layout.getBoundingClientRect().bottom > sidebar.offsetHeight + 8;
      if (shouldPin) {
        if (!pinned) {
          spacer.style.height = sidebar.offsetHeight + 'px';
          spacer.hidden = false;
          sidebar.style.setProperty('--elekt-menu-left', anchor.left + 'px');
          sidebar.style.setProperty('--elekt-menu-width', anchor.width + 'px');
          sidebar.classList.add('is-mobile-pinned');
        }
        const rect = spacer.getBoundingClientRect();
        sidebar.style.setProperty('--elekt-menu-left', rect.left + 'px');
        sidebar.style.setProperty('--elekt-menu-width', rect.width + 'px');
        sidebar.classList.add('is-mobile-pinned');
      } else {
        sidebar.classList.remove('is-mobile-pinned');
        spacer.hidden = true;
        sidebar.style.removeProperty('--elekt-menu-left');
        sidebar.style.removeProperty('--elekt-menu-width');
      }
    };
    const syncMenu = () => {
      sectionMenu.open = !compactMenu.matches;
      sectionMenu.querySelectorAll('.elekt-submenu-toggle, .tsu-submenu-toggle').forEach(button => {
        button.setAttribute('aria-expanded', String(!compactMenu.matches));
        document.getElementById(button.getAttribute('aria-controls')).hidden = compactMenu.matches;
      });
    };
    syncMenu();
    sectionMenu.classList.add('is-ready');
    compactMenu.addEventListener('change', syncMenu);
    const links = [...sectionMenu.querySelectorAll('nav a')];
    const sections = links.map(link => document.querySelector(link.hash));
    let scheduled = false;
    const updateSection = () => {
      updatePinnedMenu();
      const marker = window.innerHeight * 0.3;
      let active = 0;
      sections.forEach((section, index) => {
        if (section && section.getBoundingClientRect().top <= marker) active = index;
      });
      links.forEach((link, index) => {
        if (index === active) link.setAttribute('aria-current', 'location');
        else link.removeAttribute('aria-current');
      });
      if (links[active].closest('.elekt-expansion-submenu')) {
        sectionMenu.querySelector('a[href="#elekt-expansions"]').setAttribute('aria-current', 'location');
      }
      if (links[active].closest('.tsu-faction-submenu')) {
        sectionMenu.querySelector('a[href="#tsu-factions"]').setAttribute('aria-current', 'location');
      }
      if (links[active].closest('.tsu-expansion-submenu')) {
        sectionMenu.querySelector('a[href="#tsu-expansions"]').setAttribute('aria-current', 'location');
      }
      scheduled = false;
    };
    const scheduleUpdate = () => {
      if (!scheduled) { scheduled = true; requestAnimationFrame(updateSection); }
    };
    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate);
    links.forEach(link => link.addEventListener('click', () => {
      if (compactMenu.matches) sectionMenu.open = false;
    }));
    document.addEventListener('click', event => {
      if (compactMenu.matches && !sectionMenu.contains(event.target)) sectionMenu.open = false;
    });
    sectionMenu.addEventListener('keydown', event => {
      if (event.key === 'Escape' && compactMenu.matches) {
        sectionMenu.open = false;
        sectionMenu.querySelector('summary').focus();
      }
    });
    updateSection();
  }
})();

// Native scrolling keeps the card gallery usable without JavaScript as well.
document.querySelectorAll('.elekt-video-preview').forEach(preview => {
  preview.addEventListener('click', event => {
    event.preventDefault();
    const frame = document.createElement('iframe');
    frame.src = preview.dataset.elektVideo + '?autoplay=1';
    frame.title = preview.getAttribute('aria-label').replace(/^Odtwórz: /, '');
    frame.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
    frame.allowFullscreen = true;
    preview.replaceWith(frame);
    frame.focus();
  });
});
document.querySelectorAll('.elekt-card-carousel').forEach(gallery => {
  const track = gallery.querySelector('.elekt-card-carousel-track');
  if (!track || gallery.querySelector('.elekt-card-carousel-controls')) return;
  const cards = [...track.children];
  track.tabIndex = 0;
  const controls = document.createElement('div');
  controls.className = 'elekt-card-carousel-controls';
  const stage = document.createElement('div');
  stage.className = 'elekt-card-carousel-stage';
  const previous = document.createElement('button');
  const next = document.createElement('button');
  const status = document.createElement('span');
  previous.type = next.type = 'button';
  previous.textContent = '‹';
  next.textContent = '›';
  previous.className = 'elekt-card-carousel-arrow';
  next.className = 'elekt-card-carousel-arrow';
  previous.setAttribute('aria-label', 'Poprzednia karta');
  next.setAttribute('aria-label', 'Następna karta');
  previous.setAttribute('aria-controls', track.id);
  next.setAttribute('aria-controls', track.id);
  status.setAttribute('role', 'status');
  status.setAttribute('aria-live', 'polite');
  status.className = 'elekt-card-carousel-status';
  const indicators = document.createElement('div');
  indicators.className = 'elekt-card-carousel-indicators';
  indicators.setAttribute('role', 'group');
  indicators.setAttribute('aria-label', 'Wybierz kartę');
  const behavior = () => matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth';
  const markers = cards.map((card, index) => {
    const button = document.createElement('button');
    button.type = 'button';
    const title = card.querySelector('figcaption')?.textContent || `Karta ${index + 1}`;
    button.setAttribute('aria-label', `Pokaż kartę ${index + 1}: ${title}`);
    button.setAttribute('aria-controls', track.id);
    button.addEventListener('click', () => {
      const left = track.scrollLeft + card.getBoundingClientRect().left - track.getBoundingClientRect().left;
      track.scrollTo({left, behavior: behavior()});
    });
    indicators.append(button);
    return button;
  });
  controls.append(indicators, status);
  track.before(stage);
  stage.append(previous, track, next);
  stage.after(controls);
  gallery.classList.add('is-enhanced');
  const update = () => {
    const gap = parseFloat(getComputedStyle(track).gap) || 0;
    const step = cards[0].getBoundingClientRect().width + gap;
    const bounds = track.getBoundingClientRect();
    const visibleCards = cards.map((card, index) => {
      const rect = card.getBoundingClientRect();
      const visible = Math.min(rect.right, bounds.right) - Math.max(rect.left, bounds.left) > rect.width / 2;
      markers[index].setAttribute('aria-pressed', String(visible));
      return visible ? index : -1;
    }).filter(index => index >= 0);
    const first = visibleCards[0] ?? Math.round(track.scrollLeft / step);
    const last = (visibleCards.at(-1) ?? first) + 1;
    status.textContent = first + 1 === last ? `${last} z ${cards.length}` : `${first + 1}–${last} z ${cards.length}`;
    previous.disabled = track.scrollLeft < 2;
    next.disabled = track.scrollLeft >= track.scrollWidth - track.clientWidth - 2;
  };
  const move = direction => {
    const gap = parseFloat(getComputedStyle(track).gap) || 0;
    track.scrollBy({left: direction * (cards[0].getBoundingClientRect().width + gap), behavior: behavior()});
  };
  previous.addEventListener('click', () => move(-1));
  next.addEventListener('click', () => move(1));
  let queued = false;
  track.addEventListener('scroll', () => {
    if (queued) return;
    queued = true;
    requestAnimationFrame(() => { update(); queued = false; });
  }, {passive: true});
  new ResizeObserver(update).observe(track);
  update();
});
