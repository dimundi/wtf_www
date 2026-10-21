<?php
// Teksty źródłowe ze strony 11031 w whatthefrogpl_backup.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(423);
if(!$page || get_post_meta(423,'_wtf_ele_key',true)!=='wiedzmy2:pl') { WP_CLI::error('Brak podstrony Wiedźm 2.'); }
if(get_post_meta(423,'_wtf_wiedzmy2_description_restored',true)) { WP_CLI::success('Opis już uzupełniony.'); return; }
$source=json_decode(file_get_contents(__DIR__.'/wiedzmy2-description.json'),true,512,JSON_THROW_ON_ERROR);
$added=[];
foreach($source as $text) {
    $added[]=['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$text['tag'],'content'=>$text['content']],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
}
$found=0;
$walk=function($blocks) use (&$walk,&$found,$added) {
    foreach($blocks as &$block) {
        if($block['innerBlocks']) { $block['innerBlocks']=$walk($block['innerBlocks']); }
        if(($block['attrs']['attributes']['class']??'')==='expansion-description') {
            $found++;
            $children=[];
            foreach($block['innerBlocks'] as $child) {
                if(($child['attrs']['attributes']['class']??'')==='expansion-actions') { $children=array_merge($children,$added); }
                $children[]=$child;
            }
            if(count($children)!==count($block['innerBlocks'])+count($added)) { WP_CLI::error('Brak kontenera przycisków.'); }
            $block['innerBlocks']=$children;$block['innerHTML']='';$block['innerContent']=array_fill(0,count($children),null);
        }
    }
    return $blocks;
};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if($found!==1) { WP_CLI::error('Nieoczekiwana struktura.'); }
update_post_meta(423,'_wtf_before_restore_wiedzmy2_description',$page->post_content);
$saved=wp_update_post(['ID'=>423,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
update_post_meta(423,'_wtf_wiedzmy2_description_restored',11031);
WP_CLI::success('Dodano cztery źródłowe teksty Wiedźm 2.');
