<?php
// Działa bez ładowania WordPressa; zmienia wyłącznie lokalną kopię.
$root = '/var/www/html';
if (is_file('/backup/restore-summary.json')) { throw new RuntimeException('Konfiguracja lokalna już przygotowana; nie nadpisujemy raportu źródłowego.'); }
$original = file_get_contents($root . '/wp-config.php');
if (!preg_match('/\$table_prefix\s*=\s*[\'\"]([a-zA-Z0-9_]+)[\'\"]/', $original, $match)) { throw new RuntimeException('Nie znaleziono prefiksu tabel'); }
$prefix = $match[1];
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('db', getenv('WORDPRESS_DB_USER'), getenv('WORDPRESS_DB_PASSWORD'), getenv('WORDPRESS_DB_NAME'));
$db->set_charset('utf8mb4');
$summary = ['tables' => $db->query('SHOW TABLES')->num_rows];
foreach (['users', 'posts'] as $table) { $summary[$table] = (int) $db->query("SELECT COUNT(*) AS n FROM `{$prefix}{$table}`")->fetch_assoc()['n']; }
$summary['post_types'] = $db->query("SELECT post_type, COUNT(*) AS n FROM `{$prefix}posts` GROUP BY post_type")->fetch_all(MYSQLI_ASSOC);
$summary['active_plugins_original'] = unserialize($db->query("SELECT option_value FROM `{$prefix}options` WHERE option_name='active_plugins'")->fetch_assoc()['option_value'], ['allowed_classes' => false]);
echo json_encode($summary, JSON_UNESCAPED_SLASHES) . "\n";
file_put_contents('/backup/restore-summary.json', json_encode($summary, JSON_PRETTY_PRINT));
$disabled = [];
$active = array_values(array_filter($summary['active_plugins_original'], function ($plugin) use (&$disabled) {
    if (preg_match('~^(wordfence|wps-hide-login|wp-maintenance-mode|official-facebook-pixel|wp-mail-smtp|updraftplus|paynow-woocommerce-master|woo-inpost)/~', $plugin)) { $disabled[] = $plugin; return false; }
    return true;
}));
$statement = $db->prepare("UPDATE `{$prefix}options` SET option_value=? WHERE option_name='active_plugins'");
$serialized = serialize($active);
$statement->bind_param('s', $serialized);
$statement->execute();
foreach (['home', 'siteurl'] as $name) {
    $statement = $db->prepare("UPDATE `{$prefix}options` SET option_value='http://localhost:8082' WHERE option_name=?");
    $statement->bind_param('s', $name);
    $statement->execute();
}
$config = <<<'CONFIG'
<?php
define('DB_NAME', getenv('WORDPRESS_DB_NAME'));
define('DB_USER', getenv('WORDPRESS_DB_USER'));
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASSWORD'));
define('DB_HOST', getenv('WORDPRESS_DB_HOST'));
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('WP_ENVIRONMENT_TYPE', 'local');
define('WP_HOME', 'http://localhost:8082');
define('WP_SITEURL', 'http://localhost:8082');
define('DISABLE_WP_CRON', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
define('DISALLOW_FILE_MODS', true);
define('WP_HTTP_BLOCK_EXTERNAL', true);
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');
CONFIG;
foreach (['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'] as $key) { $config .= "\ndefine('$key', '" . bin2hex(random_bytes(32)) . "');"; }
$config .= "\n\$table_prefix = '$prefix';\nif (!defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }\nrequire_once ABSPATH . 'wp-settings.php';\n";
file_put_contents($root . '/wp-config.php', $config);
file_put_contents($root . '/.user.ini', "; Lokalnie nie używamy ścieżek auto_prepend_file z hostingu.\n");
$htaccess = <<<'HTACCESS'
<FilesMatch "^(wp-config\.php|wp-cron\.php|\.user\.ini)$">
Require all denied
</FilesMatch>
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
HTACCESS;
file_put_contents($root . '/.htaccess', $htaccess . "\n");
if (!is_dir($root . '/wp-content/mu-plugins')) { mkdir($root . '/wp-content/mu-plugins', 0755, true); }
copy('/tmp/restore-tools/local-copy-guard.php', $root . '/wp-content/mu-plugins/local-copy-guard.php');
echo 'Locally disabled plugins: ' . json_encode($disabled) . "\nLOCAL_CONFIGURATION_READY\n";
