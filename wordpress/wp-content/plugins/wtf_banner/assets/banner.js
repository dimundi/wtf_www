(() => {
  document.querySelectorAll('[data-copy-code]').forEach(button => {
    button.hidden = false;
    let timer;
    button.addEventListener('click', async () => {
      const status = button.parentElement.querySelector('[role="status"]');
      try {
        await navigator.clipboard.writeText(button.dataset.copyCode);
        clearTimeout(timer);
        button.classList.add('is-copied');
        button.title = button.dataset.copiedLabel;
        status.textContent = button.dataset.copiedLabel;
        timer = setTimeout(() => {
          button.classList.remove('is-copied');
          button.title = button.dataset.copyLabel;
          status.textContent = '';
        }, 2500);
      } catch {
        status.textContent = button.dataset.copyError;
        button.title = button.dataset.copyError;
      }
    });
  });
})();
