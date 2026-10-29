<?php
if (!defined('ABSPATH')) exit;

function wtf_contact_captcha_config($environment = null) {
    $local = in_array($environment ?? wp_get_environment_type(), ['local','development'], true);
    $site = defined('WTF_TURNSTILE_SITE_KEY') ? WTF_TURNSTILE_SITE_KEY : get_option('wtf_turnstile_site_key','');
    $secret = defined('WTF_TURNSTILE_SECRET_KEY') ? WTF_TURNSTILE_SECRET_KEY : get_option('wtf_turnstile_secret_key','');
    // Public Cloudflare dummy keys are confined to local/development environments.
    if ($local && !$site && !$secret) { $site='1x00000000000000000000AA';$secret='1x0000000000000000000000000000000AA'; }
    $dummy_sites=['1x00000000000000000000AA','2x00000000000000000000AB','1x00000000000000000000BB','2x00000000000000000000BB','3x00000000000000000000FF'];
    $dummy_secrets=['1x0000000000000000000000000000000AA','2x0000000000000000000000000000000AA','3x0000000000000000000000000000000AA'];
    $dummy = in_array($site,$dummy_sites,true) || in_array($secret,$dummy_secrets,true);
    return ['site'=>is_string($site)?$site:'','secret'=>is_string($secret)?$secret:'','ready'=>is_string($site)&&is_string($secret)&&$site!==''&&$secret!==''&&($local||!$dummy),'test'=>$local&&$dummy];
}

add_action('admin_init', static function () {
    register_setting('general','wtf_turnstile_site_key',['type'=>'string','sanitize_callback'=>'sanitize_text_field']);
    register_setting('general','wtf_turnstile_secret_key',['type'=>'string','sanitize_callback'=>static function($value){return is_string($value)&&trim($value)!==''?sanitize_text_field($value):get_option('wtf_turnstile_secret_key','');}]);
    add_settings_field('wtf_turnstile_site_key','CAPTCHA kontaktu — Site key',static function(){
        echo '<input class="regular-text" name="wtf_turnstile_site_key" value="'.esc_attr(get_option('wtf_turnstile_site_key','')).'" autocomplete="off"><p class="description">Cloudflare Turnstile, tryb Managed, domena whatthefrog.pl.</p>';
    },'general');
    add_settings_field('wtf_turnstile_secret_key','CAPTCHA kontaktu — Secret key',static function(){
        echo '<input class="regular-text" type="password" name="wtf_turnstile_secret_key" value="" autocomplete="new-password"><p class="description">Pozostaw puste, aby zachować zapisany klucz. Sekret nie jest wyświetlany w formularzu ani kodzie strony.</p>';
    },'general');
});

add_action('admin_notices',static function(){
    if(current_user_can('manage_options') && !wtf_contact_captcha_config()['ready']) echo '<div class="notice notice-warning"><p>Formularz kontaktowy wymaga kluczy Cloudflare Turnstile w Ustawienia → Ogólne. Bez nich wysyłka jest zablokowana.</p></div>';
});

add_action('wp_enqueue_scripts',static function(){
    if(!is_page() || !get_post_meta(get_queried_object_id(),'_wtf_contact_key',true) || !wtf_contact_captcha_config()['ready']) return;
    wp_enqueue_script('wtf-contact-captcha',get_theme_file_uri('assets/contact-captcha.js'),[],filemtime(get_theme_file_path('assets/contact-captcha.js')),true);
    wp_enqueue_script('wtf-contact-turnstile','https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=wtfContactCaptchaReady',['wtf-contact-captcha'],null,['strategy'=>'defer','in_footer'=>true]);
});

function wtf_contact_captcha_response_valid($result, $hostname, $test = false) {
    return is_array($result) && ($result['success']??false)===true && ($test || (
        isset($result['hostname'],$result['action']) && is_string($result['hostname']) &&
        strcasecmp($result['hostname'],$hostname)===0 && $result['action']==='contact'
    ));
}

function wtf_contact_captcha_verify($token) {
    $config=wtf_contact_captcha_config();
    if(!$config['ready']) return new WP_Error('captcha_config','Formularz jest chwilowo niedostępny. Skontaktuj się z nami przez e-mail.');
    if(!is_string($token)||$token===''||strlen($token)>2048) return new WP_Error('captcha','Potwierdź weryfikację antyspamową przed wysłaniem wiadomości.');
    $response=wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify',[
        'timeout'=>8,'redirection'=>0,'limit_response_size'=>16384,
        'body'=>['secret'=>$config['secret'],'response'=>$token],
    ]);
    if(is_wp_error($response)||wp_remote_retrieve_response_code($response)!==200) return new WP_Error('captcha_service','Nie udało się sprawdzić zabezpieczenia. Spróbuj ponownie za chwilę.');
    $result=json_decode(wp_remote_retrieve_body($response),true);
    if(!wtf_contact_captcha_response_valid($result,(string)wp_parse_url(home_url(),PHP_URL_HOST),$config['test'])) return new WP_Error('captcha','Weryfikacja antyspamowa nie powiodła się lub wygasła. Spróbuj ponownie.');
    return true;
}

function wtf_contact_captcha_render() {
    $config=wtf_contact_captcha_config();
    if(!$config['ready']){echo '<p class="contact-form-error">Formularz jest chwilowo niedostępny. Skontaktuj się z nami przez e-mail.</p>';return;}
    if($config['test']) echo '<p class="contact-captcha-test">CAPTCHA — lokalny tryb testowy</p>';
    echo '<div class="contact-captcha"><div class="wtf-contact-challenge" data-sitekey="'.esc_attr($config['site']).'"></div><p class="contact-captcha-status" role="status">Sprawdzanie zabezpieczenia antyspamowego…</p><noscript>Włącz JavaScript, aby przejść weryfikację, lub napisz do nas e-mailem.</noscript></div>';
}
