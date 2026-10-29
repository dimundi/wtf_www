(() => {
    const form = document.querySelector('.contact-form');
    if (!form) return;
    const button = form.querySelector('button[type="submit"]');
    const status = form.querySelector('.contact-captcha-status');
    const message = (text, ready = false) => {
        button.disabled = !ready;
        if (status) status.textContent = text;
    };
    let timer;
    const pending = () => {
        clearTimeout(timer);
        message('Sprawdzanie zabezpieczenia antyspamowego…');
        timer = setTimeout(() => message('Nie udało się wczytać zabezpieczenia. Odśwież stronę lub napisz do nas e-mailem.'), 20000);
    };
    pending();
    window.wtfContactCaptchaSuccess = () => {
        clearTimeout(timer);
        message('Weryfikacja zakończona.', true);
    };
    window.wtfContactCaptchaError = () => {
        clearTimeout(timer);
        message('Nie udało się sprawdzić zabezpieczenia. Spróbuj ponownie lub odśwież stronę.');
        return true;
    };
    window.wtfContactCaptchaExpired = () => message('Weryfikacja wygasła. Zaczekaj na jej ponowne wykonanie.');
    window.wtfContactCaptchaReady = () => {
        const container = form.querySelector('.wtf-contact-challenge');
        if (!container) return;
        let widget, currentSize;
        const render = () => {
            const size = container.clientWidth < 300 ? 'compact' : 'normal';
            if (size === currentSize) return;
            currentSize = size;
            if (widget !== undefined) window.turnstile.remove(widget);
            pending();
            widget = window.turnstile.render(container, {
                sitekey: container.dataset.sitekey, action: 'contact', language: 'pl', theme: 'auto', size,
                callback: window.wtfContactCaptchaSuccess,
                'error-callback': window.wtfContactCaptchaError,
                'expired-callback': window.wtfContactCaptchaExpired,
            });
        };
        render();
        new ResizeObserver(render).observe(container);
    };
    form.addEventListener('submit', () => { button.disabled = true; });
    window.addEventListener('pageshow', event => {
        if (event.persisted && window.turnstile) {
            message('Sprawdzanie zabezpieczenia antyspamowego…');
            window.turnstile.reset();
        }
    });
})();
