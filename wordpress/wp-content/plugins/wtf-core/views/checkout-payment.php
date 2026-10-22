<?php
// WooCommerce payment.php 9.8.0: methods here, submission in the summary fragment.
defined('ABSPATH') || exit;
if (!wp_doing_ajax()) do_action('woocommerce_review_order_before_payment');
?>
<div id="payment" class="woocommerce-checkout-payment">
<?php if (WC()->cart->needs_payment()): ?>
    <?php if ($available_gateways): ?>
    <ul class="wc_payment_methods payment_methods methods">
    <?php foreach ($available_gateways as $gateway) wc_get_template('checkout/payment-method.php',['gateway'=>$gateway]); ?>
    </ul>
    <?php else: ?>
    <p class="wtf-checkout-hint" role="status"><?php echo esc_html(wp_get_environment_type()==='local' ? 'Płatności są wyłączone w tej lokalnej wersji sklepu.' : 'Obecnie nie ma dostępnej metody płatności. Skontaktuj się z nami.'); ?></p>
    <?php endif; ?>
<?php else: ?>
    <p class="wtf-checkout-hint">To zamówienie nie wymaga płatności.</p>
<?php endif; ?>
</div>
<?php if (!wp_doing_ajax()) do_action('woocommerce_review_order_after_payment'); ?>
