<?php
// Jawne uzupełnienie PL z kopii starej strony, post 7973. wp eval-file.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$pages=get_posts(['post_type'=>'page','post_status'=>'publish','meta_key'=>'_wtf_tsu_key','meta_value'=>'wum:pl','numberposts'=>1,'suppress_filters'=>true]);
if (count($pages)!==1) { WP_CLI::error('Brak strony WUM.'); }
$page=$pages[0];
if (get_post_meta($page->ID,'_wtf_wum_description_restored',true)) { WP_CLI::success('Opis już uzupełniony.'); return; }
$source=json_decode(file_get_contents(__DIR__.'/wum-description.json'),true,512,JSON_THROW_ON_ERROR);
$text=static function($tag,$content) { return ['blockName'=>'wtf/tsu-text','attrs'=>['tag'=>$tag,'content'=>$content],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]]; };
$container=static function($class,$children) { return ['blockName'=>'wtf/tsu-container','attrs'=>['tag'=>'div','attributes'=>['class'=>$class]],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)]; };
$found=0;
$walk=function($blocks) use (&$walk,&$found,$source,$text,$container) {
    foreach($blocks as &$block) {
        if (($block['attrs']['content']??'')==='Tryb dla 2 graczy: do potwierdzenia') { $block['attrs']['content']='Tryb dla 2 graczy: tak'; }
        if ($block['innerBlocks']) { $block['innerBlocks']=$walk($block['innerBlocks']); }
        $class=$block['attrs']['attributes']['class']??'';
        if ($class==='tsu-faction-description') {
            $found++;
            foreach($source as $heading=>$paragraphs) {
                $block['innerBlocks'][]=$text('h3',$heading);
                foreach($paragraphs as $paragraph) { $block['innerBlocks'][]=$text('p',$paragraph); }
            }
            $block['innerHTML']='';
            $block['innerContent']=array_fill(0,count($block['innerBlocks']),null);
        }
        if (strpos($class,'tsu-faction-stats')===0) {
            $block['attrs']['attributes']['class']='tsu-faction-stats tsu-faction-stats--2';
            $list=[];
            foreach(['usuwają znaczniki z obszarów','przemieszają jednostki wroga','rozgrywają więcej wydarzeń'] as $item) { $list[]=$text('li',$item); }
            $ul=$container('', $list); $ul['attrs']['tag']='ul';
            $block['innerBlocks'][]=$container('tsu-faction-stat',[$text('h3','CECHY SPECJALNE'),$ul]);
            $block['innerHTML']=''; $block['innerContent']=array_fill(0,count($block['innerBlocks']),null);
        }
    }
    return $blocks;
};
$blocks=$walk(parse_blocks($page->post_content));
if ($found!==1) { WP_CLI::error('Nieoczekiwana struktura strony.'); }
update_post_meta($page->ID,'_wtf_wum_before_description',$page->post_content);
$result=wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
update_post_meta($page->ID,'_wtf_wum_description_restored',7973);
WP_CLI::success('Uzupełniono fabułę, taktykę, cechy i tryb 2 graczy, ID '.$page->ID.'.');
