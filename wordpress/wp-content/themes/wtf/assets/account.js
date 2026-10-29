(() => {
    const players = [...document.querySelectorAll('.wtf-download-audio')];
    players.forEach(player => {
        player.addEventListener('play', () => {
            players.forEach(other => { if (other !== player) other.pause(); });
        });
        player.addEventListener('error', () => {
            const message = player.parentElement.querySelector('.wtf-download-audio-error');
            if (message) message.hidden = false;
        });
    });
    const invoice = document.getElementById('billing_vat_invoice');
    if (!invoice) return;
    const update = () => {
        for (const key of ['billing_first_name', 'billing_last_name', 'billing_company', 'billing_nip']) {
            const input = document.getElementById(key);
            const row = document.getElementById(`${key}_field`);
            if (!input || !row) continue;
            const required = ['billing_company', 'billing_nip'].includes(key) ? invoice.checked : !invoice.checked;
            input.required = required;
            input.setAttribute('aria-required', String(required));
            row.classList.toggle('validate-required', required);
            row.classList.remove('woocommerce-invalid', 'woocommerce-invalid-required-field');
            const label = row.querySelector('label');
            if (!label) continue;
            label.querySelectorAll('.required, .optional, .screen-reader-text').forEach(marker => marker.remove());
            const marker = document.createElement('span');
            marker.className = required ? 'required' : 'optional';
            marker.textContent = required ? ' *' : ' (opcjonalne)';
            if (required) marker.setAttribute('aria-hidden', 'true');
            label.append(marker);
        }
    };
    invoice.addEventListener('change', update);
    // WooCommerce reapplies country-specific field requirements on country changes.
    jQuery(document.body).on('country_to_state_changed', update);
    update();
})();
