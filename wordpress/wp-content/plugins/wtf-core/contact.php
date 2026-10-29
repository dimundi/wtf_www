<?php
if (!defined('ABSPATH')) { exit; }
require_once __DIR__.'/contact-captcha.php';

function wtf_contact_local_game_link($url) {
    if (untrailingslashit($url)!=='https://whatthefrog.pl/kontakt') { return $url; }
    $languages = array_unique([wtf_core_language(),'pl']);
    foreach ($languages as $lang) {
        $pages=get_posts(['post_type'=>'page','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_wtf_contact_key','meta_value'=>'main:'.$lang,'lang'=>$lang,'suppress_filters'=>true]);
        if ($pages) { return get_permalink($pages[0]); }
    }
    return $url;
}

add_action('init', function () {
    wp_register_script('wtf-contact-editor', plugins_url('contact-editor.js', __FILE__), ['wp-blocks','wp-element','wp-components','wp-block-editor'], filemtime(__DIR__.'/contact-editor.js'), true);
    $common = ['api_version'=>3, 'category'=>'wtf', 'editor_script'=>'wtf-contact-editor', 'supports'=>['html'=>false]];
    register_block_type('wtf/contact-container', $common + [
        'title'=>'Układ kontaktu',
        'attributes'=>['tag'=>['type'=>'string','default'=>'div'], 'attributes'=>['type'=>'object','default'=>[]], 'label'=>['type'=>'string','default'=>'Układ']],
        'render_callback'=>function ($a, $content) {
            $tag = in_array($a['tag'], ['main','section','div','article','aside','header','nav','details','summary','a','ul','ol','li','dl','dt','dd','picture','label','blockquote','figure','h1','h2','h3','address'], true) ? $a['tag'] : 'div';
            return '<'.$tag.wtf_contact_attributes($a['attributes']).'>'.$content.'</'.$tag.'>';
        },
    ]);
    register_block_type('wtf/contact-text', $common + [
        'title'=>'Tekst kontaktu',
        'attributes'=>['tag'=>['type'=>'string','default'=>'p'], 'content'=>['type'=>'string','default'=>''], 'attributes'=>['type'=>'object','default'=>[]]],
        'render_callback'=>function ($a) {
            $tag = in_array($a['tag'], ['p','h1','h2','h3','h4','h5','h6','span','a','li','dt','dd','strong','em','small','figcaption','label','address'], true) ? $a['tag'] : 'p';
            return '<'.$tag.wtf_contact_attributes($a['attributes']).'>'.wp_kses_post($a['content']).'</'.$tag.'>';
        },
    ]);
});
function wtf_contact_attributes($attrs) {
    $html = '';
    foreach ($attrs as $key=>$value) {
        if ($key==='style' && preg_match('/^--expansion-color:#[a-fA-F0-9]{6}$/',trim($value))) { $html.=' style="'.esc_attr(trim($value)).'"'; continue; }
        if (!in_array($key, ['class','id','lang','href','for','target','rel','title','role','open','aria-label','aria-current','aria-controls','aria-expanded'], true)) { continue; }
        if ($key === 'href') { $value = esc_url($value); }
        $html .= ' '.esc_attr($key).'="'.esc_attr($value).'"';
    }
    return $html;
}

add_action('init',function(){
 register_block_type('wtf/contact-form',['api_version'=>3,'title'=>'Formularz kontaktowy','category'=>'wtf','editor_script'=>'wtf-contact-editor','supports'=>['html'=>false],
 'attributes'=>array_fill_keys(['nameLabel','emailLabel','subjectLabel','messageLabel','buttonLabel'],['type'=>'string','default'=>'']),
 'render_callback'=>'wtf_contact_render_form']);
});

add_action('admin_init', static function () {
    register_setting('general','wtf_contact_recipient',['type'=>'string','sanitize_callback'=>'sanitize_email','default'=>'biuro@whatthefrog.pl']);
    add_settings_field('wtf_contact_recipient','Odbiorca formularza kontaktowego',static function () {
        echo '<input class="regular-text" type="email" name="wtf_contact_recipient" value="'.esc_attr(get_option('wtf_contact_recipient','biuro@whatthefrog.pl')).'" required>';
    },'general');
});

function wtf_contact_send($input, $ip) {
    $nonce=$input['wtf_contact_nonce']??'';
    if (!is_string($nonce) || !wp_verify_nonce($nonce,'wtf-contact')) return new WP_Error('session','Sesja formularza wygasła. Odśwież stronę i spróbuj ponownie.');
    if (!empty($input['website'])) return new WP_Error('spam','Nie udało się wysłać wiadomości. Skontaktuj się z nami przez e-mail.');
    foreach(['name','email','subject','message'] as $field) if (!isset($input[$field]) || !is_string($input[$field])) return new WP_Error('fields','Wypełnij wszystkie pola formularza.');
    $name=sanitize_text_field($input['name']);$email=trim($input['email']);$subject=sanitize_text_field($input['subject']);$message=sanitize_textarea_field($input['message']);
    if (!$name || mb_strlen($name)>100 || !$subject || mb_strlen($subject)>200 || trim($message)==='' || mb_strlen($message)>10000 || strlen($email)>254 || preg_match('/[\r\n]/',$email) || !is_email($email)) return new WP_Error('fields','Podaj imię, poprawny e-mail, temat i niepustą wiadomość (maksymalnie 10 000 znaków).');
    $recipient=get_option('wtf_contact_recipient','biuro@whatthefrog.pl');
    if (!is_string($recipient) || !is_email($recipient) || preg_match('/[\r\n]/',$recipient)) return new WP_Error('recipient','Formularz jest chwilowo niedostępny. Skontaktuj się z nami przez e-mail.');
    $key='wtf_contact_rate_'.hash_hmac('sha256',(string)$ip,wp_salt('nonce'));
    $attempts=(int)get_transient($key);
    if ($attempts>=5) return new WP_Error('rate','Wysłano zbyt wiele wiadomości. Spróbuj ponownie za 10 minut.');
    set_transient($key,$attempts+1,10*MINUTE_IN_SECONDS);
    $captcha=wtf_contact_captcha_verify($input['cf-turnstile-response']??'');
    if(is_wp_error($captcha)) return $captcha;
    $body="Wiadomość z formularza kontaktowego What The Frog\n\nImię: ".$name."\nE-mail: ".$email."\nTemat: ".$subject."\n\n".$message;
    if (!wp_mail($recipient,'[Kontakt WTF] '.$subject,$body,['Content-Type: text/plain; charset=UTF-8','Reply-To: '.$email])) return new WP_Error('mail','Nie udało się wysłać wiadomości. Spróbuj ponownie lub napisz do nas przez e-mail.');
    return true;
}

add_action('template_redirect',static function () {
    if (!is_page() || !get_post_meta(get_queried_object_id(),'_wtf_contact_key',true)) return;
    nocache_headers();
    if (($_SERVER['REQUEST_METHOD']??'')!=='POST' || ($_POST['wtf_contact_action']??'')!=='send') return;
    $input=wp_unslash($_POST);
    // WordPress rezerwuje m.in. parametr "name" jako zmienną zapytania strony.
    foreach (['name','email','subject','message'] as $field) $input[$field]=$input['wtf_contact_'.$field]??'';
    $result=wtf_contact_send($input,$_SERVER['REMOTE_ADDR']??'');
    if (is_wp_error($result)) { $GLOBALS['wtf_contact_error']=$result;$GLOBALS['wtf_contact_values']=$input;return; }
    wp_safe_redirect(add_query_arg('wiadomosc','wyslana',get_permalink()),303);
    exit;
});

function wtf_contact_render_form($a) {
    $defaults=['nameLabel'=>'Imię','emailLabel'=>'Adres e-mail','subjectLabel'=>'Temat','messageLabel'=>'Wiadomość','buttonLabel'=>'Wyślij wiadomość'];
    $e=static fn($key)=>esc_html(($a[$key]??'')?:$defaults[$key]);
    $values=$GLOBALS['wtf_contact_values']??[];$error=$GLOBALS['wtf_contact_error']??null;
    $value=static fn($key)=>esc_attr(isset($values[$key])&&is_string($values[$key])?$values[$key]:'');
    ob_start(); ?>
    <form class="contact-form" method="post" action="<?php echo esc_url(get_permalink()); ?>" aria-describedby="contact-form-result">
        <div id="contact-form-result" role="status" aria-live="polite"><?php if($error): ?><p class="contact-form-error" role="alert"><?php echo esc_html($error->get_error_message()); ?></p><?php elseif(($_GET['wiadomosc']??'')==='wyslana'): ?><p class="contact-form-success">Dziękujemy! Twoja wiadomość została wysłana.</p><?php endif; ?></div>
        <input type="hidden" name="wtf_contact_action" value="send"><?php wp_nonce_field('wtf-contact','wtf_contact_nonce',false); ?>
        <div class="contact-form-trap" aria-hidden="true"><label for="contact-website">Zostaw to pole puste</label><input id="contact-website" name="website" tabindex="-1" autocomplete="off"></div>
        <div class="contact-form-row"><div><label for="contact-name"><?php echo $e('nameLabel'); ?></label><input id="contact-name" type="text" name="wtf_contact_name" autocomplete="given-name" maxlength="100" required value="<?php echo $value('name'); ?>"></div><div><label for="contact-mail"><?php echo $e('emailLabel'); ?></label><input id="contact-mail" type="email" name="wtf_contact_email" autocomplete="email" maxlength="254" required value="<?php echo $value('email'); ?>"></div></div>
        <label for="contact-subject"><?php echo $e('subjectLabel'); ?></label><input id="contact-subject" type="text" name="wtf_contact_subject" maxlength="200" required value="<?php echo $value('subject'); ?>">
        <label for="contact-message"><?php echo $e('messageLabel'); ?></label><textarea id="contact-message" name="wtf_contact_message" rows="7" maxlength="10000" required><?php echo esc_textarea(isset($values['message'])&&is_string($values['message'])?$values['message']:''); ?></textarea>
        <p class="contact-form-privacy">Informacje o&nbsp;przetwarzaniu danych: <a href="<?php echo esc_url(home_url('/polityka-prywatnosci/')); ?>">polityka prywatności</a>.</p>
        <?php wtf_contact_captcha_render(); ?>
        <button type="submit" class="button" disabled><?php echo $e('buttonLabel'); ?></button>
    </form>
    <?php return ob_get_clean();
}
