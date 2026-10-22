<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(421);$main=get_post(415);
if(!$page || get_post_meta(421,'_wtf_ele_key',true)!=='wiedzmy:pl') { WP_CLI::error('Brak Wiedźm.'); }
if(get_post_meta(421,'_wtf_expansion_detail_v1',true)) { WP_CLI::success('Układ już zapisany.');return; }
$find=function($blocks,$predicate) use (&$find) {
    foreach($blocks as $block) { if($predicate($block))return $block;if($block['innerBlocks']) { $found=$find($block['innerBlocks'],$predicate);if($found)return $found; } }return null;
};
$old=parse_blocks($page->post_content);
$card=$find(parse_blocks($main->post_content),static fn($b)=>($b['attrs']['attributes']['id']??'')==='dodatek-wiedzmy');
$cover=$find([$card],static fn($b)=>$b['blockName']==='wtf/ele-image');
$buy=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-buy');
$imported=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
$gallery=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-description-gallery');
if(!$cover||!$buy||!$imported||!$gallery) { WP_CLI::error('Brak materiałów.'); }
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content,$attributes=[])=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content,'attributes'=>$attributes],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cover['attrs']['attributes']=['loading'=>'eager','fetchpriority'=>'high'];
$brief=[];$lore=[];$contents=[];
foreach($imported['innerBlocks'] as $block) {
    $tag=$block['attrs']['tag']??'';$content=$block['attrs']['content']??'';
    if($tag==='blockquote')$lore[]=$block;
    elseif($tag==='ul')$contents[]=$block;
    elseif(strpos($content,'Poznaj wiedźmy')!==false)$brief[]=$block;
}
if(!$brief||!$lore||!$contents) { WP_CLI::error('Niepełny opis.'); }
$facts=[];
foreach(['Liczba kart'=>'16','Autor'=>'Krzysztof Głośnicki','Ilustracje'=>'Katarzyna Malinowska'] as $label=>$value) {
    $facts[]=$make('div','elekt-addon-fact',[$text('dt',$label),$text('dd',$value)],$label);
}
$hero=$make('header','elekt-addon-hero',[
    $make('div','elekt-addon-cover',[$cover],'Okładka dodatku'),
    $make('div','elekt-addon-intro',array_merge([
        $text('span','DODATEK DO GRY ELEKT'),$text('h1','Wiedźmy'),
        $make('div','elekt-imported',$brief,'Krótki opis'),
        $make('dl','elekt-addon-facts',$facts,'Informacje o dodatku'),
        $make('div','elekt-addon-actions',[$buy],'Zakup dodatku'),
    ]),'Wiedźmy — najważniejsze informacje'),
],'Wprowadzenie');
foreach($gallery['innerBlocks'] as &$item) {
    foreach($item['innerBlocks'] as &$image) { if($image['blockName']==='wtf/ele-image')$image['attrs']['alt']='Pudełko dodatku Wiedźmy do gry Elekt — ilustracja Katarzyny Malinowskiej.'; }unset($image);
}unset($item);
$sections=[
    $make('section','elekt-addon-section',[
        $make('div','elekt-addon-section-heading',[$text('h2','Ze świata Elekta')],'Świat gry'),
        $make('div','elekt-addon-section-copy elekt-imported',$lore,'Opis fabularny'),
    ],'Dodatkowy opis'),
    $make('section','elekt-addon-section',[
        $make('div','elekt-addon-section-heading',[$text('h2','W pudełku')],'Zawartość dodatku'),
        $make('div','elekt-addon-section-copy elekt-imported',array_merge([$text('p','Dodatek zawiera 16 kart:')],$contents),'Zawartość pudełka'),
    ],'Zawartość'),
    $make('section','elekt-addon-art',[$text('h2','Przyjrzyj się dodatkowi'),$gallery],'Grafiki dodatku'),
];
$root=$make('main','elekt-world elekt-expansion-page elekt-addon-page wrap',array_merge([
    $text('a','Poznaj grę Elekt',['class'=>'elekt-expansion-back','href'=>home_url('/elekt/')]),$hero,
],$sections),'Wiedźmy');
$root['attrs']['attributes']['id']='main';
update_post_meta(421,'_wtf_before_expansion_detail_v1',$page->post_content);
$saved=wp_update_post(['ID'=>421,'post_content'=>wp_slash(serialize_blocks([$root]))],true);
if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
update_post_meta(421,'_wtf_expansion_detail_v1',true);
WP_CLI::success('Wiedźmy przebudowane, informacja o przedsprzedaży usunięta.');
