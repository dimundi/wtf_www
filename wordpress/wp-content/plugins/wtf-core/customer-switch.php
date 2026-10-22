<?php
if (!defined('ABSPATH')) exit;

function wtf_customer_switch_allowed($user=null) {
    $user=$user ?: wp_get_current_user();
    return $user instanceof WP_User && $user->has_cap('manage_options') && $user->has_cap('edit_users');
}

function wtf_customer_switch_target($user) {
    return $user instanceof WP_User && $user->roles===['customer'] && !$user->has_cap('manage_options') && !$user->has_cap('edit_users');
}

function wtf_customer_switch_cookie_name() { return 'wtf_customer_switch_'.COOKIEHASH; }
function wtf_customer_switch_key($secret) { return 'wtf_customer_switch_'.hash('sha256',$secret); }

function wtf_customer_switch_ticket() {
    $secret=$_COOKIE[wtf_customer_switch_cookie_name()]??'';
    if (!is_string($secret) || !preg_match('/^[A-Za-z0-9]{48}$/D',$secret)) return false;
    $ticket=get_transient(wtf_customer_switch_key($secret));
    return is_array($ticket) ? ['secret'=>$secret,'ticket'=>$ticket] : false;
}

function wtf_customer_switch_context() {
    $context=wtf_customer_switch_ticket();
    if (!$context || !is_user_logged_in()) return false;
    $ticket=$context['ticket'];
    if ($ticket['customer_id']!==get_current_user_id() || !hash_equals($ticket['customer_token'],wp_get_session_token())) return false;
    return $context;
}

function wtf_customer_switch_set_cookie($value,$expires) {
    setcookie(wtf_customer_switch_cookie_name(),$value,['expires'=>$expires,'path'=>COOKIEPATH?:'/','domain'=>COOKIE_DOMAIN?:'','secure'=>is_ssl(),'httponly'=>true,'samesite'=>'Lax']);
}

function wtf_customer_switch_auth($id,$token,$expiration) {
    $duration=static fn()=>max(1,$expiration-time());
    add_filter('auth_cookie_expiration',$duration,PHP_INT_MAX);
    wp_clear_auth_cookie();
    wp_set_auth_cookie($id,false,is_ssl(),$token);
    remove_filter('auth_cookie_expiration',$duration,PHP_INT_MAX);
    wp_set_current_user($id);
    // Forget browser identifiers only; never delete either person's saved cart.
    if (function_exists('wc_setcookie')) {
        foreach ([apply_filters('woocommerce_cookie','wp_woocommerce_session_'.COOKIEHASH),'woocommerce_items_in_cart','woocommerce_cart_hash'] as $name) {
            wc_setcookie($name,'',time()-YEAR_IN_SECONDS,is_ssl(),true);
            unset($_COOKIE[$name]);
        }
    }
}

function wtf_customer_switch_return_url($context=null) {
    $context=$context ?: wtf_customer_switch_context();
    if (!$context) return '';
    return wp_nonce_url(admin_url('admin-post.php?action=wtf_return_from_customer'),'wtf_return_customer_'.hash('sha256',$context['secret']));
}

add_filter('manage_users_columns',function ($columns) {
    if (wtf_customer_switch_allowed()) $columns['wtf_customer_switch']='Konto klienta';
    return $columns;
});
add_filter('manage_users_custom_column',function ($output,$column,$id) {
    if ($column!=='wtf_customer_switch') return $output;
    $target=get_userdata($id);
    if (!wtf_customer_switch_allowed() || !current_user_can('edit_user',$id) || !wtf_customer_switch_target($target)) return '—';
    $url=wp_nonce_url(add_query_arg(['action'=>'wtf_switch_to_customer','user_id'=>$id],admin_url('admin-post.php')),'wtf_switch_customer_'.$id);
    return '<a class="button" href="'.esc_url($url).'">Zaloguj jako klient</a>';
},10,3);

add_action('admin_post_wtf_switch_to_customer',function () {
    if (!wtf_customer_switch_allowed()) wp_die('Tylko administrator może przełączyć konto.', '', ['response'=>403]);
    $id=absint($_GET['user_id']??0);
    check_admin_referer('wtf_switch_customer_'.$id);
    $target=get_userdata($id);
    if (!current_user_can('edit_user',$id) || !wtf_customer_switch_target($target)) wp_die('Można przełączyć się wyłącznie na konto klienta.', '', ['response'=>403]);
    $raw=$_COOKIE[LOGGED_IN_COOKIE]??'';
    $auth=is_string($raw)?wp_parse_auth_cookie($raw,'logged_in'):false;
    if (!$auth || wp_validate_auth_cookie($raw,'logged_in')!==get_current_user_id()) wp_die('Zaloguj się ponownie jako administrator.', '', ['response'=>403]);
    $expires=min(time()+HOUR_IN_SECONDS,(int)$auth['expiration']);
    if ($expires<=time()) wp_die('Sesja administratora wygasła.', '', ['response'=>403]);
    if ($old=wtf_customer_switch_ticket()) {
        WP_Session_Tokens::get_instance($old['ticket']['customer_id'])->destroy($old['ticket']['customer_token']);
        delete_transient(wtf_customer_switch_key($old['secret']));
    }
    $secret=wp_generate_password(48,false,false);
    $customerToken=WP_Session_Tokens::get_instance($id)->create($expires);
    $ticket=['admin_id'=>get_current_user_id(),'admin_token'=>$auth['token'],'admin_expiration'=>(int)$auth['expiration'],'customer_id'=>$id,'customer_token'=>$customerToken,'expiration'=>$expires];
    if (!set_transient(wtf_customer_switch_key($secret),$ticket,$expires-time())) {
        WP_Session_Tokens::get_instance($id)->destroy($customerToken);
        wp_die('Nie udało się przygotować bezpiecznego powrotu do administratora.');
    }
    nocache_headers();
    wtf_customer_switch_set_cookie($secret,$expires);
    wtf_customer_switch_auth($id,$customerToken,$expires);
    wp_safe_redirect(wc_get_page_permalink('myaccount'));
    exit;
});

function wtf_customer_switch_return() {
    $context=wtf_customer_switch_context();
    if (!$context) wp_die('Sesja przełączenia wygasła lub jest nieprawidłowa. Zaloguj się ponownie jako administrator.', '', ['response'=>403]);
    check_admin_referer('wtf_return_customer_'.hash('sha256',$context['secret']));
    $ticket=$context['ticket'];
    $admin=get_userdata($ticket['admin_id']);
    if (!wtf_customer_switch_allowed($admin) || $ticket['admin_expiration']<=time() || !WP_Session_Tokens::get_instance($ticket['admin_id'])->verify($ticket['admin_token'])) {
        wp_die('Sesja administratora została zakończona. Zaloguj się ponownie.', '', ['response'=>403]);
    }
    delete_transient(wtf_customer_switch_key($context['secret']));
    WP_Session_Tokens::get_instance($ticket['customer_id'])->destroy($ticket['customer_token']);
    nocache_headers();
    wtf_customer_switch_set_cookie('',time()-YEAR_IN_SECONDS);
    wtf_customer_switch_auth($ticket['admin_id'],$ticket['admin_token'],$ticket['admin_expiration']);
    wp_safe_redirect(admin_url('users.php'));
    exit;
}
add_action('admin_post_wtf_return_from_customer','wtf_customer_switch_return');
add_action('admin_post_nopriv_wtf_return_from_customer','wtf_customer_switch_return');

add_action('template_redirect',function () {
    if (!wtf_customer_switch_context()) return;
    if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE',true);
    nocache_headers();
},0);

add_action('wp_body_open',function () {
    $context=wtf_customer_switch_context();
    if (!$context) return;
    echo '<aside class="wtf-customer-switch-banner" aria-label="Przełączone konto"><span>Konto klienta: <strong>'.esc_html(wp_get_current_user()->display_name).'</strong></span><a class="wtf-customer-switch-return" href="'.esc_url(wtf_customer_switch_return_url($context)).'">Wróć do administratora</a></aside>';
});

add_filter('logout_url',function ($url) { return wtf_customer_switch_context()?wtf_customer_switch_return_url():$url; });
add_filter('woocommerce_account_menu_items',function ($items) {
    if (wtf_customer_switch_context() && isset($items['customer-logout'])) $items['customer-logout']='Wróć do administratora';
    return $items;
},20);
