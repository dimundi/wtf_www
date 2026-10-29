<?php
if (!defined('ABSPATH')) exit;

add_filter('wtf_cookies_categories', function ($categories) {
    $categories['analytics']=['label'=>'Statystyki Google Analytics','description'=>'Pomiar odwiedzin, źródeł ruchu i korzystania z quizu. Pomaga nam rozwijać stronę. Bez reklam i remarketingu.'];
    return $categories;
});

add_action('admin_init', function () {
    register_setting('wtf_analytics','wtf_analytics_measurement_id',[
        'type'=>'string','default'=>'','sanitize_callback'=>function($value){
            $value=strtoupper(trim((string)$value));
            if($value!=='' && !preg_match('/^G-[A-Z0-9]{6,20}$/D',$value)){
                add_settings_error('wtf_analytics_measurement_id','invalid','Podaj identyfikator pomiaru G-…');return get_option('wtf_analytics_measurement_id','');
            }
            return $value;
        },
    ]);
});
add_action('admin_menu',function(){
    add_options_page('WTF Analityka','WTF Analityka','manage_options','wtf-analytics',function(){
        if(!current_user_can('manage_options'))return;
        echo '<div class="wrap"><h1>WTF Analityka</h1><p>GA4 uruchamia się wyłącznie po zgodzie na statystyki, na produkcyjnej domenie whatthefrog.pl. Lokalny DEV nie wysyła danych. Puste pole wyłącza integrację.</p><form method="post" action="options.php">';
        settings_fields('wtf_analytics');settings_errors();
        echo '<p><label>Identyfikator pomiaru <input name="wtf_analytics_measurement_id" value="'.esc_attr(get_option('wtf_analytics_measurement_id','')).'" placeholder="G-…" pattern="G-[A-Z0-9]{6,20}"></label></p>';
        submit_button();echo '</form></div>';
    });
});

add_action('wp_enqueue_scripts',function(){
    $id=get_option('wtf_analytics_measurement_id','');
    if(!preg_match('/^G-[A-Z0-9]{6,20}$/D',(string)$id) || current_user_can('manage_options') || is_search() || is_404())return;
    if((function_exists('is_account_page') && is_account_page()) || (function_exists('is_checkout') && is_checkout()) || (function_exists('is_cart') && is_cart()))return;
    wp_enqueue_script('wtf-analytics',plugins_url('assets/analytics.js',__FILE__),['wtf-cookies'],filemtime(__DIR__.'/assets/analytics.js'),true);
    wp_add_inline_script('wtf-analytics','window.wtfAnalyticsConfig='.wp_json_encode([
        'id'=>$id,'enabled'=>wp_get_environment_type()==='production' && wp_parse_url(home_url(),PHP_URL_HOST)==='whatthefrog.pl',
    ],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';','before');
},20);
