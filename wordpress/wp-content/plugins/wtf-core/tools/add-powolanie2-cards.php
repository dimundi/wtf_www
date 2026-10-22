<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(417);
if(!$page || get_post_meta(417,'_wtf_ele_key',true)!=='powolanie2:pl')WP_CLI::error('Brak Powołania 2.');
if(get_post_meta(417,'_wtf_powolanie2_card_gallery',true)){WP_CLI::success('Galeria już dodana.');return;}
require_once ABSPATH.'wp-admin/includes/image.php';
$assets=[
'elekt-powolanie2-karta-zarowy-cyborg.png'=>['Żarowy cyborg','Karta Żarowy cyborg z dodatku Powołanie 2: mężczyzna z mechaniczną ręką i nogą rozświetlonymi pomarańczowym żarem.'],
'elekt-powolanie2-karta-technomantka.png'=>['Technomantka','Karta Technomantka z dodatku Powołanie 2: rudowłosa postać z ciężką bronią i mechanicznym wyposażeniem.'],
];
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content)=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cards=[];
foreach($assets as $file=>[$name,$alt]){
 $relative='media/'.$file;$path=wp_get_upload_dir()['basedir'].'/'.$relative;
 if(!is_file($path))WP_CLI::error('Brak '.$file);
 $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wtf_source_asset','meta_value'=>$relative,'suppress_filters'=>true]);
 if($existing)$id=$existing[0]->ID;
 else{
  $id=wp_insert_attachment(['post_title'=>'Elekt — Powołanie 2 — '.$name,'post_content'=>$alt.' Ilustracje dodatku: Justyna Szumczyk.','post_excerpt'=>$name.' — karta z dodatku Powołanie 2.','post_mime_type'=>'image/png','post_status'=>'inherit'],$path,0,true);
  if(is_wp_error($id))WP_CLI::error($id->get_error_message());
  update_post_meta($id,'_wtf_source_asset',$relative);update_post_meta($id,'_wp_attachment_image_alt',$alt);
  wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$path));
 }
 $term=get_term_by('name','Elekt','wtf_media_group');if($term)wp_set_object_terms($id,[(int)$term->term_id],'wtf_media_group',true);
 $image=['blockName'=>'wtf/ele-image','attrs'=>['imageId'=>$id,'image'=>wp_get_attachment_url($id),'alt'=>$alt,'attributes'=>['loading'=>'lazy']],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
 $cards[]=$make('figure','elekt-addon-card elekt-addon-card-portrait',[$image,$text('figcaption',$name)],$name);
}
$gallery=$make('section','elekt-addon-art',[$text('h2','Przyjrzyj się kartom'),$make('div','elekt-addon-card-gallery elekt-addon-card-gallery-portrait',$cards,'Karty Powołania 2')],'Galeria kart');
$blocks=parse_blocks($page->post_content);
if(count($blocks)!==1)WP_CLI::error('Nieoczekiwany układ.');
$children=[];$replaced=0;
foreach($blocks[0]['innerBlocks'] as $block){
 if(($block['attrs']['attributes']['class']??'')==='elekt-addon-art'){$children[]=$gallery;$replaced++;}else $children[]=$block;
}
if($replaced!==1)WP_CLI::error('Oczekiwano jednej starej galerii.');
$blocks[0]['innerBlocks']=$children;$blocks[0]['innerHTML']='';$blocks[0]['innerContent']=array_fill(0,count($children),null);
update_post_meta(417,'_wtf_before_powolanie2_card_gallery',$page->post_content);
$saved=wp_update_post(['ID'=>417,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(417,'_wtf_powolanie2_card_gallery',true);
WP_CLI::success('Dodano 2 karty Powołania 2 zamiast dawnych grafik.');
