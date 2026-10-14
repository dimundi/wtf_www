// Katalog: zaznaczenie aktualnie oglądanego tytułu w bocznym menu.
(() => {
  const menu = document.querySelector('.catalog-portfolio-menu');
  if (!menu) return;
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

