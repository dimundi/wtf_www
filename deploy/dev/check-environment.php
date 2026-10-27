<?php
// Uruchomienie: wp eval-file /usr/local/bin/check-environment.php --allow-root
if (!defined('WP_CLI') || !WP_CLI) exit(1);

$checks = [];
$checks['PHP 8.4 z łatami'] = PHP_MAJOR_VERSION === 8 && PHP_MINOR_VERSION === 4
    && version_compare(PHP_VERSION, '8.4.27', '>=');
foreach (['mysqli', 'mbstring', 'intl', 'gd', 'imagick', 'zip', 'exif', 'bcmath', 'curl', 'sodium'] as $extension) {
    $checks['PHP: ' . $extension] = extension_loaded($extension);
}
$checks['Wyłączony upload progress'] = !filter_var(ini_get('session.upload_progress.enabled'), FILTER_VALIDATE_BOOLEAN);
global $wpdb;
$server = $wpdb->get_row('SELECT VERSION() AS version, @@version_comment AS distribution, @@sql_mode AS sql_mode, @@max_allowed_packet AS max_allowed_packet', ARRAY_A);
$checks['Percona zgodna z OVH'] = $server && $server['version'] === '8.0.46-37'
    && str_contains($server['distribution'], 'Percona');
$checks['Tryb SQL zgodny z OVH'] = ($server['sql_mode'] ?? '') === 'NO_ENGINE_SUBSTITUTION';
$checks['Limit pakietu bazy zgodny z OVH'] = (int)($server['max_allowed_packet'] ?? 0) === 33554432;
$checks['Lokalne środowisko'] = wp_get_environment_type() === 'local'
    && in_array(wp_parse_url(home_url(), PHP_URL_HOST), ['localhost', '127.0.0.1'], true);
$checks['Cron wyłączony'] = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;
$checks['Maile klientów zablokowane'] = wp_mail('test@example.invalid', 'Test blokady', 'Test') === false;
$request = wp_remote_get('https://example.invalid');
$checks['HTTP zewnętrzne zablokowane'] = is_wp_error($request) && $request->get_error_code() === 'wtf_local_http_blocked';
$checks['Bramki płatnicze wyłączone'] = function_exists('WC') && WC()->payment_gateways()->get_available_payment_gateways() === [];
$checks['Webhooki wyłączone'] = apply_filters('woocommerce_webhook_should_deliver', true) === false;
$checks['Automatyczny runner wyłączony'] = apply_filters('action_scheduler_allow_async_request_runner', true) === false;
foreach ($checks as $name => $ok) {
    WP_CLI::log(($ok ? 'OK: ' : 'BŁĄD: ') . $name);
}
if (in_array(false, $checks, true)) WP_CLI::error('Kontrola środowiska nie przeszła.');
WP_CLI::success('Środowisko i lokalne blokady poprawne.');
