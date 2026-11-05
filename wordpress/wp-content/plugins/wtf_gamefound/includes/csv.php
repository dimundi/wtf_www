<?php
if (!defined('ABSPATH')) { exit; }

function wtfg_open_csv($path) {
    $handle=fopen($path,'rb');
    if (!$handle) { throw new RuntimeException('Nie można odczytać pliku.'); }
    try {
        $prefix=fread($handle,3); rewind($handle);
        if (str_starts_with($prefix,"\xFF\xFE") || str_starts_with($prefix,"\xFE\xFF")) {
            $encoding=str_starts_with($prefix,"\xFF\xFE") ? 'UTF-16LE' : 'UTF-16BE';
            fseek($handle,2);
            if (!@stream_filter_append($handle,'convert.iconv.'.$encoding.'/UTF-8',STREAM_FILTER_READ)) {
                throw new RuntimeException('Nie można odczytać kodowania UTF-16. Zapisz plik jako CSV UTF-8.');
            }
        } elseif ($prefix === "\xEF\xBB\xBF") { fseek($handle,3); }
        $headers=fgetcsv($handle,0,',','"','');
        if (!$headers) { throw new RuntimeException('Plik jest pusty.'); }
        return [$handle,array_map(fn($header)=>trim((string)$header),$headers)];
    } catch (Throwable $error) { fclose($handle); throw $error; }
}
