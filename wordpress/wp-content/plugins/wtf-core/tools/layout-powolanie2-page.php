<?php
// Pierwszy wzór podstrony dodatku. Wyłącznie Powołanie 2 PL.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(417);$main=get_post(415);
if(!$page || get_post_meta(417,'_wtf_ele_key',true)!=='powolanie2:pl') { WP_CLI::error('Brak strony.'); }
if(get_post_meta(417,'_wtf_expansion_detail_v1',true)) {
    $corrected=str_replace('Pięć zestawów Jednostek Podstawowych — po jednym dla każdego gracza. Łącznie 20 kart:','Dodatek zawiera 20 kart Jednostek Podstawowych. Znajdziesz w nim pięć rodzajów jednostek:',$page->post_content);
    if($corrected!==$page->post_content) { wp_update_post(['ID'=>417,'post_content'=>wp_slash($corrected)]); }
    WP_CLI::success('Układ już zapisany.');return;
}
$find=function($blocks,$predicate) use (&$find) {
    foreach($blocks as $block) { if($predicate($block)) { return $block; } if($block['innerBlocks']) { $found=$find($block['innerBlocks'],$predicate);if($found) { return $found; } } }
    return null;
};
$old=parse_blocks($page->post_content);$list=parse_blocks($main->post_content);
$card=$find($list,static fn($b)=>($b['attrs']['attributes']['id']??'')==='dodatek-powolanie2');
if(!$card) { WP_CLI::error('Brak karty na liście.'); }
$cover=$find([$card],static fn($b)=>$b['blockName']==='wtf/ele-image');
$brief=$find([$card],static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
$buy=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-buy');
$gallery=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-description-gallery');
$imported=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
$back=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-expansion-back');
if(!$cover||!$brief||!$buy||!$gallery||!$imported||!$back) { WP_CLI::error('Niepełne materiały źródłowe.'); }
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content)=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cover['attrs']['attributes']=['loading'=>'eager','fetchpriority'=>'high'];
$facts=[];
foreach(['Liczba kart'=>'20','Autor'=>'Krzysztof Głośnicki','Ilustracje'=>'Justyna Szumczyk'] as $label=>$value) {
    $facts[]=$make('div','elekt-addon-fact',[$text('dt',$label),$text('dd',$value)],$label);
}
$hero=$make('header','elekt-addon-hero',[
    $make('div','elekt-addon-cover',[$cover],'Okładka dodatku'),
    $make('div','elekt-addon-intro',[
        $text('span','DODATEK DO GRY ELEKT'),$text('h1','Powołanie 2'),$brief,
        $make('dl','elekt-addon-facts',$facts,'Informacje o dodatku'),
        $make('div','elekt-addon-actions',[$buy],'Zakup dodatku'),
    ],'Powołanie 2 — najważniejsze informacje'),
],'Wprowadzenie');
$lore=[];$contents=[];
foreach($imported['innerBlocks'] as $block) {
    $content=$block['attrs']['content']??'';
    if(strpos($content,'Autorką ilustracji jest')!==false || strpos($content,'Kolejna seria Jednostek')!==false || strpos($content,'Lista kart:')!==false) { continue; }
    if(($block['attrs']['tag']??'')==='ul') { $contents[]=$block; } else { $lore[]=$block; }
}
foreach($gallery['innerBlocks'] as &$item) {
    foreach($item['innerBlocks'] as &$image) {
        if($image['blockName']==='wtf/ele-image') { $image['attrs']['alt']=($image['attrs']['imageId']??0)===433?'Technomantka — karta z dodatku Powołanie 2 do Elekta':'Powołanie 2 — ilustracja pudełka dodatku'; }
    }unset($image);
}unset($item);
$sections=[
    $make('section','elekt-addon-section',[
        $make('div','elekt-addon-section-heading',[$text('h2','W pudełku')],'Zawartość dodatku'),
        $make('div','elekt-addon-section-copy elekt-imported',array_merge([$text('p','Dodatek zawiera 20 kart Jednostek Podstawowych. Znajdziesz w nim pięć rodzajów jednostek:')],$contents),'Lista kart'),
    ],'Zawartość'),
    $make('section','elekt-addon-section',[
        $make('div','elekt-addon-section-heading',[$text('h2','Ze świata Elekta')],'Świat gry'),
        $make('div','elekt-addon-section-copy elekt-imported',$lore,'Opis fabularny'),
    ],'Dodatkowy opis'),
    $make('section','elekt-addon-art',[$text('h2','Przyjrzyj się dodatkowi'),$gallery],'Grafiki dodatku'),
];
$root=$make('main','elekt-world elekt-expansion-page elekt-addon-page wrap',array_merge([$back,$hero],$sections),'Powołanie 2');
$root['attrs']['attributes']['id']='main';
update_post_meta(417,'_wtf_before_expansion_detail_v1',$page->post_content);
$saved=wp_update_post(['ID'=>417,'post_content'=>wp_slash(serialize_blocks([$root]))],true);
if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
update_post_meta(417,'_wtf_expansion_detail_v1',true);
WP_CLI::success('Zapisano wzór podstrony Powołanie 2.');
