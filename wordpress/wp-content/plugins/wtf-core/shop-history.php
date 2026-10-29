<?php
if (!defined('ABSPATH')) exit;

// Historical numbers remain stable even though HPOS assigns new internal IDs.
add_filter('woocommerce_order_number',function ($number,$order) {
    return $order->get_meta('_wtf_source_order_number') ?: ($order->get_meta('_wtf_order_number') ?: $number);
},10,2);

// Oddzielny licznik nie zmienia ID zamówienia używanego przez płatności i pobrania.
function wtf_assign_order_number($order_id, $order = null) {
    $order = $order instanceof WC_Order ? $order : wc_get_order($order_id);
    if (!$order instanceof WC_Order || $order->get_type() !== 'shop_order') return;
    if ($order->get_meta('_wtf_order_number') || $order->get_meta('_wtf_source_order_number') || $order->get_meta('_wtf_source_order_id')) return;
    if (in_array($order->get_status(), ['auto-draft', 'draft', 'checkout-draft'], true)) return;
    if ($order->get_id() <= (int)get_option('wtf_order_sequence_start_after_id', PHP_INT_MAX)) return;
    global $wpdb;
    // UPDATE blokuje wiersz licznika również wewnątrz transakcji checkoutu.
    // LAST_INSERT_ID jest lokalne dla połączenia: równoległe zakupy dostają różne numery.
    $updated = $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value=LAST_INSERT_ID(CAST(option_value AS UNSIGNED)+1) WHERE option_name=%s AND option_value REGEXP '^[0-9]+$'",
        'wtf_order_sequence_last'
    ));
    if ($updated === false) throw new RuntimeException('Nie udało się nadać numeru zamówienia. Spróbuj ponownie.');
    if (!$updated) return; // Numeracja nie została jeszcze uruchomiona.
    $number = (int)$wpdb->get_var('SELECT LAST_INSERT_ID()');
    if ($number < 15000) throw new RuntimeException('Nieprawidłowy licznik zamówień.');
    wp_cache_delete('wtf_order_sequence_last', 'options');
    $order->update_meta_data('_wtf_order_number', (string)$number);
    $order->save_meta_data();
}
add_action('woocommerce_new_order', 'wtf_assign_order_number', 1, 2);
add_action('woocommerce_checkout_order_created', static function ($order) { wtf_assign_order_number($order->get_id(), $order); }, 1);

foreach (['woocommerce_order_table_search_query_meta_keys', 'woocommerce_shop_order_search_fields'] as $hook) {
    add_filter($hook, static fn($keys) => array_values(array_unique(array_merge($keys, ['_wtf_order_number', '_wtf_source_order_number']))));
}

// Physical historical items deliberately have no link to the new catalog.
add_filter('woocommerce_valid_order_statuses_for_order_again',function ($statuses) {
    // WooCommerce passes only statuses, so resolve the order from the request.
    $order_id=absint(get_query_var('view-order'));
    if (isset($_GET['order_again'])) $order_id=absint(wp_unslash($_GET['order_again']));
    $order=$order_id ? wc_get_order($order_id) : false;
    return $order && $order->get_meta('_wtf_source_order_id') ? [] : $statuses;
});
