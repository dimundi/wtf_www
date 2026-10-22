<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(419);
if(!$page || get_post_meta(419,'_wtf_ele_key',true)!=='rubiez:pl') { WP_CLI::error('Brak Rubieży.'); }
if(get_post_meta(419,'_wtf_rubiez_card_gallery',true)) { WP_CLI::success('Galeria już dodana.');return; }
require_once ABSPATH.'wp-admin/includes/image.php';
$assets=[
    'elekt-rubiez-karta-misji.png'=>['Przeprawa przez Ostre Szpony','Karta Przeprawa przez Ostre Szpony z dodatku Rubież: wiszący most i statek powietrzny nad zaśnieżonym wąwozem.'],
    'elekt-rubiez-karta-wplywow.png'=>['Rytuał Ostatniego Poświęcenia','Karta Rytuał Ostatniego Poświęcenia z dodatku Rubież: postać odprawiająca rytuał nad kotłem z zielonym blaskiem.'],
];
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content)=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cards=[];
foreach($assets as $file=>[$name,$alt]) {
    $relative='media/'.$file;$path=wp_get_upload_dir()['basedir'].'/'.$relative;
    if(!is_file($path)) { WP_CLI::error('Brak pliku '.$file); }
    $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wtf_source_asset','meta_value'=>$relative,'suppress_filters'=>true]);
    if($existing) { $id=$existing[0]->ID; }
    else {
        $id=wp_insert_attachment(['post_title'=>'Elekt — Rubież — '.$name,'post_content'=>$alt,'post_excerpt'=>$name.' — karta z dodatku Rubież.','post_mime_type'=>'image/png','post_status'=>'inherit'],$path,0,true);
        if(is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
        update_post_meta($id,'_wtf_source_asset',$relative);update_post_meta($id,'_wp_attachment_image_alt',$alt);
        wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$path));
    }
    $term=get_term_by('name','Elekt','wtf_media_group');if($term)wp_set_object_terms($id,[(int)$term->term_id],'wtf_media_group',true);
    $image=['blockName'=>'wtf/ele-image','attrs'=>['imageId'=>$id,'image'=>wp_get_attachment_url($id),'alt'=>$alt,'attributes'=>['loading'=>'lazy']],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
    $cards[]=$make('figure','elekt-addon-card',[$image,$text('figcaption',$name)],$name);
}
$gallery=$make('section','elekt-addon-art',[
    $text('h2','Przyjrzyj się kartom'),$make('div','elekt-addon-card-gallery',$cards,'Karty Rubieży'),
],'Galeria kart');
$blocks=parse_blocks($page->post_content);
if(count($blocks)!==1 || ($blocks[0]['attrs']['attributes']['class']??'')!=='elekt-world elekt-expansion-page elekt-addon-page wrap') { WP_CLI::error('Nieoczekiwana struktura.'); }
$children=$blocks[0]['innerBlocks'];$position=count($children);
foreach($children as $i=>$child) { if(($child['attrs']['label']??'')==='FAQ Rubież') { $position=$i;break; } }
array_splice($children,$position,0,[$gallery]);
$blocks[0]['innerBlocks']=$children;$blocks[0]['innerHTML']='';$blocks[0]['innerContent']=array_fill(0,count($children),null);
update_post_meta(419,'_wtf_before_rubiez_card_gallery',$page->post_content);
$saved=wp_update_post(['ID'=>419,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
update_post_meta(419,'_wtf_rubiez_card_gallery',true);
WP_CLI::success('Dodano dwie karty w galerii przed FAQ.');
