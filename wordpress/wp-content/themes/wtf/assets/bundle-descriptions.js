(() => {
  const mobile = matchMedia('(max-width:760px)');
  document.querySelectorAll('.wtf-bundle-component-detail').forEach((detail) => {
    const body = detail.querySelector('.wtf-bundle-component-body');
    const button = detail.querySelector('.wtf-bundle-expand');
    const content = detail.querySelector('.wtf-bundle-component-text');
    if (!body || !button || !content) return;
    let previous;
    const update = () => {
      // Po skróceniu nie wyłączamy przycisku na podstawie zmienionego układu.
      const collapsible = mobile.matches && (previous === true || content.getBoundingClientRect().height > 260);
      if (collapsible === previous) return;
      previous = collapsible;
      detail.classList.toggle('is-collapsible', collapsible);
      button.hidden = !collapsible;
    };
    button.addEventListener('click', () => {
      const expanded = button.getAttribute('aria-expanded') !== 'true';
      button.setAttribute('aria-expanded', String(expanded));
      button.textContent = expanded ? 'Zwiń' : 'Rozwiń';
      detail.classList.toggle('is-expanded', expanded);
    });
    body.addEventListener('focusin', () => {
      detail.classList.add('is-expanded');
      button.setAttribute('aria-expanded', 'true');
      button.textContent = 'Zwiń';
    });
    mobile.addEventListener('change', update);
    new ResizeObserver(update).observe(content);
    body.querySelectorAll('img').forEach((img) => img.addEventListener('load', update));
    update();
  });
})();
