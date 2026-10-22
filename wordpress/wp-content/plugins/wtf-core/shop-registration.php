<?php
if (!defined('ABSPATH')) exit;

function wtf_registration_url() {
    return add_query_arg('rejestracja','1',wc_get_page_permalink('myaccount'));
}

function wtf_registration_screen() {
    return !is_user_logged_in() && isset($_GET['rejestracja']) && is_string($_GET['rejestracja']) && $_GET['rejestracja']==='1';
}

add_action('template_redirect',function () {
    if (!wtf_registration_screen() || !is_account_page()) return;
    nocache_headers();
    header('Referrer-Policy: no-referrer');
});

// Only a hash of the bearer token is persisted; no user exists before verification.
function wtf_registration_token($email) {
    $token=bin2hex(random_bytes(32));
    set_transient('wtf_registration_'.hash('sha256',$token),['email'=>$email,'expires'=>time()+DAY_IN_SECONDS],DAY_IN_SECONDS);
    return $token;
}

function wtf_registration_details($token) {
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D',$token)) return false;
    $details=get_transient('wtf_registration_'.hash('sha256',$token));
    return is_array($details) && ($details['expires'] ?? 0)>time() ? $details : false;
}

function wtf_registration_request($email) {
    if (!is_string($email) || !is_email($email)) return new WP_Error('email','Podaj prawidłowy adres e-mail.');
    $email=sanitize_email($email);
    if (email_exists($email)) return new WP_Error('exists','Ten adres e-mail ma już konto. Zaloguj się lub skorzystaj z przypomnienia hasła.');
    $throttle='wtf_register_email_'.hash_hmac('sha256',strtolower($email),wp_salt());
    if (get_transient($throttle)) return new WP_Error('throttle','Odczekaj minutę przed ponowną prośbą o link.');
    set_transient($throttle,1,MINUTE_IN_SECONDS);
    $token=wtf_registration_token($email);
    $url=add_query_arg('aktywacja',$token,wtf_registration_url());
    $message="Cześć!\n\nAby założyć konto What The Frog, otwórz poniższy link i ustaw hasło:\n\n".$url."\n\nLink jest ważny przez 24 godziny i można go użyć tylko raz.\nKonto powstanie dopiero po ustawieniu hasła.\n\nJeśli nie prosisz o założenie konta, zignoruj tę wiadomość.\n\nWhat The Frog";
    if (!wp_mail($email,'Załóż konto What The Frog — potwierdź adres e-mail',$message,['Content-Type: text/plain; charset=UTF-8'])) {
        return new WP_Error('mail','Nie udało się wysłać linku. Spróbuj ponownie później.');
    }
    return true;
}

function wtf_registration_activate($token,$password,$confirmation) {
    $details=wtf_registration_details($token);
    if (!$details) return new WP_Error('token','Link jest nieprawidłowy, wygasł albo został już użyty. Poproś o nowy link.');
    if (!is_string($password) || !is_string($confirmation) || mb_strlen($password)<8) return new WP_Error('password','Hasło musi mieć co najmniej 8 znaków.');
    if (!hash_equals($password,$confirmation)) return new WP_Error('confirmation','Podane hasła muszą być identyczne.');
    $email=$details['email'];
    // Serialize creation for an email, including concurrent submissions of different links.
    $lock='wtf_register_lock_'.hash_hmac('sha256',strtolower($email),wp_salt());
    $locked_at=(int)get_option($lock,0);
    if ($locked_at && $locked_at<time()-120) delete_option($lock);
    if (!add_option($lock,time(),'','no')) return new WP_Error('busy','Rejestracja jest przetwarzana. Spróbuj ponownie za chwilę.');
    try {
        if (email_exists($email)) return new WP_Error('exists','Ten adres e-mail ma już konto. Zaloguj się lub skorzystaj z przypomnienia hasła.');
        $id=wc_create_new_customer($email,'',wp_slash($password),['meta_input'=>['_wtf_email_verified_at'=>gmdate('c')],'source'=>'wtf-registration']);
        if (is_wp_error($id)) return $id;
        delete_transient('wtf_registration_'.hash('sha256',$token));
        return $id;
    } finally {
        delete_option($lock);
    }
}

add_action('wp_loaded',function () {
    if (!isset($_POST['wtf_registration_action']) || is_user_logged_in()) return;
    $nonce=$_POST['wtf_registration_nonce'] ?? '';
    if (!is_string($nonce) || !wp_verify_nonce(wp_unslash($nonce),'wtf-registration')) {
        wc_add_notice('Sesja formularza wygasła. Odśwież stronę i spróbuj ponownie.','error');
        return;
    }
    $action=$_POST['wtf_registration_action'];
    if ($action==='request') {
        $ip=$_SERVER['REMOTE_ADDR'] ?? '';
        $limit='wtf_register_ip_'.hash_hmac('sha256',$ip,wp_salt());
        $attempts=(int)get_transient($limit);
        if ($attempts>=10) { wc_add_notice('Zbyt wiele prób. Spróbuj ponownie za kilka minut.','error'); return; }
        set_transient($limit,$attempts+1,10*MINUTE_IN_SECONDS);
        $email=$_POST['wtf_registration_email'] ?? '';
        $result=wtf_registration_request(is_string($email) ? wp_unslash($email) : '');
        if (is_wp_error($result)) { wc_add_notice($result->get_error_message(),'error'); return; }
        wp_safe_redirect(add_query_arg('wyslano','1',wtf_registration_url()));
        exit;
    }
    if ($action==='activate') {
        $token=$_POST['wtf_activation_token'] ?? '';
        $password=$_POST['wtf_password'] ?? '';
        $confirmation=$_POST['wtf_password_confirm'] ?? '';
        $result=wtf_registration_activate($token,is_string($password) ? wp_unslash($password) : '',is_string($confirmation) ? wp_unslash($confirmation) : '');
        if (is_wp_error($result)) { wc_add_notice($result->get_error_message(),'error'); return; }
        wc_set_customer_auth_cookie($result);
        wc_add_notice('Konto jest aktywne. Witaj w What The Frog!');
        wp_safe_redirect(wc_get_page_permalink('myaccount'));
        exit;
    }
},20);

add_filter('woocommerce_locate_template',function ($template,$name) {
    if ($name==='myaccount/form-login.php' && wtf_registration_screen()) return __DIR__.'/views/account-registration.php';
    return $template;
},30,2);

add_action('woocommerce_login_form_end',function () {
    if (!is_account_page()) return;
    echo '<div class="wtf-registration-invite"><span>Nie masz konta?</span><a href="'.esc_url(wtf_registration_url()).'">Załóż konto</a></div>';
});
