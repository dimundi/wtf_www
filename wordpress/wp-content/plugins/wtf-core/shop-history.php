<?php
if (!defined('ABSPATH')) exit;

// Historical numbers remain stable even though HPOS assigns new internal IDs.
add_filter('woocommerce_order_number',function ($number,$order) {
    return $order->get_meta('_wtf_source_order_number') ?: $number;
},10,2);

// Physical historical items deliberately have no link to the new catalog.
add_filter('woocommerce_valid_order_statuses_for_order_again',function ($statuses) {
    // WooCommerce passes only statuses, so resolve the order from the request.
    $order_id=absint(get_query_var('view-order'));
    if (isset($_GET['order_again'])) $order_id=absint(wp_unslash($_GET['order_again']));
    $order=$order_id ? wc_get_order($order_id) : false;
    return $order && $order->get_meta('_wtf_source_order_id') ? [] : $statuses;
});
