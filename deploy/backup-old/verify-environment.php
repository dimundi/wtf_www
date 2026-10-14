<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli('db', getenv('WORDPRESS_DB_USER'), getenv('WORDPRESS_DB_PASSWORD'), getenv('WORDPRESS_DB_NAME'));
$tables = $db->query('SHOW TABLES')->num_rows;
echo 'PHP: ' . PHP_VERSION . "\n";
echo 'MySQL: ' . $db->server_info . "\n";
echo 'Tables: ' . $tables . "\n";
echo 'mail disabled: ' . (!function_exists('mail') ? 'yes' : 'no') . "\n";
$outside = @fsockopen('1.1.1.1', 443, $errno, $error, 3);
echo 'External network blocked: ' . (!$outside ? 'yes' : 'no') . "\n";
if ($outside) { fclose($outside); }
exit($tables === 0 && !function_exists('mail') && !$outside ? 0 : 1);
