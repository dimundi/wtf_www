<?php
if (!defined('ABSPATH')) { exit; }

function wtfg_upload_path($name) {
    $file=$_FILES[$name] ?? [];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) { return null; }
    if (($file['error'] ?? null)!==UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) { throw new RuntimeException('Przesyłanie pliku nie powiodło się.'); }
    if (strtolower(pathinfo($file['name'],PATHINFO_EXTENSION))!=='csv' || filesize($file['tmp_name'])>8*1024*1024) { throw new RuntimeException('Wybierz CSV do 8 MB.'); }
    return $file['tmp_name'];
}

function wtfg_parse_packet($orders_file,$items_file,$campaign,$mode='all') {
    $project=wtfg_project_id($campaign);
    $packet=['project'=>$project,'customers'=>[],'products'=>[],'orders'=>[],'items'=>[]]; $warnings=[]; $hashes=[];
    if ($orders_file) {
        $hashes[]=hash_file('sha256',$orders_file);
        $parsed=wtfg_parse_orders_csv($orders_file,$project);
        $packet['project']=$parsed['project']; $packet['orders']=$parsed['orders'];
        $warnings['Orders']=['skipped'=>$parsed['skipped'],'conflicts'=>$parsed['conflicts']];
        [$handle,$headers]=wtfg_open_csv($orders_file); fclose($handle);
        if (in_array('CustomerFullName',$headers,true) && in_array('CustomerEmail',$headers,true)) {
            $people=wtfg_parse_supporters_csv($orders_file,$packet['project']);
            $packet['customers']=$people['people'];
            $warnings['Klienci']=['skipped'=>$people['skipped'],'conflicts'=>$people['conflicts']];
        }
    }
    if ($items_file) {
        $hashes[]=hash_file('sha256',$items_file);
        if ($mode==='customers') {
            $people=wtfg_parse_supporters_csv($items_file,$packet['project']);
            $packet['project']=$people['project']; $packet['customers']=$people['people'];
            $warnings['Klienci']=['skipped'=>$people['skipped'],'conflicts'=>$people['conflicts']];
        } else {
            if ($packet['project']==='') { throw new RuntimeException('Najpierw wpisz ProjectID w konfiguracji zbiórki lub dołącz Orders.'); }
            [$packet['products'],$rows,$skipped]=wtfg_parse_products_csv($items_file,$packet['project']);
            $warnings['Produkty']=['skipped'=>$skipped];
            if ($mode!=='products') {
                $items=wtfg_parse_items_csv($items_file,$packet['project']); $packet['items']=$items['items'];
                $warnings['Order items']=['skipped'=>$items['skipped']];
            }
        }
    }
    if (!$orders_file && !$items_file) { throw new RuntimeException('Wybierz przynajmniej jeden plik CSV.'); }
    wtfg_check_project_binding($campaign,$packet['project']);
    return [$packet,$warnings,hash('sha256',implode(':',$hashes))];
}

function wtfg_candidates($state,$packet) {
    $candidates=[];
    foreach (['customers','products','orders'] as $entity) {
        foreach ($packet[$entity] as $external=>$source) {
            $before=$state[$entity][$external] ?? null;
            if ($entity==='customers') { $after=wtfg_merge_supporter($before ?? [],$source); }
            elseif ($entity==='products') {
                $before=$before?['name'=>$before['name'],'variants'=>$before['variants']]:null;
                $after=['name'=>$source['name'],'variants'=>array_values(array_unique(array_merge($before['variants'] ?? [],$source['variants'])))];
                sort($after['variants'],SORT_STRING);
            } else { $after=wtfg_order_record($source,$before ?? []); if ($before) { unset($before['id']); } }
            if ($before!==null && wtfg_same($before,$after)) { continue; }
            $candidates[]=['entity'=>$entity,'key'=>(string)$external,'before'=>$before,'after'=>$after,'fields'=>wtfg_fields_changed($before,$after)];
        }
    }
    // Cała zmiana zawartości istniejącego zamówienia jest jedną jawną propozycją.
    // Pozwala to wykryć także zmianę produktu/wariantu, która zmienia klucz wiersza.
    $item_groups=$packet['items'];
    foreach ($state['items'] as $external=>$lines) { if (!isset($item_groups[$external])) { $item_groups[$external]=$lines; } }
    foreach ($item_groups as $external=>$lines) {
        $after=[];
        foreach ($lines as $key=>$line) { $after[$key]=wtfg_resolve_item($line,$external,$state); }
        ksort($after,SORT_STRING); $before=$state['items'][$external] ?? null;
        if ($before!==null && wtfg_same($before,$after)) { continue; }
        $candidates[]=['entity'=>'items','key'=>(string)$external,'before'=>$before,'after'=>$after,'fields'=>['elementy','ilości','powiązania']];
    }
    return $candidates;
}

function wtfg_pending($campaign,$packet) {
    return array_values(array_filter(wtfg_candidates(wtfg_state($campaign),$packet),fn($entry)=>$entry['before']!==null));
}

function wtfg_auto_new($campaign,$run,$packet) {
    $counts=['customers'=>0,'products'=>0,'orders'=>0,'items'=>0];
    // Kolejność zapewnia powiązania nowych pozycji dopiero z zapisanymi rekordami.
    foreach (['customers','products','orders','items'] as $entity) {
        $state=wtfg_state($campaign);
        foreach (wtfg_candidates($state,$packet) as $entry) {
            if ($entry['entity']!==$entity || $entry['before']!==null) { continue; }
            wtfg_write($campaign,$entity,$entry['key'],$entry['after']);
            wtfg_audit($campaign,$run,$entity,$entry['key'],'auto_new',$entry['fields']);
            $counts[$entity]+=$entity==='items'?count($entry['after']):1;
        }
    }
    return $counts;
}

function wtfg_save_preview($campaign,$run,$packet,$report) {
    global $wpdb;
    $pending=wtfg_pending($campaign,$packet);
    $report['pending']=count($pending);
    $values=['status'=>$pending?'preview':'applied','finished_at'=>$pending?null:gmdate('Y-m-d H:i:s'),'snapshot_hash'=>wtfg_state_hash($campaign,wtfg_state($campaign)),
        'payload'=>$pending?wtfg_supporter_encrypt(['packet'=>$packet,'pending'=>$pending]):'','report'=>wp_json_encode($report)];
    wtfg_query_ok($wpdb->update(wtfg_table('imports'),$values,['id'=>$run]));
}

add_action('admin_post_wtfg_import',function () {
    wtfg_require_admin(); $campaign=absint($_POST['campaign_id'] ?? 0);
    check_admin_referer('wtfg_import_'.$campaign);
    if (!wtfg_valid_campaign($campaign)) { wp_die('Nieprawidłowa zbiórka.'); }
    global $wpdb; $lock=''; $run=0; $message='';
    try {
        $mode=in_array($_POST['mode'] ?? '',['customers','products'],true)?$_POST['mode']:'all';
        $orders=wtfg_upload_path('orders_csv'); $items=wtfg_upload_path('items_csv');
        if ($items && $mode==='all' && ($_POST['complete_orders'] ?? '')!=='1') { throw new RuntimeException('Potwierdź komplet elementów zamówień w pliku Order items.'); }
        $lock=wtfg_lock();
        [$packet,$warnings,$hash]=wtfg_parse_packet($orders,$items,$campaign,$mode);
        // Sprawdzenie całego planu przed pierwszym zapisem, także formatu kwot.
        wtfg_candidates(wtfg_state($campaign),$packet);
        wtfg_query_ok($wpdb->query('START TRANSACTION'));
        $report=['warnings'=>$warnings,'confirmed'=>0];
        wtfg_query_ok($wpdb->insert(wtfg_table('imports'),['campaign_id'=>$campaign,'user_id'=>get_current_user_id(),'created_at'=>gmdate('Y-m-d H:i:s'),'status'=>'processing','file_hash'=>$hash,'snapshot_hash'=>'','payload'=>'','report'=>'{}']));
        $run=(int)$wpdb->insert_id;
        if (wtfg_project_id($campaign)==='' && $packet['project']!=='') {
            update_post_meta($campaign,'_wtfg_project_id',$packet['project']);
            if ($wpdb->last_error || wtfg_project_id($campaign)!==$packet['project']) { throw new RuntimeException('Nie można zapisać ProjectID.'); }
        }
        $report['automatic']=wtfg_auto_new($campaign,$run,$packet);
        wtfg_save_preview($campaign,$run,$packet,$report);
        wtfg_query_ok($wpdb->query('COMMIT'));
    } catch (Throwable $error) {
        $wpdb->query('ROLLBACK'); clean_post_cache($campaign);
        $message=$error instanceof RuntimeException?$error->getMessage():'Nie można wykonać importu. Nie zapisano danych.';
        wtfg_query_ok($wpdb->insert(wtfg_table('imports'),['campaign_id'=>$campaign,'user_id'=>get_current_user_id(),'created_at'=>gmdate('Y-m-d H:i:s'),'finished_at'=>gmdate('Y-m-d H:i:s'),'status'=>'failed','file_hash'=>'','snapshot_hash'=>'','payload'=>'','report'=>wp_json_encode(['error'=>$message])]));
        $run=(int)$wpdb->insert_id;
    } finally { if ($lock) { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock)); } }
    wp_safe_redirect(add_query_arg(['page'=>'wtfg-orders','view'=>'import','import_id'=>$run],admin_url('admin.php'))); exit;
});

add_action('admin_post_wtfg_confirm',function () {
    wtfg_require_admin(); $campaign=absint($_POST['campaign_id'] ?? 0); $run=absint($_POST['import_id'] ?? 0);
    check_admin_referer('wtfg_confirm_'.$run);
    if (!wtfg_valid_campaign($campaign)) { wp_die('Nieprawidłowa zbiórka.'); }
    global $wpdb; $lock=''; $message='';
    try {
        $lock=wtfg_lock(); wtfg_query_ok($wpdb->query('START TRANSACTION'));
        $row=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.wtfg_table('imports').' WHERE id=%d AND campaign_id=%d AND user_id=%d FOR UPDATE',$run,$campaign,get_current_user_id()),ARRAY_A);
        wtfg_query_ok($row);
        if (!$row || $row['status']!=='preview' || strtotime($row['created_at'].' UTC')<time()-DAY_IN_SECONDS) { throw new RuntimeException('Podgląd wygasł lub został zastosowany. Wczytaj plik ponownie.'); }
        if (!hash_equals($row['snapshot_hash'],wtfg_state_hash($campaign,wtfg_state($campaign)))) { throw new RuntimeException('Dane zmieniły się od podglądu. Wczytaj plik ponownie — potwierdzenie nie nadpisze nowszych danych.'); }
        $payload=wtfg_supporter_decrypt($row['payload']); $selected=$_POST['approve'] ?? [];
        if (!is_array($selected) || !$selected) { throw new RuntimeException('Zaznacz rekordy, których wszystkie pokazane zmiany zatwierdzasz.'); }
        $selected=array_map('strval',$selected); $report=json_decode($row['report'],true,512,JSON_THROW_ON_ERROR);
        $applied=0; $item_state=null;
        foreach ($payload['pending'] as $index=>$entry) {
            if (!in_array((string)$index,$selected,true)) { continue; }
            if ($entry['entity']==='items') {
                if ($item_state===null) { $item_state=wtfg_state($campaign); }
                $resolved=[];
                foreach ($entry['after'] as $key=>$line) { $resolved[$key]=wtfg_resolve_item($line,$entry['key'],$item_state); }
                // Zatwierdzony wcześniej klient/produkt/zamówienie może zmienić propozycję.
                // Taką zmianę pokazujemy ponownie, zamiast zatwierdzać ją niejawnie.
                if (!wtfg_same($resolved,$entry['after'])) { continue; }
            }
            wtfg_write($campaign,$entry['entity'],$entry['key'],$entry['after']);
            wtfg_audit($campaign,$run,$entry['entity'],$entry['key'],'confirmed',$entry['fields']); $applied++;
        }
        if (!$applied) { throw new RuntimeException('Nie zastosowano zmian. Odśwież podgląd zależnych powiązań.'); }
        $report['confirmed']+=$applied;
        wtfg_save_preview($campaign,$run,$payload['packet'],$report);
        wtfg_query_ok($wpdb->query('COMMIT'));
    } catch (Throwable $error) {
        $wpdb->query('ROLLBACK'); $message=$error instanceof RuntimeException?$error->getMessage():'Nie można zatwierdzić zmian.';
        $owned=$wpdb->get_row($wpdb->prepare('SELECT report FROM '.wtfg_table('imports').' WHERE id=%d AND campaign_id=%d AND user_id=%d',$run,$campaign,get_current_user_id()),ARRAY_A);
        if ($owned) {
            $report=json_decode($owned['report'],true) ?: [];
            $errors=$report['apply_errors'] ?? [];
            $errors[]=['time'=>gmdate('Y-m-d H:i:s'),'user_id'=>get_current_user_id(),'message'=>$message];
            $report['apply_errors']=array_slice($errors,-20);
            $wpdb->update(wtfg_table('imports'),['report'=>wp_json_encode($report)],['id'=>$run]);
        }
    } finally { if ($lock) { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock)); } }
    if ($message) { wp_die(esc_html($message),'',['response'=>409,'back_link'=>true]); }
    wp_safe_redirect(add_query_arg(['page'=>'wtfg-orders','view'=>'import','import_id'=>$run],admin_url('admin.php'))); exit;
});
