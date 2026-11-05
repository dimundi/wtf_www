<?php
if (!defined('WP_UNINSTALL_PLUGIN')) { exit; }
// Wyłącznie świadoma konfiguracja techniczna poza panelem WordPressa.
// Dawne ustawienie wtfg_delete_data jest ignorowane, także jeśli było włączone.
// Multisite wymaga osobnego wdrożenia i nie jest tu obsługiwany.
if (is_multisite() || !defined('WTF_GAMEFOUND_DELETE_DATA') || WTF_GAMEFOUND_DELETE_DATA !== true) { return; }
global $wpdb;
$ids=$wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type='wtfg_campaign'");
foreach ($ids as $id) { wp_delete_post((int)$id,true); }
foreach (['customers','products','variants','orders','order_items','imports','changes','supporters','supporter_imports','order_imports','order_products','item_imports'] as $suffix) {
    $table=$wpdb->prefix.'wtfg_'.$suffix;
    $wpdb->query("DROP TABLE IF EXISTS `$table`");
}
delete_metadata('user',0,'_wtfg_selected_campaign','',true);
delete_option('wtfg_catalog_schema');
delete_option('wtfg_schema');
delete_option('wtfg_supporters_schema');
delete_option('wtfg_orders_schema');
delete_option('wtfg_order_products_schema');
delete_option('wtfg_order_items_schema');
delete_option('wtfg_delete_data');
