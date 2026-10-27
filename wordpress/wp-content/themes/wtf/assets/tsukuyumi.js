(() => {
  const sectionMenu = document.querySelector('.elekt-section-menu, .tsu-section-menu');
  document.querySelector('.elekt-back-to-top, .tsu-back-to-top, .klan-back-to-top')?.addEventListener('click', () => {
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
