<?php
if (!defined('ABSPATH')) exit;

add_filter('wtf_cookies_categories', function ($categories) {
    $categories['marketing'] = ['label'=>'Reklamy Meta', 'description'=>'Pomiar skuteczności reklam na Facebooku i Instagramie oraz ponowne docieranie z reklamą do odwiedzających. Meta otrzymuje informacje o odwiedzanych stronach, oglądanych produktach i urządzeniu.'];
    return $categories;
});

add_action('admin_init', function () {
    register_setting('wtf_meta_pixel', 'wtf_meta_pixel_id', [
        'type'=>'string', 'default'=>'',
        'sanitize_callback'=>function ($value) {
            $value=trim((string)$value);
            if ($value!=='' && !preg_match('/^[0-9]{5,25}$/D', $value)) {
                add_settings_error('wtf_meta_pixel_id','invalid','Podaj liczbowy identyfikator piksela Meta.');
                return get_option('wtf_meta_pixel_id','');
            }
            return $value;
        },
    ]);
});

add_action('admin_menu', function () {
    add_options_page('WTF Meta Pixel','WTF Meta Pixel','manage_options','wtf-meta-pixel',function () {
        if (!current_user_can('manage_options')) return;
        echo '<div class="wrap"><h1>WTF Meta Pixel</h1><p>Pixel uruchamia się wyłącznie po zgodzie na reklamy Meta i tylko na produkcyjnej domenie whatthefrog.pl. Lokalny DEV nie wysyła danych. Puste pole wyłącza integrację.</p><p>Obecny zakres: PageView i ViewContent. Bez API konwersji, automatycznego dopasowania danych klienta i automatycznego zbierania zdarzeń.</p><form method="post" action="options.php">';
        settings_fields('wtf_meta_pixel'); settings_errors();
        echo '<p><label>Identyfikator piksela <input name="wtf_meta_pixel_id" value="'.esc_attr(get_option('wtf_meta_pixel_id','')).'" inputmode="numeric" pattern="[0-9]{5,25}"></label></p>';
        submit_button(); echo '</form></div>';
    });
});

// Meta's SDK reads the current URL. Never start it on private pages or URLs
// containing unexpected query parameters; do not rewrite visitors' URLs.
function wtf_meta_pixel_public_request() {
    if (is_admin() || is_search() || is_404() || is_preview() || is_feed() || current_user_can('manage_options')) return false;
    if (preg_match('~^/(?:wp-admin|wp-login\.php|moje-konto|koszyk|zamowienie|checkout)(?:/|$)~', wp_parse_url(wp_unslash($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '')) return false;
    foreach (['is_account_page','is_cart','is_checkout'] as $condition) {
        if (function_exists($condition) && $condition()) return false;
    }
    $allowed=['utm_source','utm_medium','utm_campaign','utm_content','utm_term','fbclid','game','world','sort'];
    foreach ($_GET as $key=>$value) {
        if (!in_array($key,$allowed,true) || !is_string($value)) return false;
        $value=wp_unslash($value);
        $pattern=$key==='fbclid' ? '/^[A-Za-z0-9_.-]{1,500}$/D' : '/^[A-Za-z0-9_. -]{0,100}$/D';
        if (!preg_match($pattern,$value)) return false;
    }
    return true;
}

add_action('wp_enqueue_scripts', function () {
    $id=get_option('wtf_meta_pixel_id','');
    if (!preg_match('/^[0-9]{5,25}$/D',(string)$id) || is_admin()) return;
    $product=null;
    if (function_exists('is_product') && is_product()) {
        $p=wc_get_product(get_queried_object_id());
        if ($p && $p->get_status()==='publish') {
            $product=['content_type'=>'product','content_ids'=>[(string)$p->get_id()],'content_name'=>$p->get_name()];
        }
    }
    wp_enqueue_script('wtf-meta-pixel',plugins_url('assets/meta-pixel.js',__FILE__),['wtf-cookies'],filemtime(__DIR__.'/assets/meta-pixel.js'),true);
    wp_add_inline_script('wtf-meta-pixel','window.wtfMetaPixelConfig='.wp_json_encode([
        'id'=>$id,'enabled'=>wp_get_environment_type()==='production' && wp_parse_url(home_url(),PHP_URL_HOST)==='whatthefrog.pl' && wtf_meta_pixel_public_request(),'publicRequest'=>wtf_meta_pixel_public_request(),'product'=>$product,
    ],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';','before');
},20);
