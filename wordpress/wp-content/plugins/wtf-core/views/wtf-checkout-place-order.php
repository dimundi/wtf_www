<?php
defined('ABSPATH') || exit;
$blocked=WC()->cart->needs_payment() && !WC()->payment_gateways()->get_available_payment_gateways();
$text=WC()->cart->needs_payment() ? 'Zamawiam i płacę · '.wp_strip_all_tags(WC()->cart->get_total()) : 'Zamawiam';
?>
<div class="wtf-checkout-place-order form-row place-order">
    <noscript><p>Po zmianie dostawy przelicz kwotę przed złożeniem zamówienia.</p><button type="submit" class="button" name="woocommerce_checkout_update_totals" value="1">Przelicz zamówienie</button></noscript>
    <?php wc_get_template('checkout/terms.php'); ?>
    <?php do_action('woocommerce_review_order_before_submit'); ?>
    <?php echo apply_filters('woocommerce_order_button_html','<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="'.esc_attr($text).'" data-value="'.esc_attr($text).'" '.disabled($blocked,true,false).'>'.esc_html($text).'</button>'); ?>
    <?php do_action('woocommerce_review_order_after_submit'); ?>
    <?php wp_nonce_field('woocommerce-process_checkout','woocommerce-process-checkout-nonce'); ?>
</div>
