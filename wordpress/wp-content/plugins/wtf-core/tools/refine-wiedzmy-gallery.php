<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(421);
if(!$page || get_post_meta(421,'_wtf_ele_key',true)!=='wiedzmy:pl')WP_CLI::error('Brak Wiedźm.');
$found=0;
$walk=function($blocks) use (&$walk,&$found){
 foreach($blocks as &$block){
  if($block['innerBlocks'])$block['innerBlocks']=$walk($block['innerBlocks']);
  if(($block['attrs']['label']??'')!=='Galeria kart')continue;
  $found++;
  if(($block['innerBlocks'][0]['attrs']['content']??'')!=='Przyjrzyj się kartom'){
   array_unshift($block['innerBlocks'],['blockName'=>'wtf/ele-text','attrs'=>['tag'=>'h2','content'=>'Przyjrzyj się kartom'],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]]);
   $block['innerHTML']='';$block['innerContent']=array_fill(0,count($block['innerBlocks']),null);
  }
 }
 return $blocks;
};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if($found!==1)WP_CLI::error('Nieoczekiwana liczba galerii.');
$saved=wp_update_post(['ID'=>421,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
foreach(['elekt-wiedzmy-karta-swieto.png','elekt-wiedzmy-karta-laznie.png'] as $file){
 $items=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wtf_source_asset','meta_value'=>'media/'.$file,'suppress_filters'=>true]);
 if(count($items)!==1)WP_CLI::error('Brak zdjęcia '.$file);
 $p=$items[0];
 echo wp_json_encode(['title'=>$p->post_title,'alt'=>get_post_meta($p->ID,'_wp_attachment_image_alt',true),'description'=>$p->post_content,'caption'=>$p->post_excerpt,'groups'=>wp_get_object_terms($p->ID,'wtf_media_group',['fields'=>'names']),'url'=>wp_get_attachment_url($p->ID)],JSON_UNESCAPED_UNICODE).PHP_EOL;
}
