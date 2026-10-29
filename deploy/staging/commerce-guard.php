<?php
// Wyłącznie wersja testowa: przed premierą usunąć plik i konfigurację staging.
if (!defined('ABSPATH')) exit;
if (!defined('WTF_STAGING_SITE') || WTF_STAGING_SITE !== true) {
    http_response_code(503);
    exit('Brak konfiguracji wersji testowej.');
}
if (!defined('WTF_ENABLE_SMTP') || WTF_ENABLE_SMTP !== true) {
    add_filter('pre_wp_mail', '__return_false', PHP_INT_MAX);
    add_action('phpmailer_init', static function () {
        throw new \PHPMailer\PHPMailer\Exception('Wersja testowa: wysyłka maili wyłączona.');
    }, PHP_INT_MAX);
}
add_filter('pre_http_request', static function ($preempt, $args, $url) {
    if (defined('WTF_ENABLE_SMTP') && WTF_ENABLE_SMTP === true &&
        wp_parse_url($url, PHP_URL_SCHEME) === 'https' &&
        wp_parse_url($url, PHP_URL_HOST) === 'connect.wpmailsmtp.com' &&
        in_array(wp_parse_url($url, PHP_URL_PORT), [null, 443], true) &&
        rtrim((string) wp_parse_url($url, PHP_URL_PATH), '/') === '/domain-check') {
        return $preempt;
    }
    return new WP_Error('wtf_staging_http_blocked', 'Wersja testowa: zewnętrzne HTTP wyłączone.');
}, PHP_INT_MAX, 3);
if (!defined('WTF_ENABLE_PAYNOW') || WTF_ENABLE_PAYNOW !== true) {
    add_filter('woocommerce_available_payment_gateways', '__return_empty_array', PHP_INT_MAX);
}
add_filter('woocommerce_webhook_should_deliver', '__return_false', PHP_INT_MAX);
add_filter('action_scheduler_allow_async_request_runner', '__return_false', PHP_INT_MAX);
add_filter('action_scheduler_queue_runner_concurrent_batches', '__return_zero', PHP_INT_MAX);
add_filter('option_blog_public', '__return_zero', PHP_INT_MAX);
// Te integracje mogą używać transportu poza API HTTP WordPressa.
add_filter('option_active_plugins', static function ($plugins) {
    return array_values(array_filter($plugins, static fn($plugin) =>
        !str_starts_with($plugin, 'inpost-for-woocommerce/') &&
        (!str_starts_with($plugin, 'wp-mail-smtp/') || (defined('WTF_ENABLE_SMTP') && WTF_ENABLE_SMTP === true))));
});
add_action('send_headers', static function () { header('X-Robots-Tag: noindex, nofollow, noarchive'); });
