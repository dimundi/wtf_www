<?php
// Ręczne uzupełnienie ilustracji ze starej strony 7973; wp eval-file.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$pages=get_posts(['post_type'=>'page','post_status'=>'publish','meta_key'=>'_wtf_tsu_key','meta_value'=>'wum:pl','numberposts'=>1,'suppress_filters'=>true]);
if (count($pages)!==1) { WP_CLI::error('Nie znaleziono strony WUM.'); }
$page=$pages[0];
if (get_post_meta($page->ID,'_wtf_wum_images_restored',true)) { WP_CLI::success('Ilustracje już dodane.'); return; }
require_once ABSPATH.'wp-admin/includes/image.php';
$files=['Wladcy.jpg'=>'Władcy Utraconych Mórz — ilustracja frakcji','Anik_Malak.jpg'=>'Anik i Malak — jednostki Władców Utraconych Mórz','King.jpg'=>'Król — jednostka Władców Utraconych Mórz'];
foreach ([1,2,3,4,5,6,8,7] as $n) { $files['WUM_'.$n.'.jpg']='Władcy Utraconych Mórz — zdjęcie '.$n.' autorstwa @photoboardgamer'; }
$images=[];
foreach($files as $file=>$alt) {
    $relative='media/tsu-wum-'.$file;
    $path=wp_get_upload_dir()['basedir'].'/'.$relative;
    if (!is_file($path)) { WP_CLI::error('Brak pliku '.$relative); }
    $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wtf_source_asset','meta_value'=>$relative,'suppress_filters'=>true]);
    if ($existing) { $id=$existing[0]->ID; }
    else {
        $id=wp_insert_attachment(['post_title'=>pathinfo($file,PATHINFO_FILENAME),'post_mime_type'=>'image/jpeg','post_status'=>'inherit'], $path, 0, true);
        if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
        update_post_meta($id,'_wtf_source_asset',$relative);
        update_post_meta($id,'_wp_attachment_image_alt',$alt);
        wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$path));
        if (str_starts_with($file,'WUM_')) { wp_update_post(['ID'=>$id,'post_excerpt'=>'Fot. @photoboardgamer']); }
    }
    if (taxonomy_exists('wtf_media_group')) {
        $term=get_term_by('name','Tsukuyumi','wtf_media_group');
        if ($term) { wp_set_object_terms($id,[(int)$term->term_id],'wtf_media_group',true); }
    }
    $images[$file]=['blockName'=>'wtf/tsu-image','attrs'=>['image'=>wp_get_attachment_url($id),'imageId'=>$id,'alt'=>$alt,'attributes'=>['loading'=>'lazy']],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
}
$container=static function($class,$children) { return ['blockName'=>'wtf/tsu-container','attrs'=>['tag'=>'div','attributes'=>['class'=>$class]],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)]; };
$text=static function($tag,$content) { return ['blockName'=>'wtf/tsu-text','attrs'=>['tag'=>$tag,'content'=>$content],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]]; };
$found=0;
$walk=function($blocks) use (&$walk,&$found,$images,$container,$text) {
    foreach($blocks as &$block) {
        if ($block['innerBlocks']) { $block['innerBlocks']=$walk($block['innerBlocks']); }
        if (($block['attrs']['attributes']['class']??'')!=='tsu-faction-description') { continue; }
        $found++;
        $children=[]; $inserted=false;
        foreach($block['innerBlocks'] as $child) {
            if (($child['attrs']['content']??'')==='TAKTYKA') { $children[]=$images['Wladcy.jpg']; $inserted=true; }
            $children[]=$child;
        }
        if (!$inserted) { WP_CLI::error('Brak sekcji TAKTYKA.'); }
        $children[]=$container('tsu-faction-art-pair',[$images['Anik_Malak.jpg'],$images['King.jpg']]);
        $children[]=$text('h3','Galeria');
        $children[]=$text('p','Frakcja Władcy Utraconych Mórz w obiektywie @photoboardgamer. Kliknij zdjęcie, aby zobaczyć je w pełnym rozmiarze.');
        $photos=[];
        foreach ([1,2,3,4,5,6,8,7] as $n) {
            $image=$images['WUM_'.$n.'.jpg'];
            $link=$container('tsu-wum-gallery-photo',[$image]);
            $link['attrs']['tag']='a';
            $link['attrs']['attributes']['href']=$image['attrs']['image'];
            $photos[]=$link;
        }
        $children[]=$container('tsu-wum-gallery',$photos);
        $block['innerBlocks']=$children; $block['innerHTML']=''; $block['innerContent']=array_fill(0,count($children),null);
    }
    return $blocks;
};
$blocks=$walk(parse_blocks($page->post_content));
if ($found!==1) { WP_CLI::error('Nieoczekiwana struktura strony.'); }
update_post_meta($page->ID,'_wtf_wum_before_images',$page->post_content);
$result=wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
update_post_meta($page->ID,'_wtf_wum_images_restored',7973);
WP_CLI::success('Dodano 3 ilustracje i 8 zdjęć galerii.');
