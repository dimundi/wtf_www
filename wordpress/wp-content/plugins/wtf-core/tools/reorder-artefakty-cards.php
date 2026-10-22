<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(954);
if(!$page || get_post_meta(954,'_wtf_ele_key',true)!=='artefakty:pl')WP_CLI::error('Brak Artefaktów.');
$found=0;
$walk=function($blocks)use(&$walk,&$found){
 foreach($blocks as &$block){
  if($block['innerBlocks'])$block['innerBlocks']=$walk($block['innerBlocks']);
  if(($block['attrs']['attributes']['class']??'')!=='elekt-card-carousel-track')continue;
  $found++;$cards=[];$instructions=[];
  foreach($block['innerBlocks'] as $card){
   if(str_starts_with($card['attrs']['label']??'','Skrót zasad'))$instructions[]=$card;
   else $cards[]=$card;
  }
  if(count($cards)!==3||count($instructions)!==2)WP_CLI::error('Nieoczekiwana liczba kart.');
  $block['innerBlocks']=array_merge($cards,$instructions);
 }
 return $blocks;
};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if($found!==1)WP_CLI::error('Brak jednej karuzeli.');
$saved=wp_update_post(['ID'=>954,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
WP_CLI::success('Karty instrukcji przeniesiono na koniec.');
