<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(925);$main=get_post(415);
if(!$page || get_post_meta(925,'_wtf_ele_key',true)!=='intrygi:pl')WP_CLI::error('Brak Intryg.');
if(get_post_meta(925,'_wtf_expansion_detail_v1',true)){WP_CLI::success('Układ już zapisany.');return;}
$find=function($blocks,$predicate)use(&$find){foreach($blocks as $b){if($predicate($b))return $b;if($b['innerBlocks']){$r=$find($b['innerBlocks'],$predicate);if($r)return $r;}}return null;};
$mainBlocks=parse_blocks($main->post_content);$old=parse_blocks($page->post_content);
$card=$find($mainBlocks,static fn($b)=>($b['attrs']['attributes']['id']??'')==='dodatek-intrygi');
$cover=$find([$card],static fn($b)=>$b['blockName']==='wtf/ele-image');
$brief=$find([$card],static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
$full=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-page-description');
$copy=$find([$full],static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
$gallery=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-description-gallery');
if(!$cover||!$brief||!$copy||!$gallery)WP_CLI::error('Niepełne materiały.');
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content,$attributes=[])=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content,'attributes'=>$attributes],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cover['attrs']['attributes']=['loading'=>'eager','fetchpriority'=>'high'];
$facts=[];foreach(['Liczba kart'=>'24','Autor'=>'Krzysztof Głośnicki','Ilustracje'=>'Katarzyna Malinowska'] as $label=>$value)$facts[]=$make('div','elekt-addon-fact',[$text('dt',$label),$text('dd',$value)],$label);
$hero=$make('header','elekt-addon-hero',[
 $make('div','elekt-addon-cover',[$cover],'Okładka dodatku'),
 $make('div','elekt-addon-intro',[$text('span','DODATEK DO GRY ELEKT'),$text('h1','Intrygi'),$brief,$make('dl','elekt-addon-facts',$facts,'Informacje o dodatku')],'Intrygi — najważniejsze informacje'),
],'Wprowadzenie');
$rules=[];$contents=[];$listing=false;$current=null;
foreach($copy['innerBlocks'] as $b){
 $content=$b['attrs']['content']??'';
 if($content==='Lista kart'){$listing=true;continue;}
 if($listing){$contents[]=$b;continue;}
 if(preg_match('~^<strong>(.*?)</strong><br\s*/?>\s*(.*)$~su',$content,$m)){
  $heading=$m[1];$b['attrs']['content']=str_replace('staczy Żaru','starczy Żaru',$m[2]);
  $rules[]=$make('section','elekt-addon-rule-step',[$text('h3',$heading),$b],$heading);$current=count($rules)-1;
 }else{
  if($current===null)WP_CLI::error('Akapit bez sekcji.');
  $rules[$current]['innerBlocks'][]=$b;$rules[$current]['innerContent'][]=null;
 }
}
if(count($rules)!==6)WP_CLI::error('Oczekiwano sześciu sekcji zasad.');
$art=[];
foreach($gallery['innerBlocks'] as $item){
 $image=$find([$item],static fn($b)=>$b['blockName']==='wtf/ele-image');
 if(!$image||!in_array($image['attrs']['imageId']??0,[443,444],true))continue;
 $name=$image['attrs']['imageId']===443?'Krwawiące Serce':'Czarny Rynek';
 $image['attrs']['alt']='Karta '.$name.' z dodatku Intrygi do gry Elekt.';
 $art[]=$make('figure','elekt-addon-card',[$image,$text('figcaption',$name)],$name);
}
if(count($art)!==2)WP_CLI::error('Brak dwóch grafik kart.');
$root=$make('main','elekt-world elekt-expansion-page elekt-addon-page elekt-addon-intrygi wrap',[
 $text('a','Poznaj grę Elekt',['class'=>'elekt-expansion-back','href'=>home_url('/elekt/')]),$hero,
 $make('section','elekt-addon-section',[$make('div','elekt-addon-section-heading',[$text('h2','W pudełku')],'Zawartość'),$make('div','elekt-addon-section-copy elekt-imported',array_merge([$text('p','Dodatek zawiera 24 karty:')],$contents),'Lista kart')],'Zawartość dodatku'),
 $make('section','elekt-addon-section',[$make('div','elekt-addon-section-heading',[$text('h2','Jak grać z Intrygami')],'Zasady'),$make('div','elekt-addon-section-copy elekt-imported elekt-addon-rules',$rules,'Zasady Intryg')],'Zasady dodatku'),
 $make('section','elekt-addon-art',[$text('h2','Poznaj karty'),$make('div','expansion-description-gallery',$art,'Przykładowe karty Intryg')],'Galeria kart'),
],'Intrygi');
$root['attrs']['attributes']['id']='main';
update_post_meta(925,'_wtf_before_expansion_detail_v1',$page->post_content);
$saved=wp_update_post(['ID'=>925,'post_content'=>wp_slash(serialize_blocks([$root]))],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
$recolor=function($blocks)use(&$recolor){foreach($blocks as &$b){if(($b['attrs']['attributes']['id']??'')==='dodatek-intrygi')$b['attrs']['attributes']['style']='--expansion-color:#a8b49b';if($b['innerBlocks'])$b['innerBlocks']=$recolor($b['innerBlocks']);}return $blocks;};
update_post_meta(415,'_wtf_before_intrygi_recolor',$main->post_content);
$saved=wp_update_post(['ID'=>415,'post_content'=>wp_slash(serialize_blocks($recolor($mainBlocks)))],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(925,'_wtf_expansion_detail_v1',true);
WP_CLI::success('Intrygi sformatowane; szarozielony akcent na podstronie i liście.');
