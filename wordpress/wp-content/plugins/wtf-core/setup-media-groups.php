<?php
if(!defined('WP_CLI')||!WP_CLI)exit;
wp_set_current_user(1);
$attachments=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>-1,'lang'=>'','suppress_filters'=>true]);
$mediaIds=[];$paths=[];$plan=[];$filesBefore=[];
foreach($attachments as $attachment){
    $id=$attachment->ID;$mediaIds[$id]=true;$plan[$id]=[];
    $file=get_post_meta($id,'_wp_attached_file',true);$filesBefore[$id]=$file;$meta=wp_get_attachment_metadata($id);
    $sources=array_filter([$file,get_post_meta($id,'_wtf_source_asset',true),!empty($meta['original_image'])?dirname($file).'/'.$meta['original_image']:'']);
    foreach($sources as $path){
        $paths[rawurldecode($path)]=$id;$name=strtolower(rawurldecode(basename($path)));
        foreach(['elekt'=>'elekt','kurzolaki'=>'kurzolaki','tsukuyumi'=>'tsukuyumi','tsu-'=>'tsukuyumi','klanarchia'=>'klanarchia','echo-'=>'echo-swiatow'] as $prefix=>$group)if(str_starts_with($name,$prefix))$plan[$id][]=$group;
        if(str_starts_with($name,'wtf-logo'))$plan[$id][]='ogolne';
        if(str_starts_with($name,'platnosci'))$plan[$id]=array_merge($plan[$id],['ogolne','sklep']);
    }
}
function wtf_media_seed_url_family($url){
    $path=parse_url((string)$url,PHP_URL_PATH)?:'';
    foreach(['elekt','kurzolaki','tsukuyumi','klanarchia','echo-swiatow'] as $family)if(preg_match('~/(?:produkt/)?'.preg_quote($family,'~').'(?:/|-|$)~',$path))return $family;
    return '';
}
function wtf_media_seed_collect($value,$context,&$plan,$mediaIds,$paths){
    if(!is_array($value))return;
    foreach(['url','buttonUrl'] as $field)if(!empty($value[$field])&&is_string($value[$field])&&($family=wtf_media_seed_url_family($value[$field])))$context[]=$family;
    if(!empty($value['attributes']['href'])&&($family=wtf_media_seed_url_family($value['attributes']['href'])))$context[]=$family;
    $ids=[];foreach(['imageId','mobileId','fileId'] as $field)if(!empty($value[$field]))$ids[]=absint($value[$field]);
    if(isset($value['image'])&&is_string($value['image'])){$path=rawurldecode(parse_url($value['image'],PHP_URL_PATH)?:'');$path=preg_replace('~^.*?/uploads/~','',$path);if(isset($paths[$path]))$ids[]=$paths[$path];}
    foreach($ids as $id)if(isset($mediaIds[$id]))$plan[$id]=array_merge($plan[$id],$context);
    foreach($value as $child)if(is_array($child))wtf_media_seed_collect($child,$context,$plan,$mediaIds,$paths);
}
function wtf_media_seed_blocks($blocks,$context,&$plan,$mediaIds,$paths){
    foreach($blocks as $block){
        $here=$context;if($block['blockName']==='wtf/hero')$here[]='banery';
        $attrs=$block['attrs'];if($block['blockName']==='core/image'&&isset($attrs['id']))$attrs['imageId']=$attrs['id'];
        wtf_media_seed_collect($attrs,$here,$plan,$mediaIds,$paths);
        if(!empty($attrs['attributes']['href'])&&($family=wtf_media_seed_url_family($attrs['attributes']['href'])))$here[]=$family;
        wtf_media_seed_blocks($block['innerBlocks'],$here,$plan,$mediaIds,$paths);
    }
}
foreach(get_posts(['post_type'=>['page','wp_template_part','wtf_faq'],'post_status'=>['publish','draft','private'],'numberposts'=>-1,'lang'=>'','suppress_filters'=>true]) as $post){
    $context=[];foreach(['elekt'=>'_wtf_ele_kind','kurzolaki'=>'_wtf_kurz_kind','tsukuyumi'=>'_wtf_tsu_kind','echo-swiatow'=>'_wtf_echo_kind'] as $family=>$meta)if(get_post_meta($post->ID,$meta,true))$context[]=$family;
    if($post->post_type==='wtf_faq'){$games=wp_get_object_terms($post->ID,'wtf_faq_game',['fields'=>'slugs']);if(!is_wp_error($games))$context=array_merge($context,$games);}
    $blocks=parse_blocks($post->post_content);wtf_media_seed_blocks($blocks,$context,$plan,$mediaIds,$paths);
    preg_match_all('/wp-image-(\d+)/',$post->post_content,$matches);foreach($matches[1] as $id)if(isset($mediaIds[$id]))$plan[$id]=array_merge($plan[$id],$context);
}
$terms=[];foreach(wtf_core_media_groups_defaults() as $slug=>$label){$term=get_term_by('slug',$slug,'wtf_media_group');if(!$term)WP_CLI::error('Brak grupy '.$slug);$terms[$slug]=$term->term_id;}
$changed=0;$kept=0;wp_defer_term_counting(true);
try{
    foreach($plan as $id=>$slugs){
        $existing=wp_get_object_terms($id,'wtf_media_group',['fields'=>'ids']);if(is_wp_error($existing))WP_CLI::error($existing->get_error_message());if($existing){$kept++;continue;}
        $slugs=array_values(array_intersect(array_unique($slugs),array_keys($terms)));if(!$slugs)$slugs=['ogolne'];
        $result=wp_set_object_terms($id,array_map(static fn($slug)=>(int)$terms[$slug],$slugs),'wtf_media_group');if(is_wp_error($result))WP_CLI::error($result->get_error_message());$changed++;
    }
}finally{wp_defer_term_counting(false);}
foreach($filesBefore as $id=>$file)if(get_post_meta($id,'_wp_attached_file',true)!==$file)WP_CLI::error('Zmieniła się ścieżka pliku.');
WP_CLI::success(wp_json_encode(['assigned'=>$changed,'preserved'=>$kept,'filesUnchanged'=>true],JSON_UNESCAPED_UNICODE));
