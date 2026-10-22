document.querySelectorAll('[data-shop-filters]').forEach(form => {
  form.querySelectorAll('input[name="game"]').forEach(input => {
    input.addEventListener('change', () => form.requestSubmit());
  });
});
document.querySelector('#shop-sort')?.addEventListener('change', event => {
  event.target.form?.requestSubmit();
});
