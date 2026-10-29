<?php
if (!defined('ABSPATH')) exit;

// Własny formularz korzysta z mapy również bez modułu tworzenia etykiet InPost.
add_action('wp_enqueue_scripts', function () {
    if (!function_exists('is_checkout') || !is_checkout() || is_wc_endpoint_url()) return;
    $host = get_option('easypack_api_environment') === 'sandbox'
        ? 'https://sandbox-easy-geowidget-sdk.easypack24.net' : 'https://geowidget.inpost.pl';
    if (!wp_script_is('geowidget-inpost', 'enqueued')) wp_enqueue_script('geowidget-inpost', $host . '/inpost-geowidget.js', [], null, true);
    if (!wp_style_is('geowidget-css', 'enqueued')) wp_enqueue_style('geowidget-css', $host . '/inpost-geowidget.css', [], null);
}, 80);

// A classic WooCommerce checkout: native sessions, rates, coupons and order processing.
function wtf_checkout_input() {
    $data=[];
    if (isset($_POST['post_data']) && is_string($_POST['post_data'])) {
        parse_str(wp_unslash($_POST['post_data']),$data);
    } else {
        $data=wp_unslash($_POST);
    }
    return $data;
}

function wtf_checkout_invoice() {
    $data=wtf_checkout_input();
    return isset($data['wtf_document']) && $data['wtf_document']==='invoice';
}

function wtf_checkout_shipping_mode($methods=null) {
    if (!WC()->cart || !WC()->cart->needs_shipping()) return 'digital';
    $data=wtf_checkout_input();
    $methods=$methods ?? ($data['shipping_method'] ?? (WC()->session ? WC()->session->get('chosen_shipping_methods',[]) : []));
    $map=get_option('wtf_checkout_shipping_modes',[]);
    $modes=[];
    foreach ((array)$methods as $method) {
        if (is_string($method)) $modes[]=$map[$method] ?? 'courier';
    }
    return $modes && !in_array('courier',$modes,true) ? 'locker' : 'courier';
}

add_filter('woocommerce_checkout_registration_required','__return_false');
add_filter('woocommerce_checkout_registration_enabled','__return_true');
add_filter('gettext_woocommerce',function ($translation,$text) {
    return $text==='Login' && function_exists('is_checkout') && is_checkout() ? 'Zaloguj się' : $translation;
},20,2);

add_filter('woocommerce_package_rates',function ($rates,$package) {
    $courier_only=false;
    foreach ($package['contents'] as $item) {
        if ($item['data']->get_shipping_class()==='tylko-kurier') $courier_only=true;
    }
    if ($courier_only) {
        $map=get_option('wtf_checkout_shipping_modes',[]);
        foreach ($rates as $id=>$rate) if (($map[$id] ?? '')==='locker') unset($rates[$id]);
    }
    return $rates;
},20,2);

// A receipt-only purchase must not erase invoice details saved in the customer's account.
add_action('woocommerce_checkout_update_customer',function ($customer,$data) {
    if (!wtf_checkout_invoice()) {
        foreach (['first_name','last_name','company','address_1','address_2','postcode','city','state','country'] as $key) {
            $customer->{'set_billing_'.$key}((string)get_user_meta($customer->get_id(),'billing_'.$key,true));
        }
        $customer->delete_meta_data('billing_nip');
        if (metadata_exists('user',$customer->get_id(),'billing_nip')) $customer->update_meta_data('billing_nip',get_user_meta($customer->get_id(),'billing_nip',true));
    }
    if (wtf_checkout_shipping_mode($data['shipping_method'] ?? [])!=='courier') {
        foreach (['first_name','last_name','company','address_1','address_2','postcode','city','state','country'] as $key) {
            $customer->{'set_shipping_'.$key}((string)get_user_meta($customer->get_id(),'shipping_'.$key,true));
        }
    }
},20,2);

// Keep delivery separate from the invoice address, even on stores configured to bill-only.
add_filter('option_woocommerce_ship_to_destination',function ($value) {
    return (function_exists('is_checkout') && is_checkout()) || isset($_GET['wc-ajax']) ? 'shipping' : $value;
});

add_action('wp',function () {
    if (!is_checkout() || is_wc_endpoint_url()) return;
    remove_action('woocommerce_before_checkout_form','woocommerce_checkout_coupon_form',10);
    remove_action('woocommerce_before_checkout_form','woocommerce_checkout_login_form',10);
});

add_filter('woocommerce_locate_template',function ($template,$name) {
    $views=[
        'checkout/form-checkout.php'=>'checkout-form.php',
        'checkout/review-order.php'=>'checkout-review.php',
        'checkout/payment.php'=>'checkout-payment.php',
    ];
    if (isset($views[$name])) return __DIR__.'/views/'.$views[$name];
    return $template;
},20,2);

add_filter('woocommerce_checkout_get_value',function ($value,$key) {
    if (in_array($key,['billing_country','shipping_country'],true) && $value===null) {
        $saved=is_user_logged_in() ? get_user_meta(get_current_user_id(),$key,true) : '';
        return $saved ?: 'PL';
    }
    return $value;
},20,2);

add_filter('woocommerce_checkout_fields',function ($fields) {
    $fields['account']=[
        'account_password'=>['type'=>'password','label'=>'Hasło','required'=>true,'class'=>['form-row-wide'],'autocomplete'=>'new-password','priority'=>10],
        'account_password_confirm'=>['type'=>'password','label'=>'Powtórz hasło','required'=>true,'class'=>['form-row-wide'],'autocomplete'=>'new-password','priority'=>20],
    ];
    $invoice=wtf_checkout_invoice();
    $physical=WC()->cart && WC()->cart->needs_shipping();
    $courier=$physical && wtf_checkout_shipping_mode()==='courier';
    foreach ($fields['billing'] as $key=>&$field) {
        $field['required']=$key==='billing_email' || ($invoice && (in_array($key,['billing_company','billing_country','billing_address_1','billing_postcode','billing_city'],true) || ($key==='billing_state' && !empty($field['required']))));
        if (!$invoice && !in_array($key,['billing_email','billing_phone'],true)) $field['validate']=[];
    }
    unset($field);
    $fields['billing']['billing_company']=array_merge($fields['billing']['billing_company'] ?? [],[
        'type'=>'text','label'=>'Nazwa firmy','required'=>$invoice,'priority'=>30,
        'class'=>['form-row-wide','address-field'],'autocomplete'=>'organization',
    ]);
    $fields['billing']['billing_address_1']['label']='Ulica i numer budynku / lokalu';
    $fields['billing']['billing_phone']['required']=false;
    $fields['billing']['billing_nip']=['type'=>'text','label'=>'NIP','required'=>$invoice,'class'=>['form-row-wide','address-field'],'maxlength'=>32,'priority'=>35];
    foreach ($fields['shipping'] as $key=>&$field) {
        $field['required']=$physical && ($key==='shipping_country' || ($courier && (in_array($key,['shipping_first_name','shipping_last_name','shipping_address_1','shipping_postcode','shipping_city'],true) || ($key==='shipping_state' && !empty($field['required'])))));
        if (!$courier) $field['validate']=[];
    }
    unset($field);
    $fields['shipping']['shipping_phone']=['type'=>'tel','label'=>'Numer telefonu','required'=>$physical,'class'=>['form-row-wide'],'validate'=>['phone'],'autocomplete'=>'tel','priority'=>100];
    $fields['shipping']['shipping_email']=['type'=>'email','label'=>'Adres e-mail','required'=>$physical,'class'=>['form-row-wide'],'validate'=>['email'],'autocomplete'=>'email','priority'=>110];
    $fields['shipping']['shipping_address_1']['label']='Ulica i numer budynku / lokalu';
    // The point is an order attribute, never a residential or invoice address.
    $fields['order']['wtf_locker_point']=[
        'type'=>'hidden','label'=>'Wybierz Paczkomat na mapie','required'=>$physical && !$courier,
        'class'=>['form-row-wide'],'maxlength'=>32,'priority'=>5,
    ];
    $fields['order']['wtf_locker_address']=['type'=>'hidden','required'=>false];
    return $fields;
},100);

add_filter('woocommerce_checkout_posted_data',function ($data) {
    $invoice=wtf_checkout_invoice();
    $mode=wtf_checkout_shipping_mode($data['shipping_method'] ?? []);
    // Ignore stale values from fields that are not relevant to this purchase.
    if (!$invoice) {
        foreach (['first_name','last_name','company','address_1','address_2','city','postcode','state','nip'] as $key) $data['billing_'.$key]='';
        $data['billing_country']=$mode==='digital' ? 'PL' : ($data['shipping_country'] ?? 'PL');
    }
    if ($mode!=='courier') {
        foreach (['company','address_1','address_2','city','postcode','state'] as $key) $data['shipping_'.$key]='';
        $data['shipping_country']=$mode==='locker' ? 'PL' : '';
    }
    if ($mode!=='locker') { $data['wtf_locker_point']=''; $data['wtf_locker_address']=''; }
    if ($mode==='digital') {
        $data['shipping_first_name']='';
        $data['shipping_last_name']='';
    }
    $data['ship_to_different_address']=$mode!=='digital';
    return $data;
});

add_action('woocommerce_after_checkout_validation',function ($data,$errors) {
    if (!is_user_logged_in() && !empty($data['createaccount']) && !empty($data['account_password']) && !empty($data['account_password_confirm'])) {
        if (!hash_equals(wp_unslash($data['account_password']),$data['account_password_confirm'])) {
            $errors->add('account_password_confirm','Podane hasła muszą być identyczne.',['id'=>'account_password_confirm']);
        }
    }
    $posted=wtf_checkout_input();
    if (!isset($posted['wtf_document']) || !in_array($posted['wtf_document'],['receipt','invoice'],true)) {
        $errors->add('wtf_document','Wybierz paragon albo fakturę VAT.');
    }
    if (wtf_checkout_shipping_mode($data['shipping_method'] ?? [])==='locker') {
        $point=$data['wtf_locker_point'] ?? '';
        if ($point!=='' && !preg_match('/^[A-Z0-9_-]{3,32}$/i',$point)) {
            $errors->add('wtf_locker_point','Podaj prawidłowy kod paczkomatu.',['id'=>'wtf_locker_point']);
        }
    }
},20,2);

add_action('woocommerce_checkout_create_order',function ($order,$data) {
    if (WC()->cart && WC()->cart->needs_shipping()) $order->update_meta_data('_shipping_email',$data['shipping_email'] ?? '');
    $order->update_meta_data('_wtf_purchase_document',wtf_checkout_invoice() ? 'invoice' : 'receipt');
    if (wtf_checkout_invoice()) $order->update_meta_data('_billing_nip',$data['billing_nip'] ?? '');
    if (wtf_checkout_shipping_mode($data['shipping_method'] ?? [])==='locker') {
        $order->update_meta_data('_wtf_locker_point',strtoupper($data['wtf_locker_point'] ?? ''));
        $order->update_meta_data('_wtf_locker_address',$data['wtf_locker_address'] ?? '');
        $order->update_meta_data('_parcel_machine_id',strtoupper($data['wtf_locker_point'] ?? ''));
        $order->update_meta_data('_parcel_machine_desc',$data['wtf_locker_address'] ?? '');
    }
},20,2);

add_action('woocommerce_admin_order_data_after_billing_address',function ($order) {
    $document=$order->get_meta('_wtf_purchase_document');
    if (!$document) return;
    echo '<p><strong>Dokument zakupu:</strong> '.($document==='invoice' ? 'Faktura VAT' : 'Paragon').'</p>';
    if ($document==='invoice') echo '<p><strong>NIP:</strong> '.esc_html($order->get_meta('_billing_nip')).'</p>';
});
add_action('woocommerce_admin_order_data_after_shipping_address',function ($order) {
    if ($order->get_meta('_shipping_email')) echo '<p><strong>E-mail odbiorcy:</strong> '.esc_html($order->get_meta('_shipping_email')).'</p>';
    $point=$order->get_meta('_wtf_locker_point');
    if ($point) echo '<p><strong>Paczkomat:</strong> '.esc_html($point.' '.$order->get_meta('_wtf_locker_address')).'</p>';
});
add_filter('woocommerce_email_order_meta_fields',function ($fields,$sent_to_admin,$order) {
    if ($order->get_meta('_wtf_purchase_document')) $fields['wtf_document']=['label'=>'Dokument zakupu','value'=>$order->get_meta('_wtf_purchase_document')==='invoice' ? 'Faktura VAT' : 'Paragon'];
    if ($order->get_meta('_billing_nip') && $order->get_meta('_wtf_purchase_document')==='invoice') $fields['wtf_nip']=['label'=>'NIP','value'=>$order->get_meta('_billing_nip')];
    if ($order->get_meta('_wtf_locker_point')) $fields['wtf_locker']=['label'=>'Paczkomat','value'=>$order->get_meta('_wtf_locker_point')];
    return $fields;
},10,3);

function wtf_checkout_delivery_options() {
    echo '<div class="wtf-checkout-delivery-options">';
    $packages=WC()->shipping()->get_packages();
    $chosen=WC()->session->get('chosen_shipping_methods',[]);
    $map=get_option('wtf_checkout_shipping_modes',[]);
    foreach ($packages as $index=>$package) {
        if (count($packages)>1) echo '<p>Przesyłka '.esc_html($index+1).'</p>';
        echo '<div class="wtf-delivery-choices">';
        foreach ($package['rates'] as $rate) {
            $id='shipping_method_'.$index.'_'.sanitize_title($rate->get_id());
            $mode=$map[$rate->get_id()] ?? 'courier';
            echo '<label class="wtf-delivery-choice" for="'.esc_attr($id).'">';
            printf('<input type="radio" class="shipping_method" name="shipping_method[%d]" data-index="%d" data-delivery-mode="%s" id="%s" value="%s" %s>',(int)$index,(int)$index,esc_attr($mode),esc_attr($id),esc_attr($rate->get_id()),checked($chosen[$index] ?? '',$rate->get_id(),false));
            echo '<span><strong>'.wp_kses_post(wc_cart_totals_shipping_method_label($rate)).'</strong><small>'.($mode==='locker' ? 'Odbiór w wybranym paczkomacie' : 'Przesyłka pod wskazany adres').'</small></span></label>';
            do_action('woocommerce_after_shipping_rate',$rate,$index);
        }
        echo '</div>';
        if (!$package['rates']) echo '<p class="wtf-checkout-hint">Brak dostępnej dostawy dla wybranego kraju. Sprawdź adres dostawy.</p>';
    }
    echo '</div>';
}

function wtf_checkout_place_order() {
    wc_get_template('wtf-checkout-place-order.php',[], '', __DIR__.'/views/');
}

add_filter('woocommerce_update_order_review_fragments',function ($fragments) {
    ob_start(); wtf_checkout_delivery_options(); $fragments['.wtf-checkout-delivery-options']=ob_get_clean();
    ob_start(); wtf_checkout_place_order(); $fragments['.wtf-checkout-place-order']=ob_get_clean();
    return $fragments;
});

add_filter('woocommerce_cart_item_name',function ($name,$item) {
    if (!is_checkout() || is_wc_endpoint_url()) return $name;
    return '<span class="wtf-checkout-item-image">'.$item['data']->get_image('woocommerce_thumbnail',['alt'=>'']).'</span><span class="wtf-checkout-item-name">'.$name.'</span>';
},20,2);
