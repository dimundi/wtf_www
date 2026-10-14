<?php
// Uruchamiany przez SSH na stdin, bez ładowania WordPressa.
umask(0077);
$source = '/home/oekkhvv-agent/whatthefrog.pl';
$target = '/home/oekkhvv-agent/wtf-export-' . gmdate('Ymd-His');
if (!mkdir($target, 0700)) { exit(1); }
echo "EXPORT_DIRECTORY=$target\n";
$config = file_get_contents($source . '/wp-config.php');
$values = [];
foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $key) {
    $pattern = '/define\s*\(\s*([\'\"])' . $key . '\1\s*,\s*([\'\"])((?:\\\\.|(?!\2).)*)\2\s*\)/s';
    if (!preg_match($pattern, $config, $match)) { throw new RuntimeException("Nie rozpoznano $key"); }
    $values[$key] = $match[2] === "'" ? str_replace(["\\'", "\\\\"], ["'", "\\"], $match[3]) : stripcslashes($match[3]);
}
mysqli_report(MYSQLI_REPORT_OFF);
$db = @new mysqli($values['DB_HOST'], $values['DB_USER'], $values['DB_PASSWORD'], $values['DB_NAME']);
if ($db->connect_errno) { throw new RuntimeException('Brak połączenia z bazą'); }
$statement = $db->prepare('SELECT table_name, engine FROM information_schema.tables WHERE table_schema = ? AND engine <> ?');
$engine = 'InnoDB';
$statement->bind_param('ss', $values['DB_NAME'], $engine);
$statement->execute();
$nonTransactional = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
echo 'NON_TRANSACTIONAL=' . json_encode($nonTransactional) . "\n";
$escape = static fn($value) => '"' . str_replace(["\\", '"', "\n", "\r"], ["\\\\", '\\"', '\\n', '\\r'], $value) . '"';
$credentials = $target . '/client.cnf';
$host = $values['DB_HOST'];
$port = 3306;
if (preg_match('/^(.+):(\d+)$/', $host, $parts)) { $host = $parts[1]; $port = (int) $parts[2]; }
file_put_contents($credentials, "[client]\nhost=" . $escape($host) . "\nport=$port\nuser=" . $escape($values['DB_USER']) . "\npassword=" . $escape($values['DB_PASSWORD']) . "\n");
chmod($credentials, 0600);
try {
    echo "DATABASE_EXPORT_STARTED\n";
    $command = ['mysqldump', '--defaults-extra-file=' . $credentials, '--single-transaction', '--skip-lock-tables', '--quick', '--hex-blob', '--no-tablespaces', '--default-character-set=utf8mb4', '--result-file=' . $target . '/database.sql', $values['DB_NAME']];
    $process = proc_open($command, [0 => ['file', '/dev/null', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', $target . '/database-errors.log', 'w']], $pipes);
    if (!is_resource($process) || proc_close($process) !== 0) { throw new RuntimeException('Eksport bazy nie powiódł się; sprawdź prywatny log'); }
} finally {
    unlink($credentials);
}
echo "DATABASE_EXPORT_COMPLETED\n";
$process = proc_open(['gzip', $target . '/database.sql'], [0 => ['file', '/dev/null', 'r'], 1 => STDOUT, 2 => STDERR], $pipes);
if (proc_close($process) !== 0) { throw new RuntimeException('Błąd kompresji SQL'); }
echo "FILES_ARCHIVE_STARTED\n";
$command = ['tar', '--exclude=./wp-content/updraft', '--exclude=./wp-content/upgrade-temp-backup', '-czf', $target . '/site-files.tar.gz', '-C', $source, '.'];
$process = proc_open($command, [0 => ['file', '/dev/null', 'r'], 1 => STDOUT, 2 => ['file', $target . '/archive-errors.log', 'w']], $pipes);
$archiveStatus = proc_close($process);
$warnings = trim(file_get_contents($target . '/archive-errors.log'));
$directoryOnly = $archiveStatus === 1 && preg_match('/^tar: \.\/wp-content\s*:\s*(?:fichier modifié pendant sa lecture|file changed as we read it)$/u', $warnings);
if ($archiveStatus !== 0 && !$directoryOnly) { throw new RuntimeException('Archiwum wymaga sprawdzenia; kod tar: ' . $archiveStatus); }
$metadata = ['created_utc' => gmdate('c'), 'source' => $source, 'excluded' => ['wp-content/updraft', 'wp-content/upgrade-temp-backup'], 'non_transactional_tables' => $nonTransactional, 'database_method' => 'single-transaction, no table locks; non-transactional tables are not a guaranteed consistent snapshot', 'files' => []];
$metadata['archive_warnings'] = $warnings;
foreach (['database.sql.gz', 'site-files.tar.gz'] as $file) {
    $metadata['files'][$file] = ['bytes' => filesize($target . '/' . $file), 'sha256' => hash_file('sha256', $target . '/' . $file)];
}
file_put_contents($target . '/manifest.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "BACKUP_READY\n" . json_encode($metadata, JSON_UNESCAPED_SLASHES) . "\n";
