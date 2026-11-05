document.addEventListener('click', (event) => {
    const link = event.target.closest('[data-wtfg-import-toggle]');
    if (!link) return;
    event.preventDefault();
    const panel = document.getElementById(link.getAttribute('aria-controls'));
    if (!panel) return;
    panel.hidden = !panel.hidden;
    link.setAttribute('aria-expanded', String(!panel.hidden));
});

document.addEventListener('change', (event) => {
    const form = event.target.closest('form');
    if (!form) return;
    const all = form.querySelector('[data-wtfg-select-all]');
    if (!all) return;
    const boxes = [...form.querySelectorAll('input[name="approve[]"]')];
    if (event.target === all) {
        boxes.forEach((box) => { box.checked = all.checked; });
    } else if (!boxes.includes(event.target)) {
        return;
    }
    const count = boxes.filter((box) => box.checked).length;
    all.checked = boxes.length > 0 && count === boxes.length;
    all.indeterminate = count > 0 && count < boxes.length;
});
