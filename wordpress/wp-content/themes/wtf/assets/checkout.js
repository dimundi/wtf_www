(($) => {
    const form = document.querySelector('form.wtf-checkout');
    if (!form) return;
    const physical = form.dataset.physical === '1';
    const lockerDialog = form.querySelector('#wtf-locker-dialog');
    const lockerButton = form.querySelector('#wtf-open-locker-map');
    const lockerStatus = form.querySelector('#wtf-locker-map-status');
    const selectedLocker = form.querySelector('#wtf-selected-locker');
    if (lockerDialog) {
        let widget;
        let mapTimer;
        window.wtfLockerPicked = point => {
            if (!point || typeof point.name !== 'string' || !/^[A-Z0-9_-]{3,32}$/i.test(point.name)) return;
            const address = [point.address?.line1, point.address?.line2].filter(value => typeof value === 'string').join(', ');
            form.querySelector('#wtf_locker_point').value = point.name;
            form.querySelector('#wtf_locker_address').value = address;
            selectedLocker.textContent = `${point.name} · ${address}`;
            selectedLocker.hidden = false;
            lockerButton.textContent = 'Zmień Paczkomat';
            lockerDialog.close();
            $(document.body).trigger('update_checkout');
        };
        lockerButton.addEventListener('click', () => {
            lockerDialog.showModal();
            if (widget) return;
            if (!lockerDialog.dataset.token) {
                lockerStatus.textContent = 'Mapa jest chwilowo niedostępna. Spróbuj ponownie później.';
                return;
            }
            widget = document.createElement('inpost-geowidget');
            widget.setAttribute('token', lockerDialog.dataset.token);
            widget.setAttribute('language', 'pl');
            widget.setAttribute('config', 'parcelCollect');
            widget.setAttribute('onpoint', 'wtfLockerPicked');
            widget.addEventListener('inpost.geowidget.init', event => {
                clearTimeout(mapTimer);
                lockerStatus.hidden = true;
                event.detail.api.changePointsType('PARCEL_LOCKER');
                event.detail.api.changeLanguage('pl');
            });
            form.querySelector('#wtf-locker-map').append(widget);
            mapTimer = setTimeout(() => {
                lockerStatus.textContent = 'Nie udało się załadować mapy. Sprawdź połączenie i spróbuj ponownie.';
                lockerStatus.hidden = false;
            }, 20000);
        });
        form.querySelector('#wtf-close-locker-map').addEventListener('click', () => lockerDialog.close());
        lockerDialog.addEventListener('close', () => lockerButton.focus());
    }
    const createAccount = form.querySelector('#createaccount');
    const updateAccount = () => {
        if (!createAccount) return;
        const fields = form.querySelector('#wtf-account-passwords');
        fields.hidden = !createAccount.checked;
        createAccount.setAttribute('aria-expanded', String(createAccount.checked));
        fields.querySelectorAll('input').forEach(input => {
            input.disabled = !createAccount.checked;
            input.required = createAccount.checked;
            input.setAttribute('aria-required', String(createAccount.checked));
        });
    };
    createAccount?.addEventListener('change', updateAccount);
    updateAccount();
    const invoice = () => form.querySelector('[name="wtf_document"]:checked')?.value === 'invoice';
    const delivery = () => {
        if (!physical) return 'digital';
        const selected = [...form.querySelectorAll('.shipping_method:checked')];
        return selected.length && selected.every(input => input.dataset.deliveryMode === 'locker') ? 'locker' : 'courier';
    };
    const required = (input, needed) => {
        input.required = needed;
        input.setAttribute('aria-required', String(needed));
        const row = input.closest('.form-row');
        if (!row) return;
        row.classList.toggle('validate-required', needed);
        const label = row.querySelector('label');
        if (!label) return;
        label.classList.toggle('required_field', needed);
        label.querySelectorAll('.required,.optional').forEach(marker => marker.remove());
        const marker = document.createElement('span');
        marker.className = needed ? 'required' : 'optional';
        marker.textContent = needed ? ' *' : ' (opcjonalne)';
        if (needed) marker.setAttribute('aria-hidden', 'true');
        label.append(marker);
    };
    const update = () => {
        const vat = invoice();
        const mode = delivery();
        form.querySelector('.wtf-checkout-invoice').hidden = !vat;
        form.querySelectorAll('.wtf-checkout-invoice input,.wtf-checkout-invoice select').forEach(input => {
            input.disabled = !vat;
            required(input, vat && ['billing_company', 'billing_nip', 'billing_country', 'billing_address_1', 'billing_postcode', 'billing_city'].includes(input.id));
        });
        if (!physical) return;
        form.querySelector('.wtf-checkout-locker').hidden = mode !== 'locker';
        const point = form.querySelector('#wtf_locker_point');
        point.disabled = mode !== 'locker';
        required(point, mode === 'locker');
        form.querySelector('.wtf-checkout-copy').hidden = !vat || mode !== 'courier';
        form.querySelectorAll('[id^="shipping_"]').forEach(input => {
            if (!['INPUT', 'SELECT'].includes(input.tagName) || input.classList.contains('shipping_method')) return;
            if (['shipping_phone', 'shipping_email'].includes(input.id)) {
                input.disabled = false;
                required(input, true);
                return;
            }
            const name = ['shipping_first_name', 'shipping_last_name'].includes(input.id);
            const row = input.closest('.form-row');
            if (row) row.hidden = mode === 'locker' && !name;
            input.disabled = mode === 'locker' && !name && input.id !== 'shipping_country';
            if (mode === 'locker' && input.id === 'shipping_country') {
                input.value = 'PL';
                $(input).trigger('change.select2');
            }
            required(input, mode === 'courier' && ['shipping_first_name', 'shipping_last_name', 'shipping_country', 'shipping_address_1', 'shipping_postcode', 'shipping_city'].includes(input.id));
        });
    };
    form.addEventListener('change', event => {
        if (event.target.name === 'wtf_document' || event.target.classList.contains('shipping_method')) {
            update();
            $(document.body).trigger('update_checkout');
        }
    });
    $(document.body).on('updated_checkout country_to_state_changed', update);
    form.querySelectorAll('.wtf-copy-contact').forEach(button => {
        button.addEventListener('click', () => {
            const source = document.getElementById(button.dataset.copySource);
            const target = document.getElementById(button.dataset.copyTarget);
            target.value = source.value;
            $(target).trigger('change');
            target.focus();
        });
    });
    form.querySelector('.wtf-checkout-copy')?.addEventListener('click', () => {
        for (const key of ['company', 'country', 'address_1', 'address_2', 'postcode', 'city', 'state']) {
            const from = form.querySelector(`#billing_${key}`);
            const to = form.querySelector(`#shipping_${key}`);
            if (from && to) {
                to.value = from.value;
                $(to).trigger('change');
            }
        }
        form.querySelector('#shipping_first_name')?.focus();
        $(document.body).trigger('update_checkout');
    });
    const coupon = form.querySelector('#wtf-coupon');
    const apply = form.querySelector('#wtf-apply-coupon');
    apply?.addEventListener('click', async () => {
        const result = form.querySelector('#wtf-coupon-result');
        if (!coupon.value.trim()) {
            result.textContent = 'Wpisz kod rabatowy.';
            coupon.focus();
            return;
        }
        apply.disabled = true;
        try {
            const response = await $.post(wc_checkout_params.wc_ajax_url.replace('%%endpoint%%', 'apply_coupon'), {
                security: wc_checkout_params.apply_coupon_nonce,
                coupon_code: coupon.value.trim(),
                billing_email: form.querySelector('#billing_email').value,
            });
            // Native WooCommerce response; preserve only text in the inline notice.
            const parsed = new DOMParser().parseFromString(response, 'text/html');
            result.textContent = parsed.body.textContent.trim();
            $(document.body).trigger('update_checkout');
        } catch {
            result.textContent = 'Nie udało się sprawdzić kodu. Spróbuj ponownie.';
        } finally {
            apply.disabled = false;
        }
    });
    coupon?.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            apply.click();
        }
    });
    update();
})(jQuery);
