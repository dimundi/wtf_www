<?php
if(!defined('WP_CLI')||!WP_CLI) exit;
$source=json_decode(file_get_contents(wp_upload_dir()['basedir'].'/media/shop-import/products.json'),true,512,JSON_THROW_ON_ERROR);
$map=get_option('wtf_shop_import_map'); $errors=[];
foreach($source['products'] as $data){
    $p=wc_get_product($map['products'][$data['id']]??0);
    if(!$p){$errors[]='Brak '.$data['id'];continue;}
    foreach(['name','slug','regular_price','sale_price','stock_status','catalog_visibility','manage_stock','stock_quantity','virtual','downloadable','weight','length','width','height'] as $key){
        if($p->{'get_'.$key}()!=$data[$key]) $errors[]=$data['id'].' '.$key;
    }
    if(wp_parse_url($p->get_permalink(),PHP_URL_PATH)!==$data['source_path']) $errors[]='Adres '.$data['id'].': '.$p->get_permalink();
    if(!wp_attachment_is_image($p->get_image_id())) $errors[]='Grafika '.$data['id'];
    if(count($p->get_downloads())!==count($data['downloads'])) $errors[]='Pliki '.$data['id'];
    if(str_contains($p->get_description(),'localhost:8082')) $errors[]='Stary adres w opisie '.$data['id'];
}
if($errors) WP_CLI::error(implode("\n",$errors));
WP_CLI::success(count($source['products']).' produktów: ceny, dostępność, ścieżki, grafiki i definicje pobrań zgodne.');
