<?php
// Local-only safeguards: never enabled on a production hostname/environment.
if (!defined('ABSPATH') || wp_get_environment_type()!=='local') return;
if (!in_array(wp_parse_url(home_url(),PHP_URL_HOST),['localhost','127.0.0.1','::1'],true)) return;
if (!defined('DISABLE_WP_CRON')) define('DISABLE_WP_CRON',true);
add_filter('pre_wp_mail','__return_false',PHP_INT_MAX);
add_filter('woocommerce_email_classes','__return_empty_array',PHP_INT_MAX);
add_filter('woocommerce_webhook_should_deliver','__return_false',PHP_INT_MAX);
add_filter('action_scheduler_allow_async_request_runner','__return_false',PHP_INT_MAX);
add_filter('action_scheduler_queue_runner_concurrent_batches','__return_zero',PHP_INT_MAX);
add_filter('pre_http_request',function ($pre,$args,$url) {
    $host=wp_parse_url($url,PHP_URL_HOST);
    // A fresh dev environment still needs official plugin/language packages.
    if (defined('WP_CLI') && WP_CLI && in_array($host,['api.wordpress.org','downloads.wordpress.org','translate.wordpress.org'],true)) return $pre;
    return in_array($host,['localhost','127.0.0.1','::1'],true)?$pre:new WP_Error('wtf_local_http_blocked','Środowisko lokalne: połączenia zewnętrzne wyłączone.');
},PHP_INT_MAX,3);
add_filter('woocommerce_available_payment_gateways','__return_empty_array',PHP_INT_MAX);
