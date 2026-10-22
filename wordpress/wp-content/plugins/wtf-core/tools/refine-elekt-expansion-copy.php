<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(415);
if(!$page || get_post_meta(415,'_wtf_ele_key',true)!=='main:pl')WP_CLI::error('Brak Elekta.');
if(get_post_meta(415,'_wtf_expansion_copy_refined',true)){WP_CLI::success('Opisy już poprawione.');return;}
$removed=0;$edited=0;
$walk=function($blocks,$card='')use(&$walk,&$removed,&$edited){
 $result=[];
 foreach($blocks as $b){
  $local=$card;$id=$b['attrs']['attributes']['id']??'';
  if(in_array($id,['dodatek-wiedzmy','dodatek-intrygi'],true))$local=$id;
  if($local==='dodatek-wiedzmy' && ($b['attrs']['tag']??'')==='blockquote' && str_contains(serialize_blocks([$b]),'Na najwyższych piętrach')){$removed++;continue;}
  if($local==='dodatek-wiedzmy' && $b['blockName']==='wtf/ele-text' && str_starts_with($b['attrs']['content']??'','Na najwyższych piętrach')){$removed++;continue;}
  if($local==='dodatek-intrygi' && $b['blockName']==='wtf/ele-text' && str_starts_with($b['attrs']['content']??'','Duży dodatek dla prawdziwych fanów')){
   $b['attrs']['content']='Dodatek dla graczy szukających nowych wyzwań. Wylosowana lub wybrana przed grą Intryga zmienia zasady dla wszystkich aż do końca rozgrywki. Nowe Misje i Wpływy pozwalają poświęcać zasoby w zamian za Tytuły — zaplanuj, co jesteś gotów oddać.';
   $edited++;
  }
  if($b['innerBlocks']){
   $before=count($b['innerBlocks']);$b['innerBlocks']=$walk($b['innerBlocks'],$local);
   if($before!==count($b['innerBlocks'])){
    if($b['blockName']!=='wtf/ele-container')WP_CLI::error('Nieoczekiwany kontener.');
    $b['innerHTML']='';$b['innerContent']=array_fill(0,count($b['innerBlocks']),null);
   }
  }
  $result[]=$b;
 }
 return $result;
};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if($removed!==1||$edited!==1)WP_CLI::error('Nie znaleziono obu wskazanych opisów: '.$removed.'/'.$edited);
update_post_meta(415,'_wtf_before_expansion_copy_refined',$page->post_content);
$saved=wp_update_post(['ID'=>415,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(415,'_wtf_expansion_copy_refined',true);
WP_CLI::success('Usunięto cytat Wiedźm i skrócono zajawkę Intryg.');
