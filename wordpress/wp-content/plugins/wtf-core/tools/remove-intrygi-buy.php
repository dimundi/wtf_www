<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
foreach ([415,925] as $id) {
    $page=get_post($id);
    if (!$page || !in_array(get_post_meta($id,'_wtf_ele_key',true),['main:pl','intrygi:pl'],true)) { WP_CLI::error('Nieoczekiwana strona.'); }
    $removed=0;
    $walk=function($blocks,$inside=false) use (&$walk,&$removed) {
        $result=[];
        foreach($blocks as $block) {
            $local=$inside || ($block['attrs']['attributes']['id']??'')==='dodatek-intrygi';
            if($local && ($block['attrs']['attributes']['class']??'')==='expansion-buy') { $removed++; continue; }
            if($block['innerBlocks']) {
                $before=count($block['innerBlocks']);
                $block['innerBlocks']=$walk($block['innerBlocks'],$local);
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
    if(!$removed) { continue; }
    update_post_meta($id,'_wtf_before_remove_intrygi_buy',$page->post_content);
    $saved=wp_update_post(['ID'=>$id,'post_content'=>wp_slash($updated)],true);
    if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
    WP_CLI::log('Usunięto przycisk ze strony '.$id.'.');
}
