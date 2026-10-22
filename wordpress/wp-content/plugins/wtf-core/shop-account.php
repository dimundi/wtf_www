<?php
if (!defined('ABSPATH')) exit;

// Keep WooCommerce's login fields, hooks and validation inside our visual layout.
add_action('woocommerce_before_customer_login_form',function () {
    if (!is_account_page()) return;
    $uploads=wp_upload_dir();
    echo '<div class="wtf-login-layout"><figure class="wtf-login-art"><img src="'.esc_url($uploads['baseurl'].'/media/witaj_stworek.png').'" alt="" width="1200" height="1083" fetchpriority="high"><figcaption>Kornik Głodomór - gra <a href="'.esc_url(home_url('/kurzolaki/')).'">Kurzołaki</a></figcaption></figure><div class="wtf-login-panel">';
});

add_action('woocommerce_after_customer_login_form',function () {
    if (is_account_page()) echo '</div></div>';
});

add_filter('woocommerce_default_address_fields',function ($fields) {
    if (function_exists('is_account_page') && is_account_page() && isset($fields['address_1'])) {
        $fields['address_1']['label']='Adres';
    }
    return $fields;
},20);

// Reuse the imported account data; saving an address never edits past orders.
function wtf_account_billing_nip($user_id) {
    foreach (['billing_nip','_billing_nip','billing_nip_tylko_dla_firm','billing_wcccf_nip_tylko_dla_firm'] as $key) {
        if (metadata_exists('user',$user_id,$key)) return (string)get_user_meta($user_id,$key,true);
    }
    return '';
}

function wtf_account_wants_invoice() {
    if (isset($_POST['action']) && $_POST['action']==='edit_address') {
        return isset($_POST['billing_vat_invoice']);
    }
    $user_id=get_current_user_id();
    if (metadata_exists('user',$user_id,'billing_vat_invoice')) {
        return (bool)get_user_meta($user_id,'billing_vat_invoice',true);
    }
    return get_user_meta($user_id,'billing_company',true)!=='' || wtf_account_billing_nip($user_id)!=='';
}

add_filter('woocommerce_billing_fields',function ($fields) {
    if (!function_exists('is_account_page') || !is_account_page()) return $fields;
    $invoice=wtf_account_wants_invoice();
    $fields['billing_vat_invoice']=[
        'type'=>'checkbox','label'=>'Chcę otrzymać fakturę VAT','required'=>false,'priority'=>5,
        'class'=>['form-row-wide'],
    ];
    foreach (['billing_first_name','billing_last_name'] as $key) {
        if (isset($fields[$key])) $fields[$key]['required']=!$invoice;
    }
    $fields['billing_company']=array_merge($fields['billing_company'] ?? [],[
        'type'=>'text','label'=>'Nazwa firmy','required'=>$invoice,'priority'=>25,
        'class'=>['form-row-wide'],'autocomplete'=>'organization',
    ]);
    $fields['billing_nip']=[
        'type'=>'text','label'=>'NIP','required'=>$invoice,'priority'=>26,
        'class'=>['form-row-wide'],'maxlength'=>32,
    ];
    return $fields;
},30);

add_filter('woocommerce_my_account_edit_address_field_value',function ($value,$key) {
    if ($key==='billing_country' && $value==='') return 'PL';
    if ($key==='billing_vat_invoice') return wtf_account_wants_invoice() ? 1 : 0;
    return $key==='billing_nip' ? wtf_account_billing_nip(get_current_user_id()) : $value;
},10,2);

add_action('woocommerce_customer_save_address',function ($user_id,$type) {
    if ($type==='billing' && isset($_POST['billing_nip'])) {
        // Retain an explicit empty value so clearing it cannot revive an old field.
        update_user_meta($user_id,'billing_nip',sanitize_text_field(wp_unslash($_POST['billing_nip'])));
    }
},10,2);

add_action('woocommerce_my_account_after_my_address',function ($type) {
    if ($type!=='billing') return;
    $nip=wtf_account_billing_nip(get_current_user_id());
    if ($nip!=='') echo '<p class="wtf-account-nip">NIP: '.esc_html($nip).'</p>';
});

add_action('woocommerce_order_details_after_customer_address',function ($type,$order) {
    if (!is_account_page() || $type!=='billing') return;
    foreach (['_billing_nip','billing_nip','_billing_nip_tylko_dla_firm','_billing_wcccf_nip_tylko_dla_firm'] as $key) {
        $nip=$order->get_meta($key);
        if ($nip!=='') {
            echo '<p class="wtf-account-nip">NIP: '.esc_html($nip).'</p>';
            break;
        }
    }
},10,2);

add_filter('gettext_woocommerce',function ($translation,$text) {
    if ($text==='No downloads available yet.' && function_exists('is_account_page') && is_account_page()) {
        return 'Nie masz jeszcze produktów cyfrowych do pobrania.';
    }
    return $translation;
},10,2);

add_filter('woocommerce_add_notice',function ($message) {
    if (function_exists('is_account_page') && is_account_page() && is_wc_endpoint_url('downloads') && str_contains($message,'Nie masz jeszcze produktów cyfrowych do pobrania.')) {
        return 'Nie masz jeszcze produktów cyfrowych do pobrania.';
    }
    return $message;
});

add_filter('woocommerce_account_menu_items',function ($items) {
    if (isset($items['dashboard'])) $items['dashboard']='Podsumowanie';
    return $items;
});

add_filter('woocommerce_account_orders_columns',function ($columns) {
    $columns['order-number']='Numer zamówienia';
    $columns['order-total']='Wartość zamówienia';
    return $columns;
});

add_action('woocommerce_my_account_my_orders_column_order-number',function ($order) {
    printf('<a class="wtf-account-order-number" href="%s">#%s</a>',esc_url($order->get_view_order_url()),esc_html($order->get_order_number()));
});

add_action('woocommerce_my_account_my_orders_column_order-date',function ($order) {
    $date=$order->get_date_created();
    if ($date) printf('<time datetime="%s">%s</time>',esc_attr($date->date('c')),esc_html(wc_format_datetime($date,'d.m.Y')));
});

add_action('woocommerce_my_account_my_orders_column_order-total',function ($order) {
    echo '<strong>'.wp_kses_post($order->get_formatted_order_total()).'</strong>';
});

add_action('woocommerce_my_account_my_orders_column_order-status',function ($order) {
    echo '<span class="wtf-account-status">'.esc_html(wc_get_order_status_name($order->get_status())).'</span>';
});

add_filter('woocommerce_locate_template',function ($template,$name) {
    if (is_account_page() && $name==='myaccount/my-address.php') return __DIR__.'/views/account-addresses.php';
    if (is_account_page() && is_wc_endpoint_url('view-order')) {
        $views=[
            'myaccount/view-order.php'=>'account-view-order.php',
            'order/order-details.php'=>'account-order-details.php',
            'order/order-details-item.php'=>'account-order-item.php',
        ];
        if (isset($views[$name])) return __DIR__.'/views/'.$views[$name];
    }
    return $name==='myaccount/dashboard.php' ? __DIR__.'/views/account-dashboard.php' : $template;
},10,2);
