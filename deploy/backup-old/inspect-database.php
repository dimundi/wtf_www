<?php
// Odczyt konfiguracji bez uruchamiania WordPressa i jego zadań.
$config = file_get_contents('/home/oekkhvv-agent/whatthefrog.pl/wp-config.php');
$values = [];
foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $key) {
    $pattern = '/define\s*\(\s*([\'\"])' . $key . '\1\s*,\s*([\'\"])((?:\\\\.|(?!\2).)*)\2\s*\)/s';
    if (!preg_match($pattern, $config, $match)) {
        fwrite(STDERR, "Nie rozpoznano stałej konfiguracji: $key\n");
        exit(1);
    }
    $values[$key] = $match[2] === "'"
        ? str_replace(["\\'", "\\\\"], ["'", "\\"], $match[3])
        : stripcslashes($match[3]);
}
mysqli_report(MYSQLI_REPORT_OFF);
$db = @new mysqli($values['DB_HOST'], $values['DB_USER'], $values['DB_PASSWORD'], $values['DB_NAME']);
if ($db->connect_errno) {
    fwrite(STDERR, "Połączenie z bazą nie powiodło się (kod {$db->connect_errno}).\n");
    exit(1);
}
$result = $db->query('SELECT VERSION() AS version, @@version_comment AS distribution, @@sql_mode AS sql_mode, @@character_set_server AS character_set_server, @@collation_server AS collation_server, @@max_allowed_packet AS max_allowed_packet');
echo json_encode($result->fetch_assoc(), JSON_UNESCAPED_UNICODE) . "\n";
$statement = $db->prepare('SELECT COUNT(*) AS tables_count, ROUND(SUM(data_length + index_length)/1024/1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = ?');
$statement->bind_param('s', $values['DB_NAME']);
$statement->execute();
echo json_encode($statement->get_result()->fetch_assoc(), JSON_UNESCAPED_UNICODE) . "\n";
$statement = $db->prepare('SELECT engine, COUNT(*) AS tables_count FROM information_schema.tables WHERE table_schema = ? GROUP BY engine');
$statement->bind_param('s', $values['DB_NAME']);
$statement->execute();
foreach ($statement->get_result() as $row) {
    echo json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
}
