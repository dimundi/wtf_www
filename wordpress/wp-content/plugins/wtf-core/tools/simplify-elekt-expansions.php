<?php
// Jawna korekta listy PL: pełne opisy dostępne na podstronach dodatków.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(415);
if (!$page || get_post_meta(415,'_wtf_ele_key',true)!=='main:pl') { WP_CLI::error('Brak strony Elekt PL.'); }
$removed=0; $eligible=[];
$walk=function($blocks,$hasPage=false) use (&$walk,&$removed,&$eligible) {
    $result=[];
    foreach($blocks as $block) {
        $attrs=$block['attrs']['attributes']??[];
        $local=$hasPage;
        if (($attrs['class']??'')==='elekt-expansion') {
            $local=false;
            $html=render_block($block);
            if (preg_match('~<a class="expansion-page-link" href="([^"]+)"~',$html,$match)) {
                $id=url_to_postid(html_entity_decode($match[1]));
                if ($id && get_post_status($id)==='publish' && get_post_type($id)==='page') {
                    $local=true; $eligible[]=$attrs['id'];
                }
            }
        }
        if ($local && ($attrs['class']??'')==='expansion-full-description' && ($block['attrs']['tag']??'')==='details') { $removed++; continue; }
        if($block['innerBlocks']) {
            $before=count($block['innerBlocks']);
            $block['innerBlocks']=$walk($block['innerBlocks'],$local);
            if($before!==count($block['innerBlocks'])) {
                if($block['blockName']!=='wtf/ele-container') { WP_CLI::error('Nieoczekiwany kontener.'); }
                $block['innerHTML']=''; $block['innerContent']=array_fill(0,count($block['innerBlocks']),null);
            }
        }
        $result[]=$block;
    }
    return $result;
};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if(count($eligible)!==4) { WP_CLI::error('Oczekiwano 4 dodatków z podstronami.'); }
if(!$removed) { WP_CLI::success('Brak powielonych rozwijanych opisów.'); return; }
if($removed!==3) { WP_CLI::error('Nieoczekiwana liczba opisów: '.$removed); }
if(!metadata_exists('post',415,'_wtf_before_simplify_expansions')) { update_post_meta(415,'_wtf_before_simplify_expansions',$page->post_content); }
$saved=wp_update_post(['ID'=>415,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
WP_CLI::success('Usunięto rozwijane opisy Powołania 2, Rubieży i Wiedźm. Wiedźmy 2 już nie miały rozwijania.');
