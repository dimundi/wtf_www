<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$pages=get_posts(['post_type'=>'page','post_status'=>'publish','numberposts'=>-1,'meta_query'=>[['key'=>'_wtf_ele_key','value'=>':pl','compare'=>'LIKE']],'suppress_filters'=>true]);
$container=static function($class,$children) { return ['blockName'=>'wtf/ele-container','attrs'=>['tag'=>'div','attributes'=>['class'=>$class]],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)]; };
$changed=0;
foreach($pages as $page) {
    $main=get_post_meta($page->ID,'_wtf_ele_key',true)==='main:pl';
    $count=0;
    $walk=function($blocks) use (&$walk,&$count,$main,$container) {
        foreach($blocks as &$block) {
            if (($block['attrs']['attributes']['class']??'')==='expansion-card-count' && ($block['attrs']['tag']??'')==='p' && preg_match('/^Zawartość dodatku: (\d+) kart\.$/u',$block['attrs']['content']??'',$match)) {
                $block['attrs']['content']='Liczba kart: '.$match[1].'.'; $count++;
            }
            if($block['innerBlocks']) { $block['innerBlocks']=$walk($block['innerBlocks']); }
            if(($block['attrs']['attributes']['class']??'')!=='elekt-expansion') { continue; }
            $visual=null; $description=null;
            foreach($block['innerBlocks'] as $i=>$child) {
                $class=$child['attrs']['attributes']['class']??'';
                if($class==='expansion-visual') { $visual=$i; }
                if($class==='expansion-description') { $description=$i; }
            }
            if($visual===null || $description===null) { WP_CLI::error('Brak grafiki lub opisu.'); }
            $buy=null; $cards=null; $art=[];
            foreach($block['innerBlocks'][$visual]['innerBlocks'] as $child) {
                $class=$child['attrs']['attributes']['class']??'';
                if($class==='expansion-buy') { $buy=$child; }
                elseif($class==='expansion-card-count') { $cards=$child; }
                else { $art[]=$child; }
            }
            if(!$buy && !$cards) { continue; }
            $block['innerBlocks'][$visual]['innerBlocks']=$art;
            $block['innerBlocks'][$visual]['innerHTML']='';
            $block['innerBlocks'][$visual]['innerContent']=array_fill(0,count($art),null);
            $copy=$block['innerBlocks'][$description]['innerBlocks'];
            $hasPage=false; $links=[]; $content=[];
            foreach($copy as $child) {
                if(strpos($child['attrs']['content']??'','expansion-page-link')!==false) { $links[]=$child; $hasPage=true; }
                else { $content[]=$child; }
            }
            if($cards && (!$main || !$hasPage)) {
                $cards['attrs']['tag']='p';
                $cards['attrs']['content']='Liczba kart: '.(int)$cards['attrs']['content'].'.';
                array_splice($content,1,0,[$cards]);
            }
            if($buy || $links) { $content[]=$container('expansion-actions',array_merge($buy?[$buy]:[],$links)); }
            $block['innerBlocks'][$description]['innerBlocks']=$content;
            $block['innerBlocks'][$description]['innerHTML']='';
            $block['innerBlocks'][$description]['innerContent']=array_fill(0,count($content),null);
            $count++;
        }
        return $blocks;
    };
    $updated=serialize_blocks($walk(parse_blocks($page->post_content)));
    if(!$count) { continue; }
    if(!metadata_exists('post',$page->ID,'_wtf_before_expansion_layout_v2')) { update_post_meta($page->ID,'_wtf_before_expansion_layout_v2',$page->post_content); }
    $result=wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash($updated)],true);
    if(is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
    update_post_meta($page->ID,'_wtf_expansion_layout_v2',true);
    $changed+=$count;
}
WP_CLI::success('Zmieniono układ '.$changed.' kart dodatków na stronach PL.');
