<?php
if (!defined('ABSPATH')) { exit; }

function wtfg_parse_products_csv($path,$project='') {
    [$handle,$headers]=wtfg_open_csv($path);
    $catalog = []; $rows = 0; $skipped = [];
    try {
        $matches=array_keys($headers,'ProductName',true);
        if (!$matches) { throw new RuntimeException('Nie znaleziono kolumny ProductName. Plik może zawierać wiele kolumn; odczytywana jest tylko ProductName. Separator: przecinek.'); }
        if (count($matches)>1) { throw new RuntimeException('Nagłówek ProductName występuje wielokrotnie. Pozostałe kolumny mogą mieć dowolne nazwy.'); }
        $index = array_search('ProductName', $headers, true);
        $project_index=array_search('ProjectID',$headers,true);
        while (($fields = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $rows++;
            if ($rows > 50000) { throw new RuntimeException('Limit pierwszej wersji: 50 000 wierszy na plik.'); }
            if ($fields === [null]) { continue; }
            if ($project!=='' && $project_index!==false && trim((string)($fields[$project_index] ?? ''))!==$project) { continue; }
            if (!array_key_exists($index,$fields)) { throw new RuntimeException('Brak pola ProductName w rekordzie '.($rows+1).'.'); }
            $raw = trim((string)$fields[$index]);
            if ($raw === '') { continue; }
            if (!preg_match('//u', $raw)) { throw new RuntimeException('Plik musi mieć kodowanie UTF-8. Rekord '.($rows+1).'.'); }
            if ($raw === 'Bonus amount') {
                $matches=[$raw,'Bonus amount','1',''];
            } elseif (strlen($raw)>2000 || !preg_match('/^(.+?)\s*\[([0-9]{1,64})\]\s*(?:\(([^\r\n]*)\))?\s*$/u', $raw, $matches)) {
                $skipped[]=['record'=>$rows+1,'reason'=>'Nieprawidłowy format ProductName.'];
                continue;
            }
            $name = preg_replace('/^(?:Free Gift:\s*)+/iu', '', trim($matches[1]));
            $name = preg_replace('/\s+/u',' ',trim($name));
            $variant = preg_replace('/\s+/u',' ',trim($matches[3] ?? ''));
            $id = $matches[2];
            if ($name === '') { $skipped[]=['record'=>$rows+1,'reason'=>'Brak nazwy produktu.']; continue; }
            if (isset($catalog[$id]) && $catalog[$id]['name'] !== $name) {
                throw new RuntimeException('Sprzeczne nazwy dla tego samego ID produktu w rekordzie '.($rows+1).'. Popraw plik przed importem.');
            }
            if (!isset($catalog[$id])) { $catalog[$id] = ['name'=>$name,'variants'=>[]]; }
            if (!in_array($variant,$catalog[$id]['variants'],true)) { $catalog[$id]['variants'][] = $variant; }
            if (count($catalog)>5000) { throw new RuntimeException('Limit pierwszej wersji: 5000 produktów na plik.'); }
        }
        if (!$catalog) { throw new RuntimeException('Brak produktów do zaimportowania.'); }
        foreach ($catalog as &$product) { sort($product['variants'], SORT_STRING); }
        unset($product); ksort($catalog, SORT_STRING);
        return [$catalog,$rows,$skipped];
    } finally { fclose($handle); }
}

function wtfg_supporter_fields() {
    return [
        'CustomerFullName'=>'Imię i nazwisko', 'CustomerEmail'=>'E-mail', 'CustomerPhoneNumber'=>'Telefon',
        'CustomerAddressLine1'=>'Adres — wiersz 1','CustomerAddressLine2'=>'Adres — wiersz 2','CustomerAddressLine3'=>'Adres — wiersz 3',
        'CustomerCity'=>'Miasto','CustomerPostalCode'=>'Kod pocztowy','CustomerStateOrProvince'=>'Region',
        'CustomerLocationIsoCode'=>'Kod kraju','CustomerIsBusinessEntity'=>'Klient firmowy',
        'CustomerVatNumber'=>'Numer VAT','TaxpayerId'=>'Identyfikator podatkowy',
    ];
}

function wtfg_parse_supporters_csv($path,$configured) {
    [$handle,$headers]=wtfg_open_csv($path);
    $people=[]; $projects=[]; $skipped=[]; $conflicts=[]; $rows=0;
    try {
        foreach (array_unique(array_merge(['ProjectID','UserID','CustomerFullName','CustomerEmail'],array_keys(wtfg_supporter_fields()))) as $field) {
            $count=count(array_keys($headers,$field,true));
            if ($count>1) { throw new RuntimeException('Powielona kolumna '.$field.'.'); }
            if (!$count && in_array($field,['ProjectID','UserID','CustomerFullName','CustomerEmail'],true)) { throw new RuntimeException('Brak wymaganej kolumny '.$field.'.'); }
        }
        $columns=array_flip($headers);
        while (($record=fgetcsv($handle,0,',','"',''))!==false) {
            $rows++; $number=$rows+1;
            if ($rows>50000) { throw new RuntimeException('Limit: 50 000 rekordów na plik.'); }
            if ($record===[null]) { continue; }
            $project=trim((string)($record[$columns['ProjectID']] ?? ''));
            $user=trim((string)($record[$columns['UserID']] ?? ''));
            if (!preg_match('/^[0-9]{1,64}$/D',$project) || !preg_match('/^[0-9]{1,64}$/D',$user)) {
                $skipped[]=['record'=>$number,'reason'=>'Brak lub nieprawidłowy ProjectID/UserID.']; continue;
            }
            if ($configured!=='' && $configured!==$project) { $skipped[]=['record'=>$number,'reason'=>'Inny ProjectID niż w konfiguracji zbiórki.']; continue; }
            $projects[$project]=true;
            if (count($projects)>1) { throw new RuntimeException('Plik zawiera kilka ProjectID. Wpisz ProjectID w konfiguracji zbiórki.'); }
            $data=[]; $valid=true;
            foreach (wtfg_supporter_fields() as $field=>$label) {
                if (!isset($columns[$field])) { continue; }
                if (!array_key_exists($columns[$field],$record)) { $valid=false; break; }
                $value=trim((string)$record[$columns[$field]]);
                if (strlen($value)>2000 || !preg_match('//u',$value)) { $valid=false; break; }
                $data[$field]=sanitize_text_field($value);
            }
            if (!$valid || ($data['CustomerEmail']!=='' && !is_email($data['CustomerEmail']))) {
                $skipped[]=['record'=>$number,'reason'=>'Nieprawidłowe lub niekompletne dane kontaktowe.']; continue;
            }
            if (!isset($people[$user])) { $people[$user]=$data; }
            else {
                foreach ($data as $field=>$value) {
                    $old=$people[$user][$field] ?? '';
                    if ($old!=='' && $value!=='' && $old!==$value) { $conflicts[$user][$field]=true; }
                    elseif ($old==='' && $value!=='') { $people[$user][$field]=$value; }
                }
            }
            if (count($people)>5000) { throw new RuntimeException('Limit: 5000 wspierających na plik.'); }
        }
        foreach ($conflicts as $user=>$fields) { unset($people[$user]); }
        if (!$people) { throw new RuntimeException('Brak poprawnych, jednoznacznych danych wspierających dla tej zbiórki.'); }
        ksort($people,SORT_STRING);
        return ['people'=>$people,'project'=>(string)array_key_first($projects),'rows'=>$rows,'skipped'=>$skipped,'conflicts'=>array_map('array_keys',$conflicts)];
    } finally { fclose($handle); }
}

function wtfg_merge_supporter($current,$incoming) {
    foreach ($incoming as $field=>$value) {
        if ($value==='' && ($current[$field] ?? '')!=='') { continue; }
        $current[$field]=$value;
    }
    return $current;
}

function wtfg_supporter_encrypt($value) {
    $iv=random_bytes(12); $tag='';
    $cipher=openssl_encrypt(wp_json_encode($value),'aes-256-gcm',hash('sha256',wp_salt('auth'),true),OPENSSL_RAW_DATA,$iv,$tag);
    if ($cipher===false) { throw new RuntimeException('Nie można przygotować podglądu.'); }
    return wp_json_encode(['iv'=>base64_encode($iv),'tag'=>base64_encode($tag),'data'=>base64_encode($cipher)]);
}

function wtfg_supporter_decrypt($payload) {
    $packet=json_decode($payload,true,512,JSON_THROW_ON_ERROR);
    $plain=openssl_decrypt(base64_decode($packet['data'],true),'aes-256-gcm',hash('sha256',wp_salt('auth'),true),OPENSSL_RAW_DATA,base64_decode($packet['iv'],true),base64_decode($packet['tag'],true));
    if ($plain===false) { throw new RuntimeException('Podgląd jest niedostępny. Wykonaj import ponownie.'); }
    return json_decode($plain,true,512,JSON_THROW_ON_ERROR);
}

function wtfg_order_fields() {
    return ['ProjectID','OrderID','OrderPublicID','UserID','ProductsOrdered','OrderStage','OrderPaymentStatus','Order value with shipping (transaction currency)','TotalPaid','CurrencyShortName'];
}

function wtfg_order_data($data) {
    return array_intersect_key($data,array_fill_keys(wtfg_order_fields(),true));
}

function wtfg_parse_orders_csv($path,$configured) {
    [$handle,$headers]=wtfg_open_csv($path);
    $orders=[]; $projects=[]; $skipped=[]; $conflicts=[]; $rows=0;
    try {
        $required=['ProjectID','OrderID','UserID','ProductsOrdered'];
        foreach (wtfg_order_fields() as $field) {
            $count=count(array_keys($headers,$field,true));
            if ($count>1) { throw new RuntimeException('Powielona kolumna '.$field.'.'); }
            if (!$count && in_array($field,$required,true)) { throw new RuntimeException('Brak wymaganej kolumny '.$field.'. Użyj eksportu Orders.'); }
        }
        $columns=array_flip($headers);
        while (($record=fgetcsv($handle,0,',','"',''))!==false) {
            $rows++; $number=$rows+1;
            if ($rows>50000) { throw new RuntimeException('Limit: 50 000 rekordów na plik.'); }
            if ($record===[null]) { continue; }
            $project=trim((string)($record[$columns['ProjectID']] ?? ''));
            $order=trim((string)($record[$columns['OrderID']] ?? ''));
            $user=trim((string)($record[$columns['UserID']] ?? ''));
            if (!preg_match('/^[0-9]{1,64}$/D',$project) || !preg_match('/^[0-9]{1,64}$/D',$order) || ($user!=='' && !preg_match('/^[0-9]{1,64}$/D',$user))) {
                $skipped[]=['record'=>$number,'reason'=>'Nieprawidłowy ProjectID, OrderID lub UserID.']; continue;
            }
            if ($configured!=='' && $configured!==$project) { $skipped[]=['record'=>$number,'reason'=>'Inny ProjectID niż w konfiguracji zbiórki.']; continue; }
            $projects[$project]=true;
            if (count($projects)>1) { throw new RuntimeException('Plik zawiera kilka ProjectID. Wpisz ProjectID w konfiguracji zbiórki.'); }
            $data=[]; $valid=true;
            foreach (wtfg_order_fields() as $field) {
                if (!isset($columns[$field])) { continue; }
                if (!array_key_exists($columns[$field],$record)) { $valid=false; break; }
                $value=(string)$record[$columns[$field]];
                if (strlen($value)>65536 || !preg_match('//u',$value)) { $valid=false; break; }
                // Celowo bez trim/sanitizacji: ProductsOrdered i dane źródłowe 1:1.
                // Wszystkie wartości są escapowane dopiero przy wyświetlaniu.
                $data[$field]=$value;
            }
            if (!$valid) { $skipped[]=['record'=>$number,'reason'=>'Niekompletny rekord, zbyt długie pole lub nieprawidłowe kodowanie.']; continue; }
            $data['ProjectID']=$project; $data['OrderID']=$order; $data['UserID']=$user;
            if (isset($orders[$order]) && $orders[$order]!==$data) { $conflicts[$order]=true; }
            else { $orders[$order]=$data; }
            if (count($orders)>5000) { throw new RuntimeException('Limit: 5000 zamówień na plik.'); }
        }
        foreach ($conflicts as $order=>$unused) { unset($orders[$order]); }
        if (!$orders) { throw new RuntimeException('Brak poprawnych, jednoznacznych zamówień. Użyj eksportu Orders, nie Order items.'); }
        ksort($orders,SORT_STRING);
        return ['orders'=>$orders,'project'=>(string)array_key_first($projects),'rows'=>$rows,'skipped'=>$skipped,'conflicts'=>array_map('strval',array_keys($conflicts))];
    } finally { fclose($handle); }
}

function wtfg_stage_label($stage) {
    $stage=trim((string)$stage);
    return ['canceled'=>'Anulowane (Canceled)','cancelled'=>'Anulowane (Cancelled)','pledged'=>'Zadeklarowane (Pledged)'][strtolower($stage)] ?? ($stage!==''?$stage:'Brak danych');
}

function wtfg_parse_items_csv($path,$project) {
    [$handle,$headers]=wtfg_open_csv($path); $result=[]; $rows=0; $skipped=[]; $occurrences=[];
    try {
        foreach (['ProjectID','OrderID','UserID','ProductName','Quantity'] as $field) {
            if (count(array_keys($headers,$field,true))!==1) { throw new RuntimeException('Wymagana jedna kolumna o nazwie '.$field.'. Użyj eksportu Order items.'); }
        }
        $columns=array_flip($headers);
        while (($values=fgetcsv($handle,0,',','"',''))!==false) {
            $rows++; if ($rows>50000) { throw new RuntimeException('Limit: 50 000 rekordów na plik.'); }
            if ($values===[null]) { continue; }
            $source_project=trim((string)($values[$columns['ProjectID']] ?? ''));
            $order=trim((string)($values[$columns['OrderID']] ?? ''));
            $user=trim((string)($values[$columns['UserID']] ?? ''));
            if (!preg_match('/^[0-9]{1,64}$/D',$user)) { $user=''; }
            if (!preg_match('/^[0-9]{1,64}$/D',$order)) { $skipped[]=['record'=>$rows+1,'reason'=>'Nieprawidłowy OrderID.']; continue; }
            if ($source_project!==$project) { $skipped[]=['record'=>$rows+1,'reason'=>'Inny ProjectID niż w konfiguracji zbiórki.']; continue; }
            if (!array_key_exists($columns['ProductName'],$values) || !array_key_exists($columns['Quantity'],$values)) { throw new RuntimeException('Niekompletny rekord '.($rows+1).'. Nie można ustalić pełnej zawartości zamówienia.'); }
            $raw=(string)$values[$columns['ProductName']]; $quantity=trim((string)$values[$columns['Quantity']]);
            if (!preg_match('//u',$raw) || !preg_match('//u',$quantity) || strlen($raw)>2000 || strlen($quantity)>128) { throw new RuntimeException('Nieprawidłowy zapis lub zbyt długie pole w rekordzie '.($rows+1).'.'); }
            $external=''; $variant='';
            if (trim($raw)==='Bonus amount') { $external='1'; }
            elseif (preg_match('/^(.+?)\s*\[([0-9]{1,64})\]\s*(?:\(([^\r\n]*)\))?\s*$/us',trim($raw),$match)) {
                $external=$match[2]; $variant=preg_replace('/\s+/u',' ',trim($match[3] ?? ''));
            }
            $base=hash('sha256',($external!==''?$external:"invalid\0".$raw)."\0".$variant);
            $occurrences[$order][$base]=($occurrences[$order][$base] ?? 0)+1;
            // Osobny wpis dla każdego wiersza CSV, także powtórzonego produktu.
            $key=hash('sha256',$base."\0".$occurrences[$order][$base]);
            $number=preg_match('/^[0-9]{1,9}$/D',$quantity) && (int)$quantity>0 ? (int)$quantity : null;
            $result[$order][$key]=['external_user_id'=>$user,'source'=>$raw,'external_product_id'=>$external,'variant'=>$variant,'quantity'=>$number,'quantity_raw'=>$quantity,'source_rows'=>1];
            if (count($result)>5000) { throw new RuntimeException('Limit: 5000 zamówień na plik.'); }
        }
        if (!$result) { throw new RuntimeException('Brak elementów dla wybranej zbiórki.'); }
        ksort($result,SORT_STRING); foreach ($result as &$lines) { ksort($lines,SORT_STRING); } unset($lines);
        return ['items'=>$result,'rows'=>$rows,'skipped'=>$skipped];
    } finally { fclose($handle); }
}
