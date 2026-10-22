<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(954);
if(!$page || get_post_meta(954,'_wtf_ele_key',true)!=='artefakty:pl')WP_CLI::error('Brak Artefaktów.');
if(get_post_meta(954,'_wtf_artefakty_carousel',true)){WP_CLI::success('Karuzela już dodana.');return;}
require_once ABSPATH.'wp-admin/includes/image.php';
$assets=[
1=>['Skrót zasad — przygotowanie i dobieranie','Karta pomocy dodatku Artefakty: symbole kart, przygotowanie gry, dobieranie Artefaktów i wyczerpanie talii.'],
2=>['Skrót zasad — limit i natychmiastowe użycie','Karta pomocy dodatku Artefakty: limit dwóch Artefaktów, zasady natychmiastowego użycia i przykład zagrania.'],
3=>['Artemistrz','Karta Artemistrz z dodatku Artefakty do Elekta: postać w okularach pracująca nad rozżarzonym przedmiotem.'],
4=>['Kustosz Wieży Dzwonów','Karta Kustosz Wieży Dzwonów z dodatku Artefakty do Elekta: zakapturzona postać ze sznurem i dzwonami.'],
5=>['Zakazany grymuar','Karta Zakazany grymuar z dodatku Artefakty do Elekta: oprawiona w skórę księga z okiem i wystającymi językami.'],
];
$make=static fn($tag,$class,$children,$label)=>['blockName'=>'wtf/ele-container','attrs'=>['tag'=>$tag,'attributes'=>['class'=>$class],'label'=>$label],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$text=static fn($tag,$content)=>['blockName'=>'wtf/ele-text','attrs'=>['tag'=>$tag,'content'=>$content],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
$cards=[];
foreach($assets as $n=>[$title,$alt]){
 $relative='media/elekt-artefakty-karta-'.$n.'.png';$path=wp_get_upload_dir()['basedir'].'/'.$relative;
 if(!is_file($path))WP_CLI::error('Brak pliku '.$relative);
 $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wtf_source_asset','meta_value'=>$relative,'suppress_filters'=>true]);
 if($existing)$id=$existing[0]->ID;
 else{
  $id=wp_insert_attachment(['post_title'=>'Elekt — Artefakty — '.$title,'post_content'=>$alt,'post_excerpt'=>$title.' — dodatek Artefakty.','post_mime_type'=>'image/png','post_status'=>'inherit'],$path,0,true);
  if(is_wp_error($id))WP_CLI::error($id->get_error_message());
  update_post_meta($id,'_wtf_source_asset',$relative);update_post_meta($id,'_wp_attachment_image_alt',$alt);
  wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$path));
 }
 $term=get_term_by('name','Elekt','wtf_media_group');if($term)wp_set_object_terms($id,[(int)$term->term_id],'wtf_media_group',true);
 $image=['blockName'=>'wtf/ele-image','attrs'=>['imageId'=>$id,'image'=>wp_get_attachment_url($id),'alt'=>$alt,'attributes'=>['loading'=>'lazy']],'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]];
 $cards[]=$make('figure','elekt-addon-card elekt-addon-card-portrait',[$image,$text('figcaption',$title)],$title);
}
$track=$make('div','elekt-card-carousel-track',$cards,'Pięć kart Artefaktów');
$track['attrs']['attributes']['id']='artefakty-card-track';
$track['attrs']['attributes']['aria-label']='Karty i skrót zasad dodatku Artefakty';
$gallery=$make('section','elekt-addon-art elekt-card-carousel',[$text('h2','Przyjrzyj się kartom'),$track],'Galeria kart');
$blocks=parse_blocks($page->post_content);$replaced=0;
foreach($blocks[0]['innerBlocks'] as &$block){if(($block['attrs']['label']??'')==='Galeria kart'){$block=$gallery;$replaced++;}}unset($block);
if($replaced!==1)WP_CLI::error('Oczekiwano jednej galerii.');
update_post_meta(954,'_wtf_before_artefakty_carousel',$page->post_content);
$saved=wp_update_post(['ID'=>954,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(954,'_wtf_artefakty_carousel',true);
WP_CLI::success('Dodano karuzelę pięciu kart.');
