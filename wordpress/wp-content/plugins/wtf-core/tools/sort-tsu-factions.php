<?php
// Jawne uporządkowanie istniejących kart i nawigacji; nie zmienia treści ani adresów.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(68);
if (!$page || get_post_meta(68,'_wtf_tsu_key',true)!=='main:pl') { WP_CLI::error('Brak strony Tsukuyumi PL.'); }
$order=json_decode(file_get_contents(__DIR__.'/tsu-faction-order.json'),true,512,JSON_THROW_ON_ERROR);
$rank=array_flip(array_column($order,'id'));
$grids=0; $menus=0;
$walk=function($blocks) use (&$walk,&$grids,&$menus,$rank) {
    foreach($blocks as &$block) {
        if ($block['innerBlocks']) { $block['innerBlocks']=$walk($block['innerBlocks']); }
        if (($block['attrs']['attributes']['class']??'')==='tsu-faction-grid') {
            if (count($block['innerBlocks'])!==count($rank)) { WP_CLI::error('Nieoczekiwana liczba kart.'); }
            foreach($block['innerBlocks'] as $child) { if(!isset($rank[$child['attrs']['attributes']['id']??''])) { WP_CLI::error('Nieznana karta.'); } }
            usort($block['innerBlocks'],static fn($a,$b)=>$rank[$a['attrs']['attributes']['id']]<=>$rank[$b['attrs']['attributes']['id']]);
            $grids++;
        }
        if ($block['blockName']==='core/html' && strpos($block['innerHTML'],'id="tsu-menu-factions"')!==false) {
            $block['innerHTML']=preg_replace_callback('~(<div id="tsu-menu-factions"[^>]*>)(.*?)(</div>)~s',function($m) use ($rank,&$menus) {
                preg_match_all('~<a href="#([^"]+)"[^>]*>.*?</a>~s',$m[2],$links,PREG_SET_ORDER);
                if(count($links)!==count($rank)) { WP_CLI::error('Nieoczekiwana liczba linków.'); }
                foreach($links as $link) { if(!isset($rank[$link[1]])) { WP_CLI::error('Nieznany link.'); } }
                usort($links,static fn($a,$b)=>$rank[$a[1]]<=>$rank[$b[1]]);
                $menus++;
                return $m[1].implode('',array_column($links,0)).$m[3];
            },$block['innerHTML']);
            $block['innerContent']=[$block['innerHTML']];
        }
    }
    return $blocks;
};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if ($grids!==1 || $menus!==1) { WP_CLI::error('Oczekiwano jednej listy i jednego menu: '.$grids.'/'.$menus); }
if (!metadata_exists('post',68,'_wtf_before_faction_sort')) { update_post_meta(68,'_wtf_before_faction_sort',$page->post_content); }
$result=wp_update_post(['ID'=>68,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
WP_CLI::success('Uporządkowano 16 frakcji i menu alfabetycznie.');
