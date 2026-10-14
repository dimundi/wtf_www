<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
wp_set_current_user(1);
$source=$args[0]??'';
if (!$source || !is_file($source)) { WP_CLI::error('Podaj eksport JSON.'); }
$data=json_decode(file_get_contents($source),true,512,JSON_THROW_ON_ERROR);
if (($data['version']??0)!==1) { WP_CLI::error('Nieobsługiwany eksport.'); }
require_once ABSPATH.'wp-admin/includes/image.php';
$terms=[];
foreach (['wtf_faq_group','wtf_faq_game'] as $taxonomy) {
    foreach ($data['terms'][$taxonomy]??[] as $term) {
        $existing=term_exists($term['slug'],$taxonomy);
        $result=$existing?:wp_insert_term($term['name'],$taxonomy,['slug'=>$term['slug'],'description'=>$term['description']]);
        if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
        $terms[$taxonomy][$term['slug']]=(int)$result['term_id'];
    }
}
$media=[];
foreach ($data['media'] as $asset) {
    $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wtf_faq_transfer_asset','meta_value'=>$asset['key'],'suppress_filters'=>true]);
    if ($existing) { $id=$existing[0]->ID; }
    else {
        $path=wp_upload_dir()['basedir'].'/'.$asset['destination'];
        if (!is_file($path)) { WP_CLI::error('Brak przeniesionego obrazu: '.$asset['destination']); }
        $id=wp_insert_attachment(['post_title'=>$asset['title'],'post_mime_type'=>wp_check_filetype($path)['type'],'post_status'=>'inherit'],$path,0,true);
        if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
        update_attached_file($id,$path);wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$path));
        update_post_meta($id,'_wp_attachment_image_alt',$asset['alt']);update_post_meta($id,'_wtf_faq_transfer_asset',$asset['key']);
    }
    $media[$asset['id']]=['id'=>$id,'url'=>wp_get_attachment_url($id),'oldUrl'=>$asset['url']];
}
$imported=0;
foreach ($data['questions'] as $entry) {
    $existing=get_posts(['post_type'=>'wtf_faq','post_status'=>['publish','draft','pending','private','future'],'numberposts'=>1,'meta_key'=>'_wtf_faq_transfer_key','meta_value'=>$entry['key'],'suppress_filters'=>true]);
    if ($existing) { continue; }
    if ($entry['source']) {
        $existing=get_posts(['post_type'=>'wtf_faq','post_status'=>['publish','draft','pending','private','future'],'numberposts'=>1,'meta_key'=>'_wtf_faq_source_id','meta_value'=>$entry['source'],'suppress_filters'=>true]);
        if ($existing) { update_post_meta($existing[0]->ID,'_wtf_faq_transfer_key',$entry['key']);continue; }
    }
    $content=$entry['content'];
    foreach ($media as $oldId=>$asset) {
        $content=str_replace($asset['oldUrl'],$asset['url'],$content);
    }
    $content=preg_replace_callback('/\bwp-image-(\d+)\b/',static function ($match) use ($media) {
        return isset($media[(int)$match[1]])?'wp-image-'.$media[(int)$match[1]]['id']:$match[0];
    },$content);
    $content=preg_replace_callback('~<img\b[^>]*>~i',static function ($match) {
        return preg_replace('~\s+(?:srcset|sizes)=(?:"[^"]*"|\x27[^\x27]*\x27)~i','',$match[0]);
    },$content);
    $id=wp_insert_post(wp_slash(['post_type'=>'wtf_faq','post_status'=>$entry['status'],'post_title'=>$entry['title'],'post_content'=>wp_kses_post($content),'menu_order'=>$entry['order']]),true);
    if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
    foreach (['wtf_faq_group','wtf_faq_game'] as $taxonomy) {
        $slug=$entry['terms'][$taxonomy][0]??'';
        if ($slug && isset($terms[$taxonomy][$slug])) { wp_set_object_terms($id,[$terms[$taxonomy][$slug]],$taxonomy); }
    }
    update_post_meta($id,'_wtf_faq_source_id',$entry['source']);update_post_meta($id,'_wtf_faq_transfer_key',$entry['key']);
    update_post_meta($id,'_wtf_faq_import_up',max(0,(int)$entry['votes']['up']));update_post_meta($id,'_wtf_faq_import_down',max(0,(int)$entry['votes']['down']));
    $imported++;
}
if (!empty($data['tsukuyumiShortcode']) && !get_option('wtf_faq_tsukuyumi_shortcode')) { update_option('wtf_faq_tsukuyumi_shortcode',$data['tsukuyumiShortcode']); }
WP_CLI::success('Przeniesiono '.$imported.' pytań; poprzednie edycje zachowane.');
