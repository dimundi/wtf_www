<?php
require '/var/www/html/wp-load.php';
global $wpdb;
$summary = json_decode(file_get_contents('/backup/restore-summary.json'), true);
$users = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
$orders = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type=%s", 'shop_order'));
$products = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type=%s", 'product'));
$images = get_posts(['post_type'=>'attachment','post_mime_type'=>'image','posts_per_page'=>1,'fields'=>'ids']);
$image = $images ? wp_get_attachment_url($images[0]) : null;
$http = wp_remote_get('https://whatthefrog.pl/');
$mail = wp_mail('nobody@example.invalid', 'Local verification', 'Local verification');
$gateways = apply_filters('woocommerce_available_payment_gateways', ['verification' => true]);
$report = [
    'wordpress' => get_bloginfo('version'), 'theme' => get_stylesheet(),
    'home' => home_url('/'), 'shop' => wc_get_page_permalink('shop'),
    'users' => $users, 'orders' => $orders, 'products' => $products,
    'image' => $image,
    'cron_disabled' => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON,
    'mail_blocked' => $mail === false,
    'http_blocked' => is_wp_error($http) && $http->get_error_code() === 'local_copy_offline',
    'payment_gateways_blocked' => empty($gateways),
];
echo json_encode($report, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
if ($users !== $summary['users'] || $orders !== 2099 || $products !== 158 || !$report['cron_disabled'] || !$report['mail_blocked'] || !$report['http_blocked'] || !$report['payment_gateways_blocked']) { exit(1); }
file_put_contents('/backup/restore-verification.json', json_encode($report, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
