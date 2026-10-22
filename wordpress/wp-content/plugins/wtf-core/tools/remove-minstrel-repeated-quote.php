<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(927);
if(!$page || get_post_meta(927,'_wtf_ele_key',true)!=='minstrel:pl')WP_CLI::error('Brak Minstrela.');
$removed=0;
$walk=function($blocks,$inLore=false)use(&$walk,&$removed){
 $result=[];foreach($blocks as $block){
  $local=$inLore||($block['attrs']['label']??'')==='Opis fabularny';
  if($local&&($block['attrs']['tag']??'')==='blockquote'&&str_contains(serialize_blocks([$block]),'Zwerbuj Minstrela')){$removed++;continue;}
  if($block['innerBlocks']){
   $before=count($block['innerBlocks']);$block['innerBlocks']=$walk($block['innerBlocks'],$local);
   if($before!==count($block['innerBlocks'])){$block['innerHTML']='';$block['innerContent']=array_fill(0,count($block['innerBlocks']),null);}
  }
  $result[]=$block;
 }return $result;
};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if(!$removed){WP_CLI::success('Cytatu już nie ma.');return;}
if($removed!==1)WP_CLI::error('Nieoczekiwana liczba cytatów.');
update_post_meta(927,'_wtf_before_remove_repeated_quote',$page->post_content);
$saved=wp_update_post(['ID'=>927,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
WP_CLI::success('Usunięto cytat z opisu fabularnego.');
