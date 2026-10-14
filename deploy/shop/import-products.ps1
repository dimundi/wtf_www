param(
    [string]$SourceContainer = 'whatthefrogpl-backup-wordpress-1',
    [string]$TargetContainer = 'wtf-www-dev-wordpress-1'
)
$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path
function Invoke-ShopDocker([string[]]$Arguments) {
    & docker @Arguments
    if ($LASTEXITCODE -ne 0) { throw ('Docker: ' + $Arguments[0] + ' nie powiodlo sie.') }
}
$staging = Join-Path $projectRoot 'media/shop-import'
New-Item -ItemType Directory -Force $staging | Out-Null
Invoke-ShopDocker -Arguments @('cp', (Join-Path $projectRoot 'wordpress/wp-content/plugins/wtf-core/export-shop.php'), ($SourceContainer + ':/tmp/export-shop.php'))
Invoke-ShopDocker -Arguments @('exec', $SourceContainer, 'wp', 'eval-file', '/tmp/export-shop.php', '--allow-root')
Invoke-ShopDocker -Arguments @('cp', ($SourceContainer + ':/tmp/wtf-shop-export.json'), (Join-Path $staging 'products.json'))
$export = Get-Content (Join-Path $staging 'products.json') -Raw -Encoding UTF8 | ConvertFrom-Json
$files = @()
foreach ($entry in $export.media.PSObject.Properties) {
    $files += @{source=$entry.Value.file; target=($entry.Name + '-' + [IO.Path]::GetFileName($entry.Value.file))}
}
foreach ($entry in $export.inline_files.PSObject.Properties) {
    $files += @{source=$entry.Value.file; target=$entry.Value.key}
}
$done=0
foreach ($file in $files) {
    if (!$file.source.StartsWith('/var/www/html/wp-content/uploads/')) { throw 'Plik zrodlowy poza uploads.' }
    $target=Join-Path $staging ([IO.Path]::GetFileName($file.target))
    if (!(Test-Path -LiteralPath $target)) {
        Invoke-ShopDocker -Arguments @('cp', ($SourceContainer + ':' + $file.source), $target)
    }
    $done++
    if ($done % 20 -eq 0) { Write-Host ('Media: ' + $done + '/' + $files.Count) }
}
$protectedRoot=Join-Path $projectRoot 'wordpress/wp-content/uploads/woocommerce_uploads'
New-Item -ItemType Directory -Force $protectedRoot | Out-Null
Set-Content (Join-Path $protectedRoot '.htaccess') -Value 'Require all denied' -Encoding ascii
foreach ($product in $export.products) {
    foreach ($entry in $product.downloads.PSObject.Properties) {
        if (!$entry.Value.file) { continue }
        $uri=[Uri]$entry.Value.file
        $urlPath=[Uri]::UnescapeDataString($uri.AbsolutePath)
        if ($uri.Host -notin @('localhost','whatthefrog.pl','www.whatthefrog.pl') -or !$urlPath.StartsWith('/wp-content/uploads/woocommerce_uploads/')) { throw 'Plik cyfrowy wymaga osobnego sprawdzenia.' }
        $target=[IO.Path]::GetFullPath((Join-Path $projectRoot ('wordpress' + $urlPath)))
        if (!$target.StartsWith(([IO.Path]::GetFullPath($protectedRoot) + [IO.Path]::DirectorySeparatorChar),[StringComparison]::OrdinalIgnoreCase)) { throw 'Nieprawidlowa sciezka pliku cyfrowego.' }
        New-Item -ItemType Directory -Force ([IO.Path]::GetDirectoryName($target)) | Out-Null
        if (!(Test-Path -LiteralPath $target)) {
            Invoke-ShopDocker -Arguments @('cp', ($SourceContainer + ':/var/www/html' + $urlPath), $target)
        }
    }
}
foreach ($script in @('setup-shop.php','import-shop.php','complete-shop-media.php','setup-shop-elekt.php','setup-product-elekt.php','verify-shop.php')) {
    Invoke-ShopDocker -Arguments @('exec', $TargetContainer, 'wp', 'eval-file', ('wp-content/plugins/wtf-core/' + $script), '--allow-root')
}
Invoke-ShopDocker -Arguments @('exec', $TargetContainer, 'wp', 'rewrite', 'flush', '--allow-root')
Copy-Item (Join-Path $staging 'id-map.json') (Join-Path $projectRoot 'wtfDoc/sklep/mapowanie-produktow.json')
Write-Host 'Import zakonczony. Mapowania zapisane w dokumentacji.'
