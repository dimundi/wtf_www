<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(927);
if(!$page || get_post_meta(927,'_wtf_ele_key',true)!=='minstrel:pl')WP_CLI::error('Brak Minstrela.');
if(get_post_meta(927,'_wtf_minstrel_cards_added',true)){WP_CLI::success('Karty już dodane.');return;}
require_once ABSPATH.'wp-admin/includes/image.php';
$assets=[2=>['Marsz Straceńców','Karta Melodii Marsz Straceńców z dodatku Minstrel: drewniana postać grająca na bębnie.'],3=>['Minstrel','Karta Minstrel do gry Elekt: zamaskowana postać w kapeluszu grająca na instrumencie strunowym.']];
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content)=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cards=[];
foreach($assets as $n=>[$name,$alt]){
 $relative='media/elekt-minstrel-karta-'.$n.'.png';$path=wp_get_upload_dir()['basedir'].'/'.$relative;
 if(!is_file($path))WP_CLI::error('Brak pliku '.$relative);
 $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wtf_source_asset','meta_value'=>$relative,'suppress_filters'=>true]);
 if($existing)$id=$existing[0]->ID;
 else{
  $id=wp_insert_attachment(['post_title'=>'Elekt — Minstrel — '.$name,'post_content'=>$alt.' Ilustracje dodatku: Katarzyna Malinowska i Paweł Góral.','post_excerpt'=>$name.' — dodatek Minstrel.','post_mime_type'=>'image/png','post_status'=>'inherit'],$path,0,true);
  if(is_wp_error($id))WP_CLI::error($id->get_error_message());
  update_post_meta($id,'_wtf_source_asset',$relative);update_post_meta($id,'_wp_attachment_image_alt',$alt);wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$path));
 }
 $term=get_term_by('name','Elekt','wtf_media_group');if($term)wp_set_object_terms($id,[(int)$term->term_id],'wtf_media_group',true);
 $image=['blockName'=>'wtf/ele-image','attrs'=>['imageId'=>$id,'image'=>wp_get_attachment_url($id),'alt'=>$alt,'attributes'=>['loading'=>'lazy']],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
 $cards[]=$make('figure','elekt-addon-card elekt-addon-card-portrait',[$image,$text('figcaption',$name)],$name);
}
$blocks=parse_blocks($page->post_content);$found=0;
$walk=function($items)use(&$walk,&$found){foreach($items as &$b){
 if($b['innerBlocks'])$b['innerBlocks']=$walk($b['innerBlocks']);
 if(($b['attrs']['attributes']['class']??'')==='elekt-addon-rule-step'&&($b['attrs']['label']??'')==='Pieśń Zwycięstwa'){
  $b['attrs']['attributes']['class'].=' elekt-addon-rule-step-with-example';$found++;
 }
}return $items;};
$blocks=$walk($blocks);if($found!==1)WP_CLI::error('Brak kroku Pieśni Zwycięstwa.');
$blocks[0]['innerBlocks'][]=$make('section','elekt-addon-art',[$text('h2','Poznaj karty'),$make('div','elekt-addon-card-gallery elekt-addon-card-gallery-portrait',$cards,'Przykładowe karty Minstrela')],'Galeria kart');
$blocks[0]['innerHTML']='';$blocks[0]['innerContent']=array_fill(0,count($blocks[0]['innerBlocks']),null);
update_post_meta(927,'_wtf_before_minstrel_cards',$page->post_content);
$saved=wp_update_post(['ID'=>927,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(927,'_wtf_minstrel_cards_added',true);
WP_CLI::success('Dodano dwie karty i ustawiono mniejszy przykład obok kroku 05.');
