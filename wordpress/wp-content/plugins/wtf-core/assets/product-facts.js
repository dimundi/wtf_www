(() => {
  const items = document.querySelectorAll('.wtf-product-fact');
  const positionTip = item => {
    const tip = item.querySelector('.wtf-product-fact__tip');
    tip.style.transform = '';
    const rect = tip.getBoundingClientRect();
    const edge = document.documentElement.clientWidth - 8;
    const shift = rect.left < 8 ? 8 - rect.left : rect.right > edge ? edge - rect.right : 0;
    tip.style.transform = `translateX(${shift}px)`;
  };
  const setOpen = (item, open) => {
    item.querySelector('.wtf-product-fact__tip').hidden = !open;
    item.querySelector('button').setAttribute('aria-expanded', String(open));
    if (open) positionTip(item);
  };
  const closeAll = () => items.forEach(item => setOpen(item, false));
  items.forEach(item => {
    const button = item.querySelector('button');
    item.addEventListener('pointerenter', event => {
      if (event.pointerType === 'mouse') { closeAll(); setOpen(item, true); }
    });
    item.addEventListener('pointerleave', () => {
      if (!button.matches(':focus-visible')) setOpen(item, false);
    });
    button.addEventListener('focus', () => {
      if (button.matches(':focus-visible')) { closeAll(); setOpen(item, true); }
    });
    button.addEventListener('blur', () => setOpen(item, false));
    button.addEventListener('click', () => {
      const open = button.getAttribute('aria-expanded') !== 'true';
      closeAll(); setOpen(item, open);
    });
  });
  document.addEventListener('keydown', event => { if (event.key === 'Escape') closeAll(); });
  document.addEventListener('click', event => { if (!event.target.closest('.wtf-product-fact')) closeAll(); });
  const updateRows = group => {
    let top;
    group.querySelectorAll('.wtf-product-fact').forEach(item => {
      const rowTop = item.offsetTop;
      item.classList.toggle('is-row-start', top === undefined || rowTop !== top);
      top = rowTop;
      if (item.querySelector('button').getAttribute('aria-expanded') === 'true') positionTip(item);
    });
  };
  const groups = document.querySelectorAll('.wtf-product-facts[role="group"]');
  const observer = new ResizeObserver(entries => entries.forEach(entry => updateRows(entry.target)));
  groups.forEach(group => { updateRows(group); observer.observe(group); });
})();
