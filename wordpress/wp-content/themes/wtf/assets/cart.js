(($) => {
    const arrangeActions = () => {
        const actions = document.querySelector('.wtf-cart-layout .actions');
        if (!actions) return;
        const update = actions.querySelector('[name="update_cart"]');
        const coupon = actions.querySelector('.coupon');
        if (!update || !coupon) return;
        let controls = actions.querySelector('.wtf-cart-actions');
        if (!controls) {
            controls = document.createElement('div');
            controls.className = 'wtf-cart-actions';
            actions.prepend(controls);
        }
        controls.append(update, coupon);
    };
    $(document.body).on('updated_wc_div', arrangeActions);
    arrangeActions();
})(jQuery);
