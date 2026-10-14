<?php
// Ręcznie: wp eval-file wp-content/plugins/wtf-core/import-shop.php --allow-root
if (!defined('WP_CLI') || !WP_CLI) exit;
if (!class_exists('WooCommerce')) WP_CLI::error('Aktywuj WooCommerce.');
$dir=wp_upload_dir()['basedir'].'/media/shop-import';
$source=json_decode(file_get_contents($dir.'/products.json'),true,512,JSON_THROW_ON_ERROR);
$map=get_option('wtf_shop_import_map',['products'=>[],'media'=>[],'terms'=>[],'paths'=>[]]);
require_once ABSPATH.'wp-admin/includes/image.php';
foreach($source['settings'] as $key=>$value) update_option($key,$value);
update_option('woocommerce_hide_out_of_stock_items','yes');
update_option('woocommerce_download_method','force');
foreach($source['terms'] as $tax=>$terms) {
    foreach($terms as $term) {
        $existing=term_exists($term['slug'],$tax);
        if(!$existing) $existing=wp_insert_term($term['name'],$tax,['slug'=>$term['slug'],'description'=>$term['description']]);
        if(is_wp_error($existing)) WP_CLI::error($existing->get_error_message());
        $map['terms'][$tax][$term['term_id']]=(int)$existing['term_id'];
    }
    foreach($terms as $term) if($term['parent']) wp_update_term($map['terms'][$tax][$term['term_id']],$tax,['parent'=>$map['terms'][$tax][$term['parent']]]);
}
foreach($source['media'] as $old=>$image) {
    if(!empty($map['media'][$old]) && get_post($map['media'][$old])) continue;
    $file=$dir.'/'.$old.'-'.basename($image['file']);
    if(!is_file($file)) WP_CLI::error('Brak grafiki: '.$file);
    $id=wp_insert_attachment(['post_title'=>$image['title'],'post_status'=>'inherit','post_mime_type'=>wp_check_filetype($file)['type']],$file,0,true);
    if(is_wp_error($id)) WP_CLI::error($id->get_error_message());
    update_post_meta($id,'_wp_attachment_image_alt',$image['alt']);
    update_post_meta($id,'_wtf_source_attachment_id',$old);
    $map['media'][$old]=$id; update_option('wtf_shop_import_map',$map,false);
    wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$file));
    if(taxonomy_exists('wtf_media_group')) wp_set_object_terms($id,'Sklep','wtf_media_group',true);
}
$rewrite=function($html) use($source,$map){
    foreach($source['media'] as $old=>$image) {
        $path=wp_parse_url($image['url'],PHP_URL_PATH);
        $stem=preg_quote(preg_replace('/\.[^.]+$/','',$path),'~');
        $html=preg_replace('~https?://(?:localhost:8082|(?:www\.)?whatthefrog\.pl)'.$stem.'(?:-\d+x\d+)?\.[a-zA-Z0-9]+~',wp_get_attachment_url($map['media'][$old]),$html);
    }
    foreach($source['inline_files']??[] as $url=>$file) if(isset($map['files'][$url])) $html=str_replace($url,wp_get_attachment_url($map['files'][$url]),$html);
    return $html;
};
$created=0; $skipped=0; $new=[];
foreach($source['products'] as $data) {
    $old=$data['id'];
    if(!empty($map['products'][$old]) && wc_get_product($map['products'][$old])) {$skipped++;continue;}
    $class=['simple'=>'WC_Product_Simple','variable'=>'WC_Product_Variable','variation'=>'WC_Product_Variation'][$data['type']]??null;
    if(!$class) WP_CLI::error('Nieobsługiwany typ: '.$data['type']);
    $p=new $class(); $props=$data;
    foreach(['id','type','source_path','meta_data','date_created','date_modified','rating_counts','average_rating','review_count','cogs_value','brand_ids'] as $key) unset($props[$key]);
    foreach(['date_on_sale_from','date_on_sale_to'] as $key) $props[$key]=empty($data[$key])?null:$data[$key]['date'];
    $props['description']=$rewrite($props['description']); $props['short_description']=$rewrite($props['short_description']);
    $props['parent_id']=$map['products'][$data['parent_id']]??0;
    $props['image_id']=$map['media'][$data['image_id']]??0;
    $props['gallery_image_ids']=array_values(array_filter(array_map(fn($id)=>$map['media'][$id]??0,$data['gallery_image_ids'])));
    foreach(['category_ids'=>'product_cat','tag_ids'=>'product_tag'] as $key=>$tax) $props[$key]=array_values(array_filter(array_map(fn($id)=>$map['terms'][$tax][$id]??0,$data[$key]??[])));
    $props['shipping_class_id']=$map['terms']['product_shipping_class'][$data['shipping_class_id']]??0;
    $props['upsell_ids']=[]; $props['cross_sell_ids']=[];
    if($data['type']!=='variation') {
        $attrs=[];
        foreach($data['attributes'] as $attr) {
            if($attr['id']) WP_CLI::error('Wymagane mapowanie atrybutu globalnego: '.$attr['name']);
            $a=new WC_Product_Attribute(); $a->set_name($attr['name']); $a->set_options($attr['options']); $a->set_position($attr['position']); $a->set_visible($attr['visible']); $a->set_variation($attr['variation']); $attrs[]=$a;
        }
        $props['attributes']=$attrs;
    }
    $downloads=[];
    foreach($data['downloads'] as $key=>$download) {
        $download['file']=preg_replace('~^https?://(?:localhost:8082|(?:www\.)?whatthefrog\.pl)~',home_url(),$download['file']);
        $path=wp_parse_url($download['file'],PHP_URL_PATH);
        if(!is_file(ABSPATH.ltrim($path,'/'))) WP_CLI::error('Brak pliku cyfrowego '.$path);
        $d=new WC_Product_Download(); $d->set_id($download['id']); $d->set_name($download['name']); $d->set_file($download['file']); $downloads[$key]=$d;
    }
    $props['downloads']=$downloads;
    $errors=$p->set_props($props); if(is_wp_error($errors)) WP_CLI::error($errors->get_error_message());
    $p->update_meta_data('_wtf_source_product_id',$old); $p->update_meta_data('_wtf_source_product_path',$data['source_path']);
    foreach($data['meta_data'] as $meta) if(str_starts_with($meta['key'],'_iwo_')) $p->update_meta_data($meta['key'],$meta['value']);
    $id=$p->save();
    $map['products'][$old]=$id; $new[]=$old;
    if($data['type']!=='variation') $map['paths'][$data['source_path']]=$id;
    update_option('wtf_shop_import_map',$map,false);
    if($p->get_slug()!==$data['slug']) WP_CLI::error('Kolizja slugu produktu '.$old);
    $created++; WP_CLI::log($old.' -> '.$id.' '.$p->get_name());
}
foreach($source['products'] as $data) {
    $p=wc_get_product($map['products'][$data['id']]);
    if(in_array($data['id'],$new,true)) {
        foreach(['upsell_ids','cross_sell_ids'] as $key) $p->{'set_'.$key}(array_values(array_filter(array_map(fn($id)=>$map['products'][$id]??0,$data[$key]))));
        $p->save();
    }
    if($data['type']==='variable') WC_Product_Variable::sync($p->get_id());
}
file_put_contents($dir.'/id-map.json',wp_json_encode($map,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
WP_CLI::success('Utworzono '.$created.', zachowano istniejących '.$skipped.'.');
