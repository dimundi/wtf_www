<?php
/** Plugin Name: Local backup isolation */
defined('ABSPATH') || exit;
$local_url = static function ($url) { return is_string($url) ? preg_replace('~https?://(?:www\.)?whatthefrog\.pl~i', 'http://localhost:8082', $url) : $url; };
foreach (['home_url', 'site_url', 'content_url', 'plugins_url', 'pll_home_url', 'wp_get_attachment_url', 'script_loader_src', 'style_loader_src', 'wp_redirect'] as $filter) {
    add_filter($filter, $local_url, PHP_INT_MAX);
}
add_filter('pre_wp_mail', '__return_false', PHP_INT_MAX);
add_filter('pre_http_request', static function () { return new WP_Error('local_copy_offline', 'Lokalna kopia: połączenia zewnętrzne wyłączone.'); }, PHP_INT_MAX);
add_filter('action_scheduler_allow_async_request_runner', '__return_false', PHP_INT_MAX);
add_filter('woocommerce_available_payment_gateways', '__return_empty_array', PHP_INT_MAX);
add_filter('woocommerce_email_classes', '__return_empty_array', PHP_INT_MAX);
add_action('init', static function () {
    if (class_exists('ActionScheduler')) { remove_action('action_scheduler_run_queue', [ActionScheduler::runner(), 'run']); }
}, PHP_INT_MAX);
add_action('send_headers', static function () {
    header("Content-Security-Policy: connect-src 'self'; form-action 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'");
});
