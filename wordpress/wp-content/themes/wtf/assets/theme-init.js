(() => {
  const root = document.documentElement;
  root.classList.add('wtf-js');
  const dev = document.currentScript?.dataset.devControls === '1';
  const fixed = document.currentScript?.dataset.fixedTheme;
  root.dataset.themeDev = dev ? '1' : '0';
  if (fixed === 'dark' || fixed === 'light') {
    root.dataset.themeSource = 'page';
    root.dataset.theme = fixed;
    return;
  }
  const preference = matchMedia('(prefers-color-scheme: dark)');
  let saved = null;
  if (dev) {
    try {
      const value = localStorage.getItem('wtf-theme');
      if (value === 'light' || value === 'dark') saved = value;
    } catch {}
  }
  root.dataset.themeSource = saved ? 'manual' : 'system';
  root.dataset.theme = saved || (preference.matches ? 'dark' : 'light');
  preference.addEventListener('change', () => {
    if (root.dataset.themeSource !== 'system') return;
    const theme = preference.matches ? 'dark' : 'light';
    root.dataset.theme = theme;
    window.dispatchEvent(new CustomEvent('wtf:themechange', {detail: {theme}}));
  });
})();
