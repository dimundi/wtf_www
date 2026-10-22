<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(954);
if(!$page || get_post_meta(954,'_wtf_ele_key',true)!=='artefakty:pl')WP_CLI::error('Brak Artefaktów.');
if(get_post_meta(954,'_wtf_artefakty_rules_formatted',true)){WP_CLI::success('Zasady już sformatowane.');return;}
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content)=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$found=0;$steps=0;
$walk=function($blocks)use(&$walk,&$found,&$steps,$make,$text){
 foreach($blocks as &$block){
  if($block['innerBlocks'])$block['innerBlocks']=$walk($block['innerBlocks']);
  if(($block['attrs']['label']??'')!=='Pełne zasady dodatku')continue;
  $found++;$formatted=[];
  foreach($block['innerBlocks'] as $source){
   $html=$source['attrs']['content']??'';
   if(!preg_match('~^<strong>(.*?)</strong><br\s*/?>\s*(.*)$~su',$html,$match))WP_CLI::error('Nieoczekiwana struktura akapitu.');
   $heading=$match[1]==='Setup'?'Przygotowanie gry':$match[1];
   $lines=preg_split('~<br\s*/?>\s*~u',$match[2]);$children=[$text('h3',$heading)];$list=[];
   $flush=function()use(&$children,&$list,$make){if($list){$children[]=$make('ul','elekt-addon-rule-list',$list,'Punkty zasad');$list=[];}};
   foreach($lines as $line){
    $line=trim($line);if($line==='')continue;
    $line=str_replace('Stree','Strefie',$line);
    if(preg_match('~^[–—-]\s*(.*)$~su',$line,$point)){$list[]=$text('li',$point[1]);continue;}
    $flush();
    if(str_starts_with($line,'<strong>Przykład:</strong>')){
     $example=preg_replace('~^<strong>Przykład:</strong>\s*~u','',$line);
     $children[]=$make('div','elekt-addon-rule-example',[$text('h4','Przykład'),$text('p',$example)],'Przykład zastosowania');
    }else{$children[]=$text('p',$line);}
   }
   $flush();$formatted[]=$make('section','elekt-addon-rule-step',$children,$heading);$steps++;
  }
  $block['attrs']['attributes']['class']='elekt-addon-section-copy elekt-imported elekt-addon-rules';
  $block['innerBlocks']=$formatted;$block['innerHTML']='';$block['innerContent']=array_fill(0,count($formatted),null);
 }
 return $blocks;
};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if($found!==1||$steps!==6)WP_CLI::error('Oczekiwano sześciu grup zasad.');
update_post_meta(954,'_wtf_before_rules_format',$page->post_content);
$saved=wp_update_post(['ID'=>954,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(954,'_wtf_artefakty_rules_formatted',true);
WP_CLI::success('Zasady rozdzielone na 6 sekcji, listy punktów i wyróżniony przykład.');
