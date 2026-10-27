<?php
$private = dirname(__DIR__) . '/.wtf-staging';
if (!is_file($private . '/ovh-staging.json') || !is_file($private . '/runtime.json')) {
    http_response_code(503); exit('Wersja testowa w przygotowaniu.');
}
$db = json_decode(ltrim(file_get_contents($private . '/ovh-staging.json'), "\xEF\xBB\xBF"), true, 512, JSON_THROW_ON_ERROR);
$runtime = json_decode(file_get_contents($private . '/runtime.json'), true, 512, JSON_THROW_ON_ERROR);
define('DB_NAME', $db['db_name']);
define('DB_USER', $db['db_user']);
define('DB_PASSWORD', $db['db_password']);
define('DB_HOST', $db['db_host']);
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
foreach ($runtime['salts'] as $key => $value) define($key, $value);
unset($db, $runtime);
$table_prefix = 'wp_';
define('WP_HOME', 'https://whathefrog.pl');
define('WP_SITEURL', 'https://whathefrog.pl');
define('WP_ENVIRONMENT_TYPE', 'staging');
define('WTF_STAGING_SITE', true);
define('DISABLE_WP_CRON', true);
define('WP_HTTP_BLOCK_EXTERNAL', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('DISALLOW_FILE_EDIT', true);
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);
if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');
require_once ABSPATH . 'wp-settings.php';
