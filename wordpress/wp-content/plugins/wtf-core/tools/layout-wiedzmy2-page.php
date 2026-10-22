<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(423);$main=get_post(415);
if(!$page || get_post_meta(423,'_wtf_ele_key',true)!=='wiedzmy2:pl')WP_CLI::error('Brak Wiedźm 2.');
if(get_post_meta(423,'_wtf_expansion_detail_v1',true)){WP_CLI::success('Układ już zapisany.');return;}
$find=function($blocks,$predicate)use(&$find){foreach($blocks as $b){if($predicate($b))return $b;if($b['innerBlocks']){$r=$find($b['innerBlocks'],$predicate);if($r)return $r;}}return null;};
$old=parse_blocks($page->post_content);
$card=$find(parse_blocks($main->post_content),static fn($b)=>($b['attrs']['attributes']['id']??'')==='dodatek-wiedzmy2');
$cover=$find([$card],static fn($b)=>$b['blockName']==='wtf/ele-image');
$buy=$find($old,static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-buy');
$art=$find($old,static fn($b)=>($b['attrs']['imageId']??0)===935);
$faq=$find($old,static fn($b)=>$b['blockName']==='core/shortcode'&&str_contains($b['innerHTML'],'dodatek-wiedzmy-2'));
$wallpaper=$find($old,static fn($b)=>($b['attrs']['label']??'')==='Tapety Wiedźmy 2');
if(!$cover||!$buy||!$art||!$faq||!$wallpaper)WP_CLI::error('Niepełne materiały.');
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content,$attributes=[])=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content,'attributes'=>$attributes],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cover['attrs']['attributes']=['loading'=>'eager','fetchpriority'=>'high'];
$facts=[];foreach(['Liczba kart'=>'22','Autor'=>'Krzysztof Głośnicki','Ilustracje'=>'Justyna Szumczyk'] as $label=>$value)$facts[]=$make('div','elekt-addon-fact',[$text('dt',$label),$text('dd',$value)],$label);
$hero=$make('header','elekt-addon-hero',[
 $make('div','elekt-addon-cover',[$cover],'Okładka dodatku'),
 $make('div','elekt-addon-intro',[
  $text('span','DODATEK DO GRY ELEKT'),$text('h1','Wiedźmy 2'),
  $make('div','elekt-imported',[$text('p','Potężne czarodziejki, zaklinaczki demonów i mistyczne wojowniczki powracają. Kontynuacja dodatku Wiedźmy przynosi 22 karty z klimatycznymi ilustracjami i zaskakującymi zdolnościami — nowe możliwości dla twojej strategii.')],'Krótki opis'),
  $make('dl','elekt-addon-facts',$facts,'Informacje o dodatku'),$make('div','elekt-addon-actions',[$buy],'Zakup dodatku'),
 ],'Wiedźmy 2 — najważniejsze informacje'),
],'Wprowadzenie');
$lore=$make('section','elekt-addon-section',[
 $make('div','elekt-addon-section-heading',[$text('h2','Ze świata Elekta')],'Świat gry'),
 $make('div','elekt-addon-section-copy elekt-imported',[
  $make('blockquote','',[$text('p','Omeny zapowiadają nadejście wielkiej wojny pomiędzy Dominatem Ebionitów a Wolnymi Klanami. Odczytawszy znaki, Wiedźmy z całej Rubieży przybywają do Czarnego Kromlechu, by radzić i wróżyć. Po której stronie powinny się opowiedzieć? Czy wesprzeć potężnego, starego Cesarza, czy gorącokrwiste Klany?')],'Czarny Kromlech'),
  $text('p','Wiedźmy były jednym z pierwszych trzech dodatków do gry Elekt i wyprzedały się najszybciej. W Wiedźmach 2 powracają z nowymi kartami. Ilustracje przygotowała Justyna Szumczyk, która szlifowała umiejętności pod okiem Katarzyny Malinowskiej — autorki ilustracji Elekta.'),
 ],'Opis fabularny'),
],'Dodatkowy opis');
$links=[];foreach($wallpaper['innerBlocks'] as $b)if($b['blockName']==='wtf/ele-text'&&str_contains($b['attrs']['content']??'','expansion-page-link'))$links[]=$b;
if(count($links)!==2)WP_CLI::error('Brak dwóch tapet.');
$root=$make('main','elekt-world elekt-expansion-page elekt-addon-page elekt-addon-wiedzmy2 wrap',[
 $text('a','Poznaj grę Elekt',['class'=>'elekt-expansion-back','href'=>home_url('/elekt/')]),$hero,$lore,
 $make('section','elekt-addon-art elekt-addon-character',[$make('figure','elekt-addon-character-figure',[$art,$text('figcaption','Żerczyni — ilustracja Justyny Szumczyk do dodatku Wiedźmy 2.')],'Żerczyni')],'Ilustracja żerczyni'),
 $make('section','elekt-addon-section',[$make('div','elekt-addon-section-heading',[$text('h2','Najczęściej zadawane pytania')],'FAQ'),$make('div','elekt-addon-section-copy',[$faq],'Pytania z WTF FAQ')],'FAQ Wiedźmy 2'),
 $make('section','elekt-addon-section',[$make('div','elekt-addon-section-heading',[$text('h2','Tapety do pobrania')],'Tapety'),$make('div','elekt-addon-section-copy elekt-imported',array_merge([$text('p','Ilustracje Justyny Szumczyk do pobrania jako tapety na komputer.')],$links),'Dwie tapety')],'Tapety Wiedźmy 2'),
],'Wiedźmy 2');
$root['attrs']['attributes']['id']='main';
update_post_meta(423,'_wtf_before_expansion_detail_v1',$page->post_content);
$saved=wp_update_post(['ID'=>423,'post_content'=>wp_slash(serialize_blocks([$root]))],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(423,'_wtf_expansion_detail_v1',true);
WP_CLI::success('Wiedźmy 2 sformatowane według wzorca.');
