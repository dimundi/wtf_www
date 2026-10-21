<?php
// Usuwa wyłącznie wskazaną parę ilustracji z treści strony, zachowując Media.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(82);
if (!$page || get_post_meta(82,'_wtf_tsu_key',true)!=='wum:pl') { WP_CLI::error('Nie znaleziono strony WUM.'); }
$removed=0;
$walk=function($blocks) use (&$walk,&$removed) {
    $result=[];
    foreach($blocks as $block) {
        if (($block['attrs']['attributes']['class']??'')==='tsu-faction-art-pair') {
            $json=wp_json_encode($block);
            if (strpos($json,'tsu-wum-Anik_Malak')!==false && strpos($json,'tsu-wum-King')!==false) { $removed++; continue; }
        }
        if ($block['innerBlocks']) {
            $before=count($block['innerBlocks']);
            $block['innerBlocks']=$walk($block['innerBlocks']);
            if ($before!==count($block['innerBlocks'])) {
                if ($block['blockName']!=='wtf/tsu-container') { WP_CLI::error('Nieoczekiwany kontener.'); }
                $block['innerHTML']='';
                $block['innerContent']=array_fill(0,count($block['innerBlocks']),null);
            }
        }
        $result[]=$block;
    }
    return $result;
};
$blocks=$walk(parse_blocks($page->post_content));
if (!$removed) { WP_CLI::success('Brak wskazanych ilustracji na stronie.'); return; }
if ($removed!==1) { WP_CLI::error('Nieoczekiwana liczba par.'); }
update_post_meta(82,'_wtf_wum_before_remove_unit_art',$page->post_content);
$result=wp_update_post(['ID'=>82,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
WP_CLI::success('Usunięto parę ilustracji Anik/Malak i Król.');
