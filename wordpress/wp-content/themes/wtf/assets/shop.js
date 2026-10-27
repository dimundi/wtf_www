document.querySelectorAll('[data-shop-filters]').forEach(form => {
  form.querySelectorAll('input[name="game"]').forEach(input => {
    input.addEventListener('change', () => form.requestSubmit());
  });
});
document.querySelector('#shop-sort')?.addEventListener('change', event => {
  event.target.form?.requestSubmit();
});
document.querySelectorAll('.shop-page.has-game').forEach(page => {
  const nav = page.querySelector('.shop-game-nav');
  const toolbar = page.querySelector('.shop-results-toolbar');
  if (!nav || !toolbar) return;
  const select = page.querySelector('.shop-section-select') || document.createElement('select');
  select.className = 'shop-section-select';
  select.setAttribute('aria-label', 'Sekcja oferty');
  if (!select.options.length) nav.querySelectorAll('a[href^="#"]').forEach(link => {
    const label = link.cloneNode(true);
    label.querySelector('span')?.remove();
    select.add(new Option(label.textContent.trim(), link.getAttribute('href')));
  });
  if (!select.options.length) return;
  select.addEventListener('change', () => {
    document.getElementById(select.value.slice(1))?.scrollIntoView({
      behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
      block: 'start'
    });
  });
  toolbar.append(select);
  page.classList.add('has-section-select');
});
