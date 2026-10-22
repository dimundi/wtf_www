<?php
/**
 * Plugin Name: WTF Omnibus
 * Description: Historia cen PLN, ręczne korekty i cena odniesienia przed obniżką w WooCommerce.
 * Version: 0.1.0
 * Requires Plugins: woocommerce
 * Text Domain: wtf_omnibus
 */
if (!defined('ABSPATH')) exit;

require_once __DIR__.'/includes/history.php';
require_once __DIR__.'/includes/admin.php';
require_once __DIR__.'/includes/display.php';

register_activation_hook(__FILE__, 'wtfo_install');
add_action('plugins_loaded', function () {
    if (!class_exists('WooCommerce')) return;
    if (get_option('wtfo_schema') !== '1') wtfo_install();
    add_action('woocommerce_after_product_object_save', 'wtfo_track');
    add_action('woocommerce_after_product_variation_object_save', 'wtfo_track');
    add_action('before_delete_post', function ($id) {
        if (!in_array(get_post_type($id), ['product', 'product_variation'], true)) return;
        global $wpdb;
        $wpdb->delete($wpdb->prefix.'wtfo_prices', ['product_id'=>$id], ['%d']);
    });
});
