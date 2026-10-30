document.addEventListener('DOMContentLoaded', () => {
  const panel = document.querySelector('.wtf-bundle-config');
  if (!panel) return;
  const kind = document.querySelector('#wtf_product_kind');
  const update = () => { panel.hidden = kind?.value !== 'zestaw'; };
  kind?.addEventListener('change', update);
  update();
  let index = panel.querySelectorAll('tbody tr').length;
  panel.addEventListener('click', (event) => {
    if (event.target.closest('[data-bundle-remove]')) event.target.closest('tr').remove();
    if (event.target.closest('[data-bundle-add]')) {
      const html = panel.querySelector('template').innerHTML.replaceAll('__INDEX__', String(index++));
      panel.querySelector('tbody').insertAdjacentHTML('beforeend', html);
    }
  });
});
