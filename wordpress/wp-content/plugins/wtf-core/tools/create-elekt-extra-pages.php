<?php
// Ręczne przeniesienie istniejących opisów; oryginalne ścieżki potwierdzone w kopii.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
wp_set_current_user(1);
$main=get_post(415);
if (!$main || get_post_meta(415,'_wtf_ele_key',true)!=='main:pl') { WP_CLI::error('Brak Elekta PL.'); }
$definitions=['intrygi'=>'Intrygi','minstrel'=>'Minstrel'];
$cards=[];
$find=function($blocks) use (&$find,&$cards,$definitions) {
    foreach($blocks as $block) {
        $id=$block['attrs']['attributes']['id']??'';
        foreach($definitions as $slug=>$title) { if($id==='dodatek-'.$slug) { $cards[$slug]=$block; } }
        if($block['innerBlocks']) { $find($block['innerBlocks']); }
    }
};
$original=parse_blocks($main->post_content); $find($original);
if(count($cards)!==2) { WP_CLI::error('Brak obu kart.'); }
$make=static function($name,$attrs,$children=[]) { return ['blockName'=>$name,'attrs'=>$attrs,'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)]; };
$text=static fn($tag,$content,$attributes=[])=>$make('wtf/ele-text',['tag'=>$tag,'content'=>$content,'attributes'=>$attributes]);
$unfold=function($blocks) use (&$unfold) {
    $result=[];
    foreach($blocks as $block) {
        if(($block['attrs']['tag']??'')==='summary') { continue; }
        if(($block['attrs']['tag']??'')==='details' && ($block['attrs']['attributes']['class']??'')==='expansion-full-description') {
            $block['attrs']['tag']='div';
            $block['attrs']['attributes']['class']='expansion-page-description';
        }
        if($block['innerBlocks']) {
            $block['innerBlocks']=$unfold($block['innerBlocks']);
            if($block['blockName']==='wtf/ele-container') { $block['innerHTML']='';$block['innerContent']=array_fill(0,count($block['innerBlocks']),null); }
        }
        $result[]=$block;
    }
    return $result;
};
$links=[];
foreach($definitions as $slug=>$title) {
    $pair=[];
    foreach(['pl','en'] as $lang) {
        $key=$slug.':'.$lang;
        $existing=get_posts(['post_type'=>'page','post_status'=>['publish','draft','private','pending'],'numberposts'=>1,'meta_key'=>'_wtf_ele_key','meta_value'=>$key,'suppress_filters'=>true]);
        if($existing) { $id=$existing[0]->ID; }
        else {
            $parent=$lang==='pl'?415:416;
            $path=$lang==='pl'?'elekt/'.$slug:'elekt-en/'.$slug;
            if(get_page_by_path($path)) { WP_CLI::error('Adres zajęty: '.$path); }
            $card=$unfold([$cards[$slug]])[0];
            $back=$lang==='pl'?home_url('/elekt/#dodatek-'.$slug):get_permalink(416).'#dodatek-'.$slug;
            $content=serialize_blocks([$make('wtf/ele-container',['tag'=>'main','attributes'=>['id'=>'main','class'=>'elekt-world elekt-expansion-page wrap'],'label'=>$title],[
                $text('a',$lang==='pl'?'Wróć do Elekta':'Back to Elekt',['class'=>'elekt-expansion-back','href'=>$back]),
                $text('h1',$title),$card,
            ])]);
            $id=wp_insert_post(wp_slash(['post_type'=>'page','post_title'=>$title,'post_name'=>$slug,'post_parent'=>$parent,'post_status'=>$lang==='pl'?'publish':'draft','post_content'=>$content]),true);
            if(is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
            update_post_meta($id,'_wtf_ele_key',$key);
            update_post_meta($id,'_wtf_ele_kind','expansion');
            update_post_meta($id,'_wp_page_template','elekt');
            pll_set_post_language($id,$lang);
        }
        $pair[$lang]=$id;
    }
    pll_save_post_translations($pair);
    if(get_post_status($pair['pl'])!=='publish') { WP_CLI::error('Podstrona PL nie jest opublikowana.'); }
    $links[$slug]=get_permalink($pair['pl']);
    WP_CLI::log($title.': PL '.$pair['pl'].', EN '.$pair['en'].'; '.$links[$slug]);
}
$shorten=function($blocks,$slug=null) use (&$shorten,$links,$text) {
    $result=[];
    foreach($blocks as $block) {
        $local=$slug;
        foreach($links as $key=>$url) { if(($block['attrs']['attributes']['id']??'')==='dodatek-'.$key) { $local=$key; } }
        $class=$block['attrs']['attributes']['class']??'';
        if($local && ($class==='expansion-full-description' || $class==='expansion-card-count')) { continue; }
        if($block['innerBlocks']) { $block['innerBlocks']=$shorten($block['innerBlocks'],$local); }
        if($local && $class==='expansion-actions' && strpos(serialize_blocks($block['innerBlocks']),'expansion-page-link')===false) {
            $block['innerBlocks'][]=$text('p','<a class="expansion-page-link" href="'.esc_url($links[$local]).'">Strona dodatku</a>');
        }
        if($block['blockName']==='wtf/ele-container') { $block['innerHTML']=''; $block['innerContent']=array_fill(0,count($block['innerBlocks']),null); }
        $result[]=$block;
    }
    return $result;
};
if(!metadata_exists('post',415,'_wtf_before_extra_expansion_pages')) { update_post_meta(415,'_wtf_before_extra_expansion_pages',$main->post_content); }
$saved=wp_update_post(['ID'=>415,'post_content'=>wp_slash(serialize_blocks($shorten($original)))],true);
if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
WP_CLI::success('Podstrony gotowe; lista dodatków z zajawkami i linkami.');
