// Katalog: zaznaczenie aktualnie oglądanego tytułu w bocznym menu.
(() => {
  const menu = document.querySelector('.catalog-portfolio-menu');
  if (!menu) return;
  const sidebar = menu.closest('.catalog-sidebar');
  const compact = window.matchMedia('(max-width: 600px)');
  const details = sidebar.querySelector('.catalog-section-menu') || document.createElement('details');
  details.className = 'catalog-section-menu';
  const summary = details.querySelector('summary') || document.createElement('summary');
  summary.textContent = document.documentElement.lang.startsWith('en') ? 'Games and publications' : 'Gry i publikacje';
  if (!details.parentElement) sidebar.insertBefore(details, menu);
  details.append(summary, menu);
  const top = sidebar.querySelector('.catalog-back-to-top') || document.createElement('button');
  top.type = 'button';
  top.className = 'catalog-back-to-top';
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

