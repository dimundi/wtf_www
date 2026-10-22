<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(417);
if(!$page || get_post_meta(417,'_wtf_ele_key',true)!=='powolanie2:pl') { WP_CLI::error('Brak podstrony.'); }
$blocks=parse_blocks($page->post_content);
if(count($blocks)!==1 || ($blocks[0]['attrs']['attributes']['class']??'')!=='elekt-world elekt-expansion-page elekt-addon-page wrap') { WP_CLI::error('Nieoczekiwany układ.'); }
$children=$blocks[0]['innerBlocks'];$sections=[];
foreach($children as $i=>&$block) {
    if(($block['attrs']['attributes']['class']??'')==='elekt-expansion-back') {
        $block['attrs']['content']='Poznaj grę Elekt';
        $block['attrs']['attributes']['href']=home_url('/elekt/');
    }
    if(($block['attrs']['attributes']['class']??'')==='elekt-addon-section') { $sections[]=$i; }
}unset($block);
if(count($sections)!==2) { WP_CLI::error('Oczekiwano dwóch sekcji.'); }
$heading=$children[$sections[0]]['innerBlocks'][0]['innerBlocks'][0]['attrs']['content']??'';
if($heading==='W pudełku') {
    [$children[$sections[0]],$children[$sections[1]]]=[$children[$sections[1]],$children[$sections[0]]];
}elseif($heading!=='Ze świata Elekta') { WP_CLI::error('Nieznana sekcja.'); }
$blocks[0]['innerBlocks']=$children;
if(!metadata_exists('post',417,'_wtf_before_refine_addon_page')) { update_post_meta(417,'_wtf_before_refine_addon_page',$page->post_content); }
$saved=wp_update_post(['ID'=>417,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
WP_CLI::success('Zmieniono link i kolejność sekcji.');
