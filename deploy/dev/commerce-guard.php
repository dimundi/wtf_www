<?php
// Local-only safeguards: never enabled on a production hostname/environment.
if (!defined('ABSPATH') || wp_get_environment_type()!=='local') return;
if (!in_array(wp_parse_url(home_url(),PHP_URL_HOST),['localhost','127.0.0.1','::1'],true)) return;
if (!defined('DISABLE_WP_CRON')) define('DISABLE_WP_CRON',true);
function wtf_dev_mail_recipient_allowed($recipients) {
    $recipients=is_array($recipients) ? $recipients : explode(',',(string)$recipients);
    if (!$recipients) return false;
    foreach ($recipients as $recipient) {
        if (!is_string($recipient)) return false;
        $address=trim($recipient);
        if (preg_match('/^[^<>\r\n]*<([^<>]+)>$/D',$address,$match)) $address=trim($match[1]);
        if (strcasecmp($address,'kotow55@gmail.com')!==0) return false;
    }
    return true;
}
add_filter('pre_wp_mail',function ($pre,$mail) {
    if (!wtf_dev_mail_recipient_allowed($mail['to'] ?? [])) return false;
    $headers=$mail['headers'] ?? [];
    if (!is_array($headers)) $headers=explode("\n",str_replace("\r\n","\n",$headers));
    foreach ($headers as $header) {
        if (preg_match('/^(?:cc|bcc)\s*:(.*)$/i',trim($header),$match) && !wtf_dev_mail_recipient_allowed($match[1])) return false;
    }
    return $pre;
},PHP_INT_MAX,2);
// Recheck parsed envelope recipients, including folded CC/BCC headers and plugin changes.
add_action('phpmailer_init',function ($mailer) {
    if (!wtf_dev_mail_recipient_allowed(array_keys($mailer->getAllRecipientAddresses()))) {
        throw new \PHPMailer\PHPMailer\Exception('Środowisko lokalne: odbiorca spoza listy dozwolonych adresów.');
    }
},PHP_INT_MAX);
// Keep the classes: WooCommerce directly calls the new-account email instance.
// Disable delivery instead, with pre_wp_mail above as the final transport guard.
add_filter('woocommerce_email_classes',function ($emails) {
    foreach ($emails as $email) {
        if (is_object($email) && isset($email->id)) add_filter('woocommerce_email_enabled_'.$email->id,'__return_false',PHP_INT_MAX);
    }
    return $emails;
},PHP_INT_MAX);
add_filter('woocommerce_webhook_should_deliver','__return_false',PHP_INT_MAX);
add_filter('action_scheduler_allow_async_request_runner','__return_false',PHP_INT_MAX);
add_filter('action_scheduler_queue_runner_concurrent_batches','__return_zero',PHP_INT_MAX);
add_filter('pre_http_request',function ($pre,$args,$url) {
    $host=wp_parse_url($url,PHP_URL_HOST);
    // Wyłącznie oficjalny endpoint CAPTCHA z publicznym kluczem testowym.
    if ($url==='https://challenges.cloudflare.com/turnstile/v0/siteverify' && ($args['body']['secret']??'')==='1x0000000000000000000000000000000AA') return $pre;
    // A fresh dev environment still needs official plugin/language packages.
    if (defined('WP_CLI') && WP_CLI && in_array($host,['api.wordpress.org','downloads.wordpress.org','translate.wordpress.org'],true)) return $pre;
    return in_array($host,['localhost','127.0.0.1','::1'],true)?$pre:new WP_Error('wtf_local_http_blocked','Środowisko lokalne: połączenia zewnętrzne wyłączone.');
},PHP_INT_MAX,3);
add_filter('woocommerce_available_payment_gateways','__return_empty_array',PHP_INT_MAX);
