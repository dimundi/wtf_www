<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(415);
if (!$page || get_post_meta(415,'_wtf_ele_key',true)!=='main:pl') { WP_CLI::error('Brak strony Elekt PL.'); }
$removed=0;
$walk=function($blocks,$inSteps=false) use (&$walk,&$removed) {
    $result=[];
    foreach($blocks as $block) {
        $inside=$inSteps || ($block['attrs']['attributes']['class']??'')==='elekt-game-steps';
        if($inside && $block['blockName']==='wtf/ele-image' && ($block['attrs']['imageId']??0)===428) { $removed++; continue; }
        if($block['innerBlocks']) {
            $before=count($block['innerBlocks']);
            $block['innerBlocks']=$walk($block['innerBlocks'],$inside);
            if($before!==count($block['innerBlocks'])) {
                if($block['blockName']!=='wtf/ele-container') { WP_CLI::error('Nieoczekiwany kontener.'); }
                $block['innerHTML']='';$block['innerContent']=array_fill(0,count($block['innerBlocks']),null);
            }
        }
        $result[]=$block;
    }
    return $result;
};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if(!$removed) { WP_CLI::success('Wskazanego obrazu już nie ma.'); return; }
if($removed!==1) { WP_CLI::error('Nieoczekiwana liczba obrazów.'); }
update_post_meta(415,'_wtf_before_remove_rules_image',$page->post_content);
$saved=wp_update_post(['ID'=>415,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
WP_CLI::success('Usunięto obraz Cenzora z sekcji kroków gry.');
