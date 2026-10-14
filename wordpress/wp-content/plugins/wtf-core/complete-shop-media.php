<?php
// Jednorazowe uzupełnienie mediów i odnośników początkowego importu.
if(!defined('WP_CLI')||!WP_CLI)exit;
$dir=wp_upload_dir()['basedir'].'/media/shop-import';
$source=json_decode(file_get_contents($dir.'/products.json'),true,512,JSON_THROW_ON_ERROR);
$map=get_option('wtf_shop_import_map');
require_once ABSPATH.'wp-admin/includes/image.php';
foreach($source['inline_files'] as $url=>$file){
    $used=false;
    foreach($map['products'] as $id) if(str_contains(get_post_field('post_content',$id).get_post_field('post_excerpt',$id),$url)){$used=true;break;}
    if(!$used||isset($map['files'][$url]))continue;
    $path=$dir.'/'.$file['key'];
    if(!is_file($path))WP_CLI::error('Brak '.$path);
    $id=wp_insert_attachment(['post_title'=>pathinfo($path,PATHINFO_FILENAME),'post_status'=>'inherit','post_mime_type'=>wp_check_filetype($path)['type']],$path,0,true);
    if(is_wp_error($id))WP_CLI::error($id->get_error_message());
    if(wp_attachment_is_image($id))wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$path));
    wp_set_object_terms($id,'Sklep','wtf_media_group',true);
    $map['files'][$url]=$id;update_option('wtf_shop_import_map',$map,false);
}
foreach($map['products'] as $old=>$id){
    $p=wc_get_product($id); if(!$p||$p->get_meta('_wtf_shop_media_completed'))continue;
    foreach(['description','short_description'] as $field){
        $html=$p->{'get_'.$field}();
        foreach($map['files']??[] as $url=>$new) $html=str_replace($url,wp_get_attachment_url($new),$html);
        $html=preg_replace_callback('~https?://localhost:8082/[^\s"<>\']+~',function($m){
            $url=str_replace('http://localhost:8082','https://whatthefrog.pl',$m[0]);
            $path=wp_parse_url($url,PHP_URL_PATH);
            $local=home_url($path);
            return url_to_postid($local)?$local:wtf_shop_local_url($url);
        },$html);
        $p->{'set_'.$field}($html);
    }
    $p->update_meta_data('_wtf_shop_media_completed',1);$p->save();
}
file_put_contents($dir.'/id-map.json',wp_json_encode($map,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
WP_CLI::success('Uzupełniono media opisów i odnośniki.');
