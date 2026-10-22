<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(419);$main=get_post(415);
if(!$page || get_post_meta(419,'_wtf_ele_key',true)!=='rubiez:pl') { WP_CLI::error('Brak Rubieży.'); }
if(get_post_meta(419,'_wtf_expansion_detail_v1',true)) {
    $update=function($blocks) use (&$update) {
        foreach($blocks as &$block) {
            if($block['innerBlocks'])$block['innerBlocks']=$update($block['innerBlocks']);
            if(($block['attrs']['attributes']['class']??'')!=='elekt-addon-facts')continue;
            $found=false;
            foreach($block['innerBlocks'] as &$fact) {
                if(($fact['innerBlocks'][0]['attrs']['content']??'')==='Ilustracje') { $fact['innerBlocks'][1]['attrs']['content']='Justyna Szumczyk i Katarzyna Malinowska';$found=true; }
            }unset($fact);
            if(!$found) {
                $children=[];foreach(['dt'=>'Ilustracje','dd'=>'Justyna Szumczyk i Katarzyna Malinowska'] as $tag=>$content) { $children[]=['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]]; }
                $block['innerBlocks'][]=['blockName'=>'wtf/ele-container','attrs'=>['tag'=>'div','attributes'=>['class'=>'elekt-addon-fact'],'label'=>'Ilustracje'],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>[null,null]];
                $block['innerHTML']='';$block['innerContent']=array_fill(0,count($block['innerBlocks']),null);
            }
        }return $blocks;
    };
    $saved=wp_update_post(['ID'=>419,'post_content'=>wp_slash(serialize_blocks($update(parse_blocks($page->post_content))))],true);
    if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
    WP_CLI::success('Dopisano obie ilustratorki.');return;
}
$find=function($blocks,$predicate) use (&$find) {
    foreach($blocks as $block) { if($predicate($block))return $block;if($block['innerBlocks']) { $found=$find($block['innerBlocks'],$predicate);if($found)return $found; } }return null;
};
$old=parse_blocks($page->post_content);
$card=$find(parse_blocks($main->post_content),static fn($b)=>($b['attrs']['attributes']['id']??'')==='dodatek-rubiez');
$cover=$find([$card],static fn($b)=>$b['blockName']==='wtf/ele-image');
$brief=$find([$card],static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
$buy=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-buy');
$imported=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
if(!$cover||!$brief||!$buy||!$imported) { WP_CLI::error('Brak materiałów.'); }
$term=get_term_by('slug','dodatek-rubiez','wtf_faq_group');
if(!$term || (int)$term->count!==3) { WP_CLI::error('Brak 3 pytań w grupie FAQ.'); }
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content,$attributes=[])=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content,'attributes'=>$attributes],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cover['attrs']['attributes']=['loading'=>'eager','fetchpriority'=>'high'];
$facts=[];$credits=['Liczba kart'=>'22','Autor'=>'Krzysztof Głośnicki','Ilustracje'=>'Justyna Szumczyk i Katarzyna Malinowska'];
foreach($credits as $label=>$value) { $facts[]=$make('div','elekt-addon-fact',[$text('dt',$label),$text('dd',$value)],$label); }
$hero=$make('header','elekt-addon-hero',[
    $make('div','elekt-addon-cover',[$cover],'Okładka dodatku'),
    $make('div','elekt-addon-intro',[
        $text('span','DODATEK DO GRY ELEKT'),$text('h1','Rubież'),$brief,
        $make('dl','elekt-addon-facts',$facts,'Informacje o dodatku'),
        $make('div','elekt-addon-actions',[$buy],'Zakup dodatku'),
    ],'Rubież — najważniejsze informacje'),
],'Wprowadzenie');
$lore=[];
foreach($imported['innerBlocks'] as $block) {
    if(($block['attrs']['tag']??'')==='blockquote') {
        foreach($block['innerBlocks'] as &$paragraph) { if(isset($paragraph['attrs']['content']))$paragraph['attrs']['content']=str_replace('i i mrocznych','i mrocznych',$paragraph['attrs']['content']); }unset($paragraph);
        $lore[]=$block;
    }
}
if(!$lore) { WP_CLI::error('Brak opisu fabularnego.'); }
$list=[];foreach(['10 kart Misji','10 kart Wpływów','2 karty Przywilejów'] as $item) { $list[]=$text('li',$item); }
$shortcode='[wtf_faq game="elekt" groups="dodatek-rubiez"]';
$sections=[
    $make('section','elekt-addon-section',[
        $make('div','elekt-addon-section-heading',[$text('h2','Ze świata Elekta')],'Świat gry'),
        $make('div','elekt-addon-section-copy elekt-imported',$lore,'Opis fabularny'),
    ],'Dodatkowy opis'),
    $make('section','elekt-addon-section',[
        $make('div','elekt-addon-section-heading',[$text('h2','W pudełku')],'Zawartość dodatku'),
        $make('div','elekt-addon-section-copy elekt-imported',[
            $text('p','Dodatek zawiera 22 karty przedstawiające miejsca i wydarzenia ze świata Rubieży:'),
            $make('ul','elekt-addon-contents',$list,'Lista kart'),
        ],'Zawartość pudełka'),
    ],'Zawartość'),
    $make('section','elekt-addon-section',[
        $make('div','elekt-addon-section-heading',[$text('h2','Najczęściej zadawane pytania')],'FAQ'),
        $make('div','elekt-addon-section-copy',[
            ['blockName'=>'core/shortcode','attrs'=>[],'innerBlocks'=>[],'innerHTML'=>$shortcode,'innerContent'=>[$shortcode]],
        ],'Pytania z WTF FAQ'),
    ],'FAQ Rubież'),
];
$root=$make('main','elekt-world elekt-expansion-page elekt-addon-page wrap',array_merge([
    $text('a','Poznaj grę Elekt',['class'=>'elekt-expansion-back','href'=>home_url('/elekt/')]),$hero,
],$sections),'Rubież');
$root['attrs']['attributes']['id']='main';
update_post_meta(419,'_wtf_before_expansion_detail_v1',$page->post_content);
$saved=wp_update_post(['ID'=>419,'post_content'=>wp_slash(serialize_blocks([$root]))],true);
if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
update_post_meta(419,'_wtf_expansion_detail_v1',true);
WP_CLI::success('Rubież przebudowana; FAQ obsługiwane przez wtyczkę.');
