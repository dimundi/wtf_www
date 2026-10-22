<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
wp_set_current_user(1);
$main=get_post(415);
$find=function($blocks,$predicate) use (&$find){foreach($blocks as $b){if($predicate($b))return $b;if($b['innerBlocks']){$r=$find($b['innerBlocks'],$predicate);if($r)return $r;}}return null;};
$blocks=parse_blocks($main->post_content);
$card=$find($blocks,static fn($b)=>($b['attrs']['attributes']['id']??'')==='dodatek-artefakty');
if(!$card)WP_CLI::error('Brak karty Artefaktów.');
$existing=get_posts(['post_type'=>'page','post_status'=>['publish','draft'],'numberposts'=>1,'meta_key'=>'_wtf_ele_key','meta_value'=>'artefakty:pl','suppress_filters'=>true]);
if($existing){WP_CLI::success('Podstrona już istnieje: '.$existing[0]->ID);return;}
$cover=$find([$card],static fn($b)=>$b['blockName']==='wtf/ele-image');
$brief=$find([$card],static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
$full=$find([$card],static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-full-description');
$gallery=$find([$full],static fn($b)=>($b['attrs']['attributes']['class']??'')==='expansion-description-gallery');
$description=$find([$full],static fn($b)=>($b['attrs']['attributes']['class']??'')==='elekt-imported');
if(!$cover||!$brief||!$description||!$gallery)WP_CLI::error('Niepełne materiały.');
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content,$attributes=[])=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content,'attributes'=>$attributes],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cover['attrs']['attributes']=['loading'=>'eager','fetchpriority'=>'high'];
$facts=[];foreach(['Liczba kart'=>'30','Autor'=>'Krzysztof Głośnicki','Ilustracje'=>'Katarzyna Malinowska i Paweł Góral'] as $label=>$value)$facts[]=$make('div','elekt-addon-fact',[$text('dt',$label),$text('dd',$value)],$label);
$hero=$make('header','elekt-addon-hero',[
 $make('div','elekt-addon-cover',[$cover],'Okładka dodatku'),
 $make('div','elekt-addon-intro',[$text('span','DODATEK DO GRY ELEKT'),$text('h1','Artefakty'),$brief,$make('dl','elekt-addon-facts',$facts,'Informacje o dodatku')],'Artefakty — najważniejsze informacje'),
],'Wprowadzenie');
$intro=[];$rules=[];$contents=[];$inContents=false;
foreach($description['innerBlocks'] as $b){
 if(($b['attrs']['tag']??'')==='blockquote'){$intro[]=$b;continue;}
 if(($b['attrs']['content']??'')==='Lista kart'){$inContents=true;continue;}
 if($inContents)$contents[]=$b;else $rules[]=$b;
}
$art=[];
foreach($gallery['innerBlocks'] as $item){
 $image=$find([$item],static fn($b)=>$b['blockName']==='wtf/ele-image');
 if(!$image || !in_array($image['attrs']['imageId']??0,[439,440],true))continue;
 $image['attrs']['alt']=($image['attrs']['imageId']===439)?'Przykładowe karty z dodatku Artefakty do gry Elekt — zestaw pierwszy.':'Przykładowe karty z dodatku Artefakty do gry Elekt — zestaw drugi.';
 $art[]=$make('div','',[$image],'Przykładowe karty');
}
if(count($art)!==2)WP_CLI::error('Brak dwóch grafik Artefaktów.');
$sections=[
 $make('section','elekt-addon-section',[$make('div','elekt-addon-section-heading',[$text('h2','Ze świata Elekta')],'Świat gry'),$make('div','elekt-addon-section-copy elekt-imported',$intro,'Wprowadzenie fabularne')],'Dodatkowy opis'),
 $make('section','elekt-addon-section',[$make('div','elekt-addon-section-heading',[$text('h2','W pudełku')],'Zawartość'),$make('div','elekt-addon-section-copy elekt-imported',array_merge([$text('p','Dodatek zawiera 30 kart:')],$contents),'Lista kart')],'Zawartość dodatku'),
 $make('section','elekt-addon-section',[$make('div','elekt-addon-section-heading',[$text('h2','Jak grać z Artefaktami')],'Zasady'),$make('div','elekt-addon-section-copy elekt-imported',$rules,'Pełne zasady dodatku')],'Zasady dodatku'),
 $make('section','elekt-addon-art',[$text('h2','Przyjrzyj się kartom'),$make('div','expansion-description-gallery',$art,'Przykładowe karty Artefaktów')],'Galeria kart'),
];
$root=$make('main','elekt-world elekt-expansion-page elekt-addon-page elekt-addon-artefakty wrap',array_merge([$text('a','Poznaj grę Elekt',['class'=>'elekt-expansion-back','href'=>home_url('/elekt/')]),$hero],$sections),'Artefakty');
$root['attrs']['attributes']['id']='main';
$pair=[];
foreach(['pl'=>415,'en'=>416] as $lang=>$parent){
 $existing=get_posts(['post_type'=>'page','post_status'=>['publish','draft'],'numberposts'=>1,'meta_key'=>'_wtf_ele_key','meta_value'=>'artefakty:'.$lang,'suppress_filters'=>true]);
 if($existing){$pair[$lang]=$existing[0]->ID;continue;}
 if(get_page_by_path(($lang==='pl'?'elekt':'elekt-en').'/artefakty'))WP_CLI::error('Adres zajęty.');
 $id=wp_insert_post(wp_slash(['post_type'=>'page','post_title'=>'Artefakty','post_name'=>'artefakty','post_parent'=>$parent,'post_status'=>$lang==='pl'?'publish':'draft','post_content'=>serialize_blocks([$root])]),true);
 if(is_wp_error($id))WP_CLI::error($id->get_error_message());
 update_post_meta($id,'_wtf_ele_key','artefakty:'.$lang);update_post_meta($id,'_wtf_ele_kind','expansion');update_post_meta($id,'_wp_page_template','elekt');update_post_meta($id,'_wtf_expansion_detail_v1',true);
 pll_set_post_language($id,$lang);$pair[$lang]=$id;
}
pll_save_post_translations($pair);
$shorten=function($items,$inside=false)use(&$shorten,$pair,$make,$text){$result=[];foreach($items as $b){
 $local=$inside||($b['attrs']['attributes']['id']??'')==='dodatek-artefakty';$class=$b['attrs']['attributes']['class']??'';
 if($local&&in_array($class,['expansion-full-description','expansion-card-count'],true))continue;
 if($b['innerBlocks'])$b['innerBlocks']=$shorten($b['innerBlocks'],$local);
 if(($b['attrs']['attributes']['id']??'')==='dodatek-artefakty'){
  foreach($b['innerBlocks'] as &$child){if(($child['attrs']['attributes']['class']??'')==='expansion-description'){
   $child['innerBlocks'][]=$make('div','expansion-actions',[$text('p','<a class="expansion-page-link" href="'.esc_url(get_permalink($pair['pl'])).'">Strona dodatku</a>')],'Informacje o dodatku');
   $child['innerHTML']='';$child['innerContent']=array_fill(0,count($child['innerBlocks']),null);
  }}unset($child);
 }
 if($b['blockName']==='wtf/ele-container'){$b['innerHTML']='';$b['innerContent']=array_fill(0,count($b['innerBlocks']),null);}
 $result[]=$b;
}return $result;};
update_post_meta(415,'_wtf_before_artefakty_page',$main->post_content);
$saved=wp_update_post(['ID'=>415,'post_content'=>wp_slash(serialize_blocks($shorten($blocks)))],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
WP_CLI::success('Artefakty: PL '.$pair['pl'].', EN '.$pair['en'].' (szkic), '.get_permalink($pair['pl']));
