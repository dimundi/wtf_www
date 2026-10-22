<?php
defined('ABSPATH') || exit;
$token=isset($_GET['aktywacja']) && is_string($_GET['aktywacja']) ? sanitize_text_field(wp_unslash($_GET['aktywacja'])) : '';
$activation=$token!=='';
$details=$activation ? wtf_registration_details($token) : false;
$sent=isset($_GET['wyslano']) && $_GET['wyslano']==='1';
do_action('woocommerce_before_customer_login_form');
?>
<h2><?php echo $activation ? 'Ustaw hasło' : ($sent ? 'Sprawdź skrzynkę' : 'Załóż konto'); ?></h2>
<?php if ($activation && !$details): ?>
    <p class="wtf-registration-description" role="alert">Link jest nieprawidłowy, wygasł albo został już użyty.</p>
    <a class="wtf-registration-primary" href="<?php echo esc_url(wtf_registration_url()); ?>">Wyślij nowy link</a>
<?php elseif ($sent): ?>
    <p class="wtf-registration-description" role="status">Wysłaliśmy wiadomość z linkiem. Otwórz go, aby ustawić hasło i aktywować konto.</p>
    <p class="wtf-registration-description">Link jest ważny przez 24 godziny. Jeśli nie widzisz wiadomości, sprawdź folder spam.</p>
    <a class="wtf-registration-secondary" href="<?php echo esc_url(wtf_registration_url()); ?>">Wyślij link ponownie lub popraw adres e-mail</a>
<?php else: ?>
    <p class="wtf-registration-description"><?php echo $activation ? 'Ustaw hasło, aby zakończyć rejestrację i aktywować konto.' : 'Podaj adres e-mail. Wyślemy Ci link do ustawienia hasła i aktywacji konta.'; ?></p>
    <form method="post" class="wtf-registration-form" action="<?php echo esc_url($activation ? add_query_arg('aktywacja',$token,wtf_registration_url()) : wtf_registration_url()); ?>">
    <?php if ($activation): ?>
        <p><label for="wtf-password">Hasło <span class="required" aria-hidden="true">*</span></label><input type="password" name="wtf_password" id="wtf-password" required minlength="8" autocomplete="new-password" aria-describedby="wtf-password-hint"><small id="wtf-password-hint">Co najmniej 8 znaków.</small></p>
        <p><label for="wtf-password-confirm">Powtórz hasło <span class="required" aria-hidden="true">*</span></label><input type="password" name="wtf_password_confirm" id="wtf-password-confirm" required minlength="8" autocomplete="new-password"></p>
        <input type="hidden" name="wtf_activation_token" value="<?php echo esc_attr($token); ?>">
    <?php else: ?>
        <p><label for="wtf-registration-email">Adres e-mail <span class="required" aria-hidden="true">*</span></label><input type="email" name="wtf_registration_email" id="wtf-registration-email" required autocomplete="email" value="<?php echo isset($_POST['wtf_registration_email']) && is_string($_POST['wtf_registration_email']) ? esc_attr(wp_unslash($_POST['wtf_registration_email'])) : ''; ?>"></p>
    <?php endif; ?>
    <?php if (get_privacy_policy_url()): ?><p class="wtf-registration-privacy">Informacje o przetwarzaniu danych znajdziesz w <a href="<?php echo esc_url(get_privacy_policy_url()); ?>">polityce prywatności</a>.</p><?php endif; ?>
    <input type="hidden" name="wtf_registration_action" value="<?php echo $activation ? 'activate' : 'request'; ?>">
    <?php wp_nonce_field('wtf-registration','wtf_registration_nonce'); ?>
    <button type="submit" class="button wtf-registration-primary"><?php echo $activation ? 'Aktywuj konto' : 'Wyślij link aktywacyjny'; ?></button>
    </form>
<?php endif; ?>
<p class="wtf-registration-back"><a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">Wróć do logowania</a></p>
<?php do_action('woocommerce_after_customer_login_form'); ?>
