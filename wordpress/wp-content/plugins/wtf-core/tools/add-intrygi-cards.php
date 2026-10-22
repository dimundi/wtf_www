<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(925);
if(!$page || get_post_meta(925,'_wtf_ele_key',true)!=='intrygi:pl')WP_CLI::error('Brak Intryg.');
if(get_post_meta(925,'_wtf_intrygi_card_gallery',true)){WP_CLI::success('Galeria już dodana.');return;}
require_once ABSPATH.'wp-admin/includes/image.php';
$assets=[
'elekt-intrygi-karta-1.png'=>['Rafineria Żaru','Karta Rafineria Żaru z dodatku Intrygi: przemysłowe wnętrze z postacią przy rozświetlonym piecu i mechanicznym urządzeniem.'],
'elekt-intrygi-karta-2.png'=>['Czarny Rynek','Karta Czarny Rynek z dodatku Intrygi: opuszczona hala z przeszklonym dachem i postacią pośród stoisk.'],
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
  $id=wp_insert_attachment(['post_title'=>'Elekt — Intrygi — '.$name,'post_content'=>$alt.' Ilustracje dodatku: Katarzyna Malinowska.','post_excerpt'=>$name.' — karta z dodatku Intrygi.','post_mime_type'=>'image/png','post_status'=>'inherit'],$path,0,true);
  if(is_wp_error($id))WP_CLI::error($id->get_error_message());
  update_post_meta($id,'_wtf_source_asset',$relative);update_post_meta($id,'_wp_attachment_image_alt',$alt);
  wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$path));
 }
 $term=get_term_by('name','Elekt','wtf_media_group');if($term)wp_set_object_terms($id,[(int)$term->term_id],'wtf_media_group',true);
 $image=['blockName'=>'wtf/ele-image','attrs'=>['imageId'=>$id,'image'=>wp_get_attachment_url($id),'alt'=>$alt,'attributes'=>['loading'=>'lazy']],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
 $cards[]=$make('figure','elekt-addon-card',[$make('div','elekt-addon-card-rotated',[$image],'Karta obrócona 90 stopni w prawo'),$text('figcaption',$name)],$name);
}
$gallery=$make('section','elekt-addon-art',[$text('h2','Poznaj karty'),$make('div','elekt-addon-card-gallery',$cards,'Karty Intryg')],'Galeria kart');
$blocks=parse_blocks($page->post_content);
if(count($blocks)!==1)WP_CLI::error('Nieoczekiwany układ.');
$children=[];$replaced=0;
foreach($blocks[0]['innerBlocks'] as $block){
 if(($block['attrs']['attributes']['class']??'')==='elekt-addon-art'){$children[]=$gallery;$replaced++;}else $children[]=$block;
}
if($replaced!==1)WP_CLI::error('Oczekiwano jednej starej galerii.');
$blocks[0]['innerBlocks']=$children;$blocks[0]['innerHTML']='';$blocks[0]['innerContent']=array_fill(0,count($children),null);
update_post_meta(925,'_wtf_before_intrygi_card_gallery',$page->post_content);
$saved=wp_update_post(['ID'=>925,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(925,'_wtf_intrygi_card_gallery',true);
WP_CLI::success('Zastąpiono galerię dwiema nowymi kartami obróconymi w prawo.');
