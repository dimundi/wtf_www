(() => {
  const cards = [...document.querySelectorAll('.wtf-gamefound[data-wtfg-end]')];
  if (!cards.length) return;
  const update = () => cards.forEach(card => {
    const end = Number(card.dataset.wtfgEnd) * 1000;
    const ended = end > 0 && Date.now() >= end;
    card.querySelectorAll('[data-wtfg-state]').forEach(panel => {
      panel.hidden = (panel.dataset.wtfgState === 'ended') !== ended;
    });
    const countdown = card.querySelector('[data-wtfg-countdown]');
    if (!countdown) return;
    countdown.hidden = ended || !end;
    if (countdown.hidden) return;
    const seconds = Math.max(0, Math.ceil((end - Date.now()) / 1000));
    const days = Math.floor(seconds / 86400);
    const pad = n => String(n).padStart(2, '0');
    countdown.textContent = `Do końca: ${days} ${days === 1 ? 'dzień' : 'dni'} ${pad(Math.floor(seconds % 86400 / 3600))}:${pad(Math.floor(seconds % 3600 / 60))}:${pad(seconds % 60)}`;
  });
  update();
  const timer = setInterval(update, 1000);
  addEventListener('pagehide', () => clearInterval(timer), {once: true});
})();
