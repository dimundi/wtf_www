<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$oldIds=[439,440];
$patterns=['Elekt-Artefakty-Dodatek-przykladowe-karty-01','Elekt-Artefakty-Dodatek-przykladowe-karty-02'];
$matches=static function($block)use($oldIds,$patterns){
 if(in_array($block['attrs']['imageId']??$block['attrs']['id']??0,$oldIds,true))return true;
 $url=$block['attrs']['image']??$block['attrs']['url']??'';
 foreach($patterns as $pattern)if(str_contains($url,$pattern))return true;
 return false;
};
$walk=function($blocks)use(&$walk,$matches){
 $result=[];foreach($blocks as $block){
  if($matches($block))continue;
  if($block['innerBlocks']){
   $before=count($block['innerBlocks']);$block['innerBlocks']=$walk($block['innerBlocks']);
   if($before!==count($block['innerBlocks'])){
    if($block['blockName']!=='wtf/ele-container')WP_CLI::error('Nieoczekiwany kontener.');
    if(!$block['innerBlocks'])continue;
    $block['innerHTML']='';$block['innerContent']=array_fill(0,count($block['innerBlocks']),null);
   }
  }
  $result[]=$block;
 }return $result;
};
foreach(get_posts(['post_type'=>'page','post_status'=>['publish','draft','private','pending','future','trash'],'numberposts'=>-1,'suppress_filters'=>true]) as $page){
 $old=parse_blocks($page->post_content);$new=$walk($old);
 if($new===$old)continue;
 $saved=wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash(serialize_blocks($new))],true);
 if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
 WP_CLI::log('Usunięto stare zdjęcia z treści strony '.$page->ID.' ('.$page->post_status.').');
}
foreach($oldIds as $id){
 if(!get_post($id))continue;
 $path=get_attached_file($id);$basename=basename($path);$valid=false;
 foreach($patterns as $pattern)if(str_contains($basename,$pattern))$valid=true;
 if(!$valid || !str_starts_with(wp_normalize_path($path),wp_normalize_path(wp_get_upload_dir()['basedir']).'/media/'))WP_CLI::error('Nieoczekiwany plik dla '.$id);
 if(!wp_delete_attachment($id,true))WP_CLI::error('Nie usunięto załącznika '.$id);
 WP_CLI::log('Usunięto trwale załącznik '.$id.': '.$basename);
}
