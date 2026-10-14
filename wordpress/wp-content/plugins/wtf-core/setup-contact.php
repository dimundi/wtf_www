<?php
if(!defined('WP_CLI')||!WP_CLI)exit;
wp_set_current_user(1);
$seed=json_decode(file_get_contents(__DIR__.'/contact-seed.json'),true,512,JSON_THROW_ON_ERROR);
function wtf_contact_seed_block($name,$attrs,$children=[]){return ['blockName'=>$name,'attrs'=>$attrs,'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];}
function wtf_contact_seed_attrs($node){$a=[];foreach($node->attributes as $attr)$a[$attr->name]=$attr->value;return $a;}
function wtf_contact_seed_nodes($parent){
 $blocks=[];
 foreach($parent->childNodes as $node){
  if(!$node instanceof DOMElement)continue;
  $tag=strtolower($node->tagName);$attrs=wtf_contact_seed_attrs($node);
  if($tag==='form'){
   $labels=$node->getElementsByTagName('label');$keys=['nameLabel','emailLabel','subjectLabel','messageLabel'];$a=[];
   foreach($keys as $i=>$k)$a[$k]=$labels->item($i)->textContent;
   $a['buttonLabel']=$node->getElementsByTagName('button')->item(0)->textContent;
   $blocks[]=wtf_contact_seed_block('wtf/contact-form',$a);continue;
  }
  if(in_array($tag,['span','h1','h2','h3','p','a','address','dt','dd'],true)){
   $html='';foreach($node->childNodes as $child)$html.=$node->ownerDocument->saveHTML($child);
   $blocks[]=wtf_contact_seed_block('wtf/contact-text',['tag'=>$tag,'content'=>$html,'attributes'=>$attrs]);continue;
  }
  $label=$attrs['class']??$tag;
  $blocks[]=wtf_contact_seed_block('wtf/contact-container',['tag'=>$tag,'attributes'=>$attrs,'label'=>$label,'metadata'=>['name'=>$label]],wtf_contact_seed_nodes($node));
 }
 return $blocks;
}
$pages=[];
foreach(['pl','en'] as $lang){
 $old=get_posts(['post_type'=>'page','post_status'=>['publish','draft','pending','private'],'numberposts'=>1,'meta_key'=>'_wtf_contact_key','meta_value'=>'main:'.$lang,'lang'=>'','suppress_filters'=>true]);
 $id=$old?$old[0]->ID:wp_insert_post(['post_type'=>'page','post_title'=>$lang==='pl'?'Kontakt':'Contact','post_name'=>$lang==='pl'?'kontakt':'contact','post_status'=>$lang==='pl'?'publish':'draft'],true);
 if(is_wp_error($id))WP_CLI::error($id->get_error_message());
 $pages[$lang]=$id;pll_set_post_language($id,$lang);update_post_meta($id,'_wtf_contact_key','main:'.$lang);update_post_meta($id,'_wp_page_template','contact');
 if(get_post_meta($id,'_wtf_contact_imported',true)){WP_CLI::log('Zachowano edycje: '.$id);continue;}
 $catalog=get_posts(['post_type'=>'page','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_wtf_catalog_key','meta_value'=>'main:pl','lang'=>'pl','suppress_filters'=>true]);
 if(!$catalog)WP_CLI::error('Brak katalogu.');
 $html=str_replace([esc_url($seed['links'][$lang]),$seed['links'][$lang]],get_permalink($catalog[0]),$seed[$lang]);
 $doc=new DOMDocument('1.0','UTF-8');libxml_use_internal_errors(true);$doc->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);libxml_clear_errors();
 $main=$doc->getElementsByTagName('main')->item(0);
 $content=serialize_block(wtf_contact_seed_block('wtf/contact-container',['tag'=>'main','attributes'=>wtf_contact_seed_attrs($main),'label'=>'Kontakt'],wtf_contact_seed_nodes($main)));
 $result=wp_update_post(wp_slash(['ID'=>$id,'post_content'=>$content]),true);if(is_wp_error($result))WP_CLI::error($result->get_error_message());
 update_post_meta($id,'_wtf_contact_imported',1);
 WP_CLI::log($lang.': '.$id);
}
pll_save_post_translations($pages);flush_rewrite_rules();
WP_CLI::success('Kontakt przeniesiony; formularz nie wysyła wiadomości.');
