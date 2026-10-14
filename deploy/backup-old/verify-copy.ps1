param(
    [Parameter(Mandatory=$true)][string]$BackupDirectory,
    [Parameter(Mandatory=$true)][string]$DatabaseSha256,
    [Parameter(Mandatory=$true)][string]$FilesSha256,
    [Parameter(Mandatory=$true)][string]$RemoteDirectory
)
$ErrorActionPreference = 'Stop'
$directory = (Resolve-Path -LiteralPath $BackupDirectory).Path
$expected = @{'database.sql.gz'=$DatabaseSha256; 'site-files.tar.gz'=$FilesSha256}
$files = @{}
foreach ($name in $expected.Keys) {
    $path = Join-Path $directory $name
    $hash = (Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash.ToLowerInvariant()
    if ($hash -ne $expected[$name].ToLowerInvariant()) { throw "Checksum mismatch: $name" }
    $files[$name] = @{bytes=(Get-Item -LiteralPath $path).Length; sha256=$hash}
    Write-Output "SHA256 OK: $name"
}
$stream = [IO.File]::OpenRead((Join-Path $directory 'database.sql.gz'))
$gzip = [IO.Compression.GZipStream]::new($stream,[IO.Compression.CompressionMode]::Decompress)
$reader = [IO.StreamReader]::new($gzip)
$tables = 0
$complete = $false
try {
    while (-not $reader.EndOfStream) {
        $line = $reader.ReadLine()
        if ($line.StartsWith('CREATE TABLE ')) { $tables++ }
        if ($line.StartsWith('-- Dump completed on ')) { $complete=$true }
    }
} finally { $reader.Dispose(); $gzip.Dispose(); $stream.Dispose() }
if ($tables -ne 187 -or -not $complete) { throw 'Incomplete SQL dump' }
Write-Output "SQL OK: $tables tables"
$entries = @(& tar -tzf (Join-Path $directory 'site-files.tar.gz'))
if ($LASTEXITCODE -ne 0) { throw 'Invalid tar archive' }
foreach ($required in @('./wp-config.php','./wp-settings.php','./wp-content/plugins/woocommerce/woocommerce.php','./wp-content/themes/dimundi/style.css')) {
    if ($entries -notcontains $required) { throw "Missing file: $required" }
}
if (@($entries | Where-Object { $_ -match '^\./wp-content/(updraft|upgrade-temp-backup)(/|$)' }).Count -gt 0) { throw 'Old backups found in archive' }
if (@($entries | Where-Object { $_ -match '^(/|[A-Za-z]:)|(^|/)\.\.(/|$)' }).Count -gt 0) { throw 'Unsafe archive path' }
Write-Output "TAR OK: $($entries.Count) entries; old backups excluded"
$manifest = @{
    verified_utc=[DateTime]::UtcNow.ToString('o')
    source_site='https://whatthefrog.pl'
    remote_directory=$RemoteDirectory
    files=$files
    database_tables=$tables
    archive_entries=$entries.Count
    excluded=@('wp-content/updraft','wp-content/upgrade-temp-backup')
    archive_warning='Only the wp-content directory changed during reading; remote and local archive listing succeeded.'
    database_consistency='InnoDB single-transaction snapshot; media-folder MyISAM and Wordfence MEMORY tables are not transactionally consistent.'
}
[IO.File]::WriteAllText((Join-Path $directory 'manifest.json'),($manifest | ConvertTo-Json -Depth 6),[Text.UTF8Encoding]::new($false))
Write-Output 'VERIFICATION_COMPLETED'
