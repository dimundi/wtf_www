<?php
if (!defined('ABSPATH')) { exit; }

function wtfg_query_ok($result) {
    global $wpdb;
    if ($result===false || $wpdb->last_error) { throw new RuntimeException('Nie można zapisać danych Gamefound. Operacja została przerwana.'); }
    return $result;
}

function wtfg_lock() {
    global $wpdb;
    $name='wtfg_write_'.substr(hash('sha256',$wpdb->prefix),0,30);
    if ((string)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s,0)',$name))!=='1') { throw new RuntimeException('Trwa zapis danych Gamefound. Spróbuj ponownie.'); }
    return $name;
}

function wtfg_state($campaign) {
    global $wpdb;
    $state=['customers'=>[],'products'=>[],'orders'=>[],'items'=>[]];
    foreach (['customers','products','orders','order_items'] as $table) {
        $rows=$wpdb->get_results($wpdb->prepare('SELECT * FROM '.wtfg_table($table).' WHERE campaign_id=%d ORDER BY id',$campaign),ARRAY_A);
        wtfg_query_ok($rows);
        foreach ($rows as $row) {
            $id=(int)$row['id']; unset($row['id'],$row['campaign_id'],$row['updated_at']);
            if ($table==='customers') { $state['customers'][$row['external_id']]=json_decode($row['data'],true,512,JSON_THROW_ON_ERROR); }
            elseif ($table==='products') { $state['products'][$row['external_id']]=['id'=>$id,'name'=>$row['name'],'variants'=>[],'variant_ids'=>[]]; }
            elseif ($table==='orders') { $external=$row['external_id']; unset($row['external_id']); $state['orders'][$external]=['id'=>$id]+$row; }
            else {
                $order=$row['external_order_id']; $key=$row['line_key']; unset($row['external_order_id'],$row['line_key']);
                foreach (['quantity','variant_id','order_id'] as $field) { $row[$field]=$row[$field]===null?null:(int)$row[$field]; }
                $state['items'][$order][$key]=$row;
            }
        }
    }
    $variants=$wpdb->get_results($wpdb->prepare('SELECT v.id,v.name,p.external_id FROM '.wtfg_table('variants').' v JOIN '.wtfg_table('products').' p ON p.id=v.product_id AND p.campaign_id=v.campaign_id WHERE v.campaign_id=%d ORDER BY v.id',$campaign),ARRAY_A);
    wtfg_query_ok($variants);
    foreach ($variants as $variant) {
        $product=&$state['products'][$variant['external_id']];
        $product['variants'][]=$variant['name']; $product['variant_ids'][$variant['name']]=(int)$variant['id']; unset($product);
    }
    foreach ($state['products'] as &$product) { sort($product['variants'],SORT_STRING); ksort($product['variant_ids'],SORT_STRING); } unset($product);
    foreach ($state['items'] as &$lines) { ksort($lines,SORT_STRING); } unset($lines);
    foreach ($state as &$entries) { ksort($entries,SORT_STRING); } unset($entries);
    return $state;
}

function wtfg_state_hash($campaign,$state) {
    return hash_hmac('sha256',wp_json_encode([wtfg_project_id($campaign),$state]),wp_salt('auth'));
}

function wtfg_decimal($raw) {
    if ($raw==='') { return null; }
    $raw=trim((string)$raw);
    if (!preg_match('/^([0-9]{1,16})(?:\.([0-9]{1,4}))?$/D',$raw,$m)) { throw new RuntimeException('Nieprawidłowa kwota. Wymagana liczba z kropką dziesiętną.'); }
    return (ltrim($m[1],'0') ?: '0').'.'.str_pad($m[2] ?? '',4,'0');
}

function wtfg_order_record($source,$old=[]) {
    $map=['OrderPublicID'=>'public_id','UserID'=>'user_external_id','OrderStage'=>'stage','OrderPaymentStatus'=>'payment_status','CurrencyShortName'=>'currency','ProductsOrdered'=>'description','Order value with shipping (transaction currency)'=>'total_amount','TotalPaid'=>'total_paid'];
    $result=$old; unset($result['id']);
    foreach ($map as $field=>$column) {
        if (!array_key_exists($field,$source)) { continue; }
        // Puste komórki eksportu częściowego nie kasują istniejących danych.
        if ($source[$field]==='' && isset($old[$column]) && $old[$column]!=='') { continue; }
        $result[$column]=in_array($column,['total_amount','total_paid'],true)?wtfg_decimal($source[$field]):(string)$source[$field];
    }
    foreach (['public_id','user_external_id','stage','payment_status','currency','description'] as $key) { $result[$key]=$result[$key] ?? ''; }
    foreach (['total_amount','total_paid'] as $key) { $result[$key]=$result[$key] ?? null; }
    foreach (['public_id'=>128,'stage'=>64,'payment_status'=>64,'currency'=>8] as $key=>$length) { if (mb_strlen($result[$key])>$length) { throw new RuntimeException('Zbyt długa wartość pola zamówienia: '.$key.'.'); } }
    ksort($result); return $result;
}

function wtfg_resolve_item($line,$order,$state) {
    unset($line['source_rows']);
    $parent=$state['orders'][$order] ?? null; $product=$state['products'][$line['external_product_id']] ?? null;
    $line['order_id']=$parent['id'] ?? null;
    $line['variant_id']=$product['variant_ids'][$line['variant']] ?? null;
    if ($line['quantity']===null) { $status='invalid_quantity'; }
    elseif ($line['external_product_id']==='') { $status='parse_error'; }
    elseif (!$parent) { $status='missing_order'; }
    elseif ($line['external_user_id']==='') { $status='missing_user'; }
    elseif ($parent['user_external_id']!=='' && $parent['user_external_id']!==$line['external_user_id']) { $status='user_mismatch'; $line['order_id']=null; }
    elseif (!$product) { $status='missing_product'; }
    elseif ($line['variant_id']===null) { $status='missing_variant'; }
    else { $status='matched'; }
    $line['status']=$status; ksort($line); return $line;
}

function wtfg_same($a,$b) {
    if (is_array($a) && is_array($b)) {
        if (count($a)!==count($b)) { return false; }
        foreach ($a as $key=>$value) { if (!array_key_exists($key,$b) || !wtfg_same($value,$b[$key])) { return false; } }
        return true;
    }
    return $a===$b;
}

function wtfg_fields_changed($before,$after) {
    $fields=[];
    foreach (array_unique(array_merge(array_keys($before ?? []),array_keys($after))) as $key) {
        if (!array_key_exists($key,$before ?? []) || !array_key_exists($key,$after) || !wtfg_same($before[$key],$after[$key])) { $fields[]=(string)$key; }
    }
    return $fields;
}

function wtfg_write($campaign,$entity,$external,$data) {
    global $wpdb;
    if ($entity==='items') {
        $existing=$wpdb->get_results($wpdb->prepare('SELECT id,line_key FROM '.wtfg_table('order_items').' WHERE campaign_id=%d AND external_order_id=%s',$campaign,(string)$external),ARRAY_A);
        wtfg_query_ok($existing); $existing=array_column($existing,'id','line_key');
        foreach ($data as $key=>$line) {
            $values=$line+['campaign_id'=>$campaign,'external_order_id'=>(string)$external,'line_key'=>$key];
            wtfg_query_ok(isset($existing[$key])?$wpdb->update(wtfg_table('order_items'),$values,['id'=>$existing[$key]]):$wpdb->insert(wtfg_table('order_items'),$values));
        }
        foreach ($existing as $key=>$id) { if (!isset($data[$key])) { wtfg_query_ok($wpdb->delete(wtfg_table('order_items'),['id'=>$id])); } }
        return;
    }
    $table=wtfg_table($entity);
    $id=$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE campaign_id=%d AND external_id=%s",$campaign,(string)$external));
    wtfg_query_ok($id);
    if ($entity==='customers') {
        $business=strtolower(trim($data['CustomerIsBusinessEntity'] ?? ''));
        $values=['full_name'=>$data['CustomerFullName'] ?? '', 'is_business'=>in_array($business,['true','1'],true)?1:(in_array($business,['false','0'],true)?0:null),'data'=>wp_json_encode($data)];
    } elseif ($entity==='products') { $values=['name'=>$data['name']]; }
    else { $values=$data; unset($values['id']); }
    $values['updated_at']=gmdate('Y-m-d H:i:s');
    wtfg_query_ok($id?$wpdb->update($table,$values,['id'=>$id]):$wpdb->insert($table,$values+['campaign_id'=>$campaign,'external_id'=>(string)$external]));
    if (!$id) { $id=$wpdb->insert_id; }
    if ($entity==='products') {
        foreach ($data['variants'] as $variant) {
            $hash=hash('sha256',$variant);
            $exists=$wpdb->get_row($wpdb->prepare('SELECT id,name FROM '.wtfg_table('variants').' WHERE product_id=%d AND name_hash=%s',$id,$hash),ARRAY_A);
            wtfg_query_ok($exists);
            if ($exists && $exists['name']!==$variant) { throw new RuntimeException('Konflikt identyfikatora wariantu.'); }
            if (!$exists) { wtfg_query_ok($wpdb->insert(wtfg_table('variants'),['campaign_id'=>$campaign,'product_id'=>$id,'name'=>$variant,'name_hash'=>$hash])); }
        }
    }
}

function wtfg_audit($campaign,$run,$entity,$external,$operation,$fields) {
    global $wpdb;
    wtfg_query_ok($wpdb->insert(wtfg_table('changes'),['campaign_id'=>$campaign,'import_id'=>$run,'user_id'=>get_current_user_id(),'created_at'=>gmdate('Y-m-d H:i:s'),'entity'=>$entity,'external_id'=>(string)$external,'operation'=>$operation,'fields'=>wp_json_encode(array_values($fields))]));
}

function wtfg_item_labels() {
    return ['matched'=>'Powiązany','missing_order'=>'Brak zamówienia — wczytaj Orders','missing_user'=>'Brak poprawnego UserID','user_mismatch'=>'UserID nie zgadza się z zamówieniem','missing_product'=>'Brak produktu','missing_variant'=>'Brak wariantu','parse_error'=>'Nierozpoznany ProductName','invalid_quantity'=>'Nieprawidłowa ilość'];
}
