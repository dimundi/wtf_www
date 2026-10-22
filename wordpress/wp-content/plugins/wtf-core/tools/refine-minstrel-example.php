<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(927);
if(!$page || get_post_meta(927,'_wtf_ele_key',true)!=='minstrel:pl')WP_CLI::error('Brak Minstrela.');
if(get_post_meta(927,'_wtf_minstrel_example_moved',true)){WP_CLI::success('Przykład już przeniesiony.');return;}
$blocks=parse_blocks($page->post_content);$example=null;
$find=function($items)use(&$find,&$example){foreach($items as $b){if(($b['attrs']['label']??'')==='Galeria kart')foreach($b['innerBlocks'] as $gallery)foreach($gallery['innerBlocks'] as $figure){if(($figure['innerBlocks'][0]['attrs']['imageId']??0)===449)$example=$figure;}if($b['innerBlocks'])$find($b['innerBlocks']);}};
$find($blocks);if(!$example)WP_CLI::error('Brak ilustracji przykładu.');
$example['attrs']['attributes']['class']='elekt-addon-instruction-example';
$moved=0;$removed=0;
$walk=function($items)use(&$walk,$example,&$moved,&$removed){$result=[];foreach($items as $b){
 if(($b['attrs']['label']??'')==='Galeria kart'){$removed++;continue;}
 if($b['innerBlocks']){$before=count($b['innerBlocks']);$b['innerBlocks']=$walk($b['innerBlocks']);if($before!==count($b['innerBlocks'])){$b['innerHTML']='';$b['innerContent']=array_fill(0,count($b['innerBlocks']),null);}}
 if(($b['attrs']['attributes']['class']??'')==='elekt-addon-rule-step' && ($b['attrs']['label']??'')==='Pieśń Zwycięstwa'){
  $b['innerBlocks'][]=$example;$b['innerHTML']='';$b['innerContent']=array_fill(0,count($b['innerBlocks']),null);$moved++;
 }
 $result[]=$b;
}return $result;};
$updated=serialize_blocks($walk($blocks));
if($moved!==1||$removed!==1)WP_CLI::error('Nieoczekiwana struktura.');
update_post_meta(927,'_wtf_before_minstrel_example_move',$page->post_content);
$saved=wp_update_post(['ID'=>927,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(927,'_wtf_minstrel_example_moved',true);
WP_CLI::success('Przykład przeniesiony do Pieśni Zwycięstwa; powtórzona okładka i galeria usunięte z treści.');
