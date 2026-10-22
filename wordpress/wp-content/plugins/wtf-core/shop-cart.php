<?php
if (!defined('ABSPATH')) exit;

add_action('woocommerce_before_cart',function () {
    if (is_cart()) echo '<div class="wtf-cart-layout">';
},20);
add_action('woocommerce_after_cart',function () {
    if (is_cart()) echo '</div>';
},100);
add_filter('woocommerce_shipping_show_shipping_calculator',function ($show) {
    return is_cart() ? false : $show;
});
add_filter('woocommerce_shipping_estimate_html',function ($html) {
    return is_cart() ? '' : $html;
});
