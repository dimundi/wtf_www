<?php
if (!defined('ABSPATH')) { exit; }

const WTFG_SCHEMA_VERSION = '3';

function wtfg_table($name) {
    global $wpdb;
    $names=['customers','products','variants','orders','order_items','imports','changes'];
    if (!in_array($name,$names,true)) { throw new InvalidArgumentException('Nieznana tabela Gamefound.'); }
    return $wpdb->prefix.'wtfg_'.$name;
}

function wtfg_install_schema() {
    global $wpdb;
    require_once ABSPATH.'wp-admin/includes/upgrade.php';
    $charset=$wpdb->get_charset_collate();
    $definitions=[
        'customers'=>"id bigint unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint unsigned NOT NULL,
            external_id varchar(64) NOT NULL,
            full_name varchar(500) NOT NULL,
            is_business tinyint unsigned NULL,
            data longtext NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY campaign_customer (campaign_id,external_id)",
        'products'=>"id bigint unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint unsigned NOT NULL,
            external_id varchar(64) NOT NULL,
            name text NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY campaign_product (campaign_id,external_id)",
        'variants'=>"id bigint unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint unsigned NOT NULL,
            product_id bigint unsigned NOT NULL,
            name text NOT NULL,
            name_hash char(64) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY product_variant (product_id,name_hash),
            KEY campaign_variants (campaign_id)",
        'orders'=>"id bigint unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint unsigned NOT NULL,
            external_id varchar(64) NOT NULL,
            public_id varchar(128) NOT NULL,
            user_external_id varchar(64) NOT NULL,
            stage varchar(64) NOT NULL,
            payment_status varchar(64) NOT NULL,
            currency varchar(8) NOT NULL,
            total_amount decimal(20,4) NULL,
            total_paid decimal(20,4) NULL,
            description longtext NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY campaign_order (campaign_id,external_id),
            KEY campaign_public (campaign_id,public_id),
            KEY campaign_stage (campaign_id,stage),
            KEY campaign_customer (campaign_id,user_external_id)",
        'order_items'=>"id bigint unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint unsigned NOT NULL,
            external_order_id varchar(64) NOT NULL,
            external_user_id varchar(64) NOT NULL,
            order_id bigint unsigned NULL,
            line_key char(64) NOT NULL,
            source text NOT NULL,
            external_product_id varchar(64) NOT NULL,
            variant text NOT NULL,
            variant_id bigint unsigned NULL,
            quantity bigint unsigned NULL,
            quantity_raw varchar(128) NOT NULL,
            status varchar(32) NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY source_line (campaign_id,external_order_id,line_key),
            KEY campaign_status (campaign_id,status),
            KEY fulfillment_order (order_id),
            KEY fulfillment_variant (variant_id)",
        'imports'=>"id bigint unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint unsigned NOT NULL,
            user_id bigint unsigned NOT NULL,
            created_at datetime NOT NULL,
            finished_at datetime NULL,
            status varchar(24) NOT NULL,
            file_hash varchar(64) NOT NULL,
            snapshot_hash varchar(64) NOT NULL,
            payload longtext NOT NULL,
            report longtext NOT NULL,
            PRIMARY KEY  (id),
            KEY campaign_history (campaign_id,id),
            KEY expiry (status,created_at)",
        'changes'=>"id bigint unsigned NOT NULL AUTO_INCREMENT,
            campaign_id bigint unsigned NOT NULL,
            import_id bigint unsigned NOT NULL,
            user_id bigint unsigned NOT NULL,
            created_at datetime NOT NULL,
            entity varchar(24) NOT NULL,
            external_id varchar(128) NOT NULL,
            operation varchar(24) NOT NULL,
            fields text NOT NULL,
            PRIMARY KEY  (id),
            KEY import_changes (import_id,id),
            KEY campaign_changes (campaign_id,id)",
    ];
    foreach ($definitions as $name=>$definition) {
        dbDelta('CREATE TABLE '.wtfg_table($name)." (\n$definition\n) ENGINE=InnoDB $charset;");
        if ($wpdb->last_error || $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s',$wpdb->esc_like(wtfg_table($name))))!==wtfg_table($name)) {
            throw new RuntimeException('Nie można utworzyć tabel Gamefound.');
        }
    }
    update_option('wtfg_schema',WTFG_SCHEMA_VERSION,false);
}

add_action('admin_init',function () {
    if (!current_user_can('manage_options')) { return; }
    // Zmiana modelu nie kasuje automatycznie danych istniejącej instalacji.
    if (get_option('wtfg_schema')!==WTFG_SCHEMA_VERSION) { return; }
    if (str_starts_with((string)($_GET['page'] ?? ''),'wtfg')) {
        global $wpdb;
        $wpdb->query('UPDATE '.wtfg_table('imports')." SET payload='',status='expired' WHERE status='preview' AND created_at < UTC_TIMESTAMP() - INTERVAL 1 DAY");
    }
});

add_action('before_delete_post',function ($id,$post) {
    if ($post->post_type!=='wtfg_campaign' || get_option('wtfg_schema')!==WTFG_SCHEMA_VERSION) { return; }
    global $wpdb;
    foreach (['changes','imports','order_items','orders','variants','products','customers'] as $name) { $wpdb->delete(wtfg_table($name),['campaign_id'=>$id]); }
    delete_metadata('user',0,'_wtfg_selected_campaign',(string)$id,true);
},10,2);
