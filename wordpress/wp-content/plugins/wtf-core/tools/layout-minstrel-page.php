<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(927);$main=get_post(415);
if(!$page || get_post_meta(927,'_wtf_ele_key',true)!=='minstrel:pl')WP_CLI::error('Brak Minstrela.');
if(get_post_meta(927,'_wtf_expansion_detail_v1',true)){WP_CLI::success('Układ już zapisany.');return;}
$find=function($blocks,$predicate)use(&$find){foreach($blocks as $b){if($predicate($b))return $b;if($b['innerBlocks']){$r=$find($b['innerBlocks'],$predicate);if($r)return $r;}}return null;};
$old=parse_blocks($page->post_content);
$card=$find(parse_blocks($main->post_content),static fn($b)=>($b['attrs']['attributes']['id']??'')==='dodatek-minstrel');
$cover=$find([$card],static fn($b)=>$b['blockName']==='wtf/ele-image');
$brief=$find([$card],static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
$full=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-page-description');
$copy=$find([$full],static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
$buy=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-buy');
$gallery=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-description-gallery');
if(!$cover||!$brief||!$copy||!$buy||!$gallery)WP_CLI::error('Niepełne materiały.');
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content,$attributes=[])=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content,'attributes'=>$attributes],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cover['attrs']['attributes']=['loading'=>'eager','fetchpriority'=>'high'];
$facts=[];$credits=['Liczba kart'=>'16','Autor'=>'Krzysztof Głośnicki'];
if(!empty($args[0]))$credits['Ilustracje']=$args[0];
foreach($credits as $label=>$value)$facts[]=$make('div','elekt-addon-fact',[$text('dt',$label),$text('dd',$value)],$label);
$availability=[];$lore=[];$rules=[];$headings=['Przygotowanie gry','Karty Melodii','Zagrywanie Melodii','Gdy Minstrel przestaje grać','Pieśń Zwycięstwa'];
foreach($copy['innerBlocks'] as $b){
 $content=$b['attrs']['content']??'';
 if(($b['attrs']['tag']??'')==='blockquote'){$lore[]=$b;continue;}
 if(str_contains($content,'Dodatek dostępny tylko')){$availability[]=$b;continue;}
 if(str_contains($content,'Ty również jej pożądasz')){$lore[]=$b;continue;}
 if(wp_strip_all_tags($content)==='Przykład ułożonej Pieśni Zwycięstwa')continue;
 if(!trim(wp_strip_all_tags($content)))continue;
 $content=str_replace(['Podsta-wowych','Wygranie Pieśni'],['Podstawowych','wygranie Pieśni'],$content);
 $b['attrs']['content']=$content;
 $heading=$headings[count($rules)]??null;if(!$heading)WP_CLI::error('Nadmiar akapitów zasad: '.wp_strip_all_tags($content));
 $rules[]=$make('section','elekt-addon-rule-step',[$text('h3',$heading),$b],$heading);
}
if(count($rules)!==5)WP_CLI::error('Oczekiwano 5 sekcji zasad.');
$hero=$make('header','elekt-addon-hero',[
 $make('div','elekt-addon-cover',[$cover],'Okładka dodatku'),
 $make('div','elekt-addon-intro',array_merge([$text('span','DODATEK DO GRY ELEKT'),$text('h1','Minstrel'),$brief],[$make('div','elekt-imported',$availability,'Dostępność z albumem'),$make('dl','elekt-addon-facts',$facts,'Informacje o dodatku'),$make('div','elekt-addon-actions',[$buy],'Zakup dodatku')]),'Minstrel — najważniejsze informacje'),
],'Wprowadzenie');
$art=[];
foreach($gallery['innerBlocks'] as $item){
 $image=$find([$item],static fn($b)=>$b['blockName']==='wtf/ele-image');if(!$image)continue;
 $example=($image['attrs']['imageId']??0)===449;
 $image['attrs']['alt']=$example?'Przykład ułożonej Pieśni Zwycięstwa z dodatku Minstrel do Elekta.':'Minstrel — ilustracja dodatku do gry Elekt.';
 $art[]=$make('figure','elekt-addon-card',[$image,$text('figcaption',$example?'Przykład ułożonej Pieśni Zwycięstwa':'Minstrel')],'Grafika dodatku');
}
$root=$make('main','elekt-world elekt-expansion-page elekt-addon-page elekt-addon-minstrel wrap',[
 $text('a','Poznaj grę Elekt',['class'=>'elekt-expansion-back','href'=>home_url('/elekt/')]),$hero,
 $make('section','elekt-addon-section',[$make('div','elekt-addon-section-heading',[$text('h2','Ze świata Elekta')],'Świat gry'),$make('div','elekt-addon-section-copy elekt-imported',$lore,'Opis fabularny')],'Dodatkowy opis'),
 $make('section','elekt-addon-section',[$make('div','elekt-addon-section-heading',[$text('h2','W pudełku')],'Zawartość'),$make('div','elekt-addon-section-copy elekt-imported',[$text('p','16 kart: Jednostki Podstawowe Minstrel oraz cztery zestawy Melodii. Każdy zestaw Melodii zawiera po jednej kopii każdego ich rodzaju.')],'Zawartość pudełka')],'Zawartość'),
 $make('section','elekt-addon-section',[$make('div','elekt-addon-section-heading',[$text('h2','Jak grać z Minstrelem')],'Zasady'),$make('div','elekt-addon-section-copy elekt-imported elekt-addon-rules',$rules,'Zasady Minstrela')],'Zasady dodatku'),
 $make('section','elekt-addon-art',[$text('h2','Przyjrzyj się kartom'),$make('div','expansion-description-gallery',$art,'Grafiki Minstrela')],'Galeria kart'),
],'Minstrel');
$root['attrs']['attributes']['id']='main';
update_post_meta(927,'_wtf_before_expansion_detail_v1',$page->post_content);
$saved=wp_update_post(['ID'=>927,'post_content'=>wp_slash(serialize_blocks([$root]))],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(927,'_wtf_expansion_detail_v1',true);
WP_CLI::success('Minstrel sformatowany według wzorca.');
