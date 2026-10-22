<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(415);
if(!$page || get_post_meta(415,'_wtf_ele_key',true)!=='main:pl')WP_CLI::error('Brak Elekta.');
if(str_contains($page->post_content,'elekt-learn-videos')){WP_CLI::success('Sekcja już istnieje.');return;}
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content)=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$videos=[];
foreach(['EDilRFaTygw','BW7NJwjF6hQ','8EFLKABp_x8'] as $i=>$id){
 $title='Nauka gry w Elekt — film '.($i+1);
 $video=['blockName'=>'wtf/ele-video','attrs'=>['url'=>'https://www.youtube-nocookie.com/embed/'.$id,'title'=>$title,'clickToPlay'=>true],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
 $videos[]=$make('article','elekt-learn-video',[$video,$text('h3',$title),$text('p','<a href="https://youtu.be/'.$id.'" target="_blank" rel="noopener">Zobacz na YouTube</a>')],$title);
}
$section=$make('section','elekt-section wrap',[$text('h2','Nauka gry'),$text('p','Poznaj zasady Elekta w trzech materiałach wideo.'),$make('div','elekt-learn-videos',$videos,'Filmy do nauki gry')],'Nauka gry');
$section['attrs']['attributes']['id']='elekt-learn';
$inserted=0;$menu=0;
$walk=function($blocks)use(&$walk,&$inserted,&$menu,$section){$result=[];foreach($blocks as $b){
 if($b['blockName']==='core/html'&&str_contains($b['innerHTML'],'elekt-section-menu')){
  $b['innerHTML']=preg_replace('~(<nav\b[^>]*>.*?</a>)~s','$1<a href="#elekt-learn">Nauka gry</a>',$b['innerHTML'],1,$count);
  $menu+=$count;$b['innerContent']=[$b['innerHTML']];
 }
 if($b['innerBlocks']){
  $before=count($b['innerBlocks']);$b['innerBlocks']=$walk($b['innerBlocks']);
  if($before!==count($b['innerBlocks'])){$b['innerHTML']='';$b['innerContent']=array_fill(0,count($b['innerBlocks']),null);}
 }
 if(($b['attrs']['attributes']['id']??'')==='elekt-expansions'){$result[]=$section;$inserted++;}
 $result[]=$b;
}return $result;};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if($inserted!==1||$menu!==1)WP_CLI::error('Nieoczekiwana struktura strony.');
update_post_meta(415,'_wtf_before_learning_videos',$page->post_content);
$saved=wp_update_post(['ID'=>415,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
WP_CLI::success('Dodano trzy filmy i link w menu.');
