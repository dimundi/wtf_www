<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$destination=$args[0]??'';
if (!$destination) { WP_CLI::error('Podaj plik JSON.'); }
$data=['version'=>1,'terms'=>[],'questions'=>[],'media'=>[],'tsukuyumiShortcode'=>get_option('wtf_faq_tsukuyumi_shortcode','')];
foreach (['wtf_faq_group','wtf_faq_game'] as $taxonomy) {
    foreach (get_terms(['taxonomy'=>$taxonomy,'hide_empty'=>false]) as $term) { $data['terms'][$taxonomy][]=['name'=>$term->name,'slug'=>$term->slug,'description'=>$term->description]; }
}
$posts=get_posts(['post_type'=>'wtf_faq','post_status'=>['publish','draft','pending','private','future'],'numberposts'=>-1,'orderby'=>['menu_order'=>'ASC','ID'=>'ASC'],'suppress_filters'=>true]);
$counts=wtf_faq_vote_counts(wp_list_pluck($posts,'ID'));
foreach ($posts as $post) {
    $terms=[];
    foreach (['wtf_faq_group','wtf_faq_game'] as $taxonomy) { $terms[$taxonomy]=wp_get_object_terms($post->ID,$taxonomy,['fields'=>'slugs']); }
    $source=get_post_meta($post->ID,'_wtf_faq_source_id',true);
    $key=get_post_meta($post->ID,'_wtf_faq_transfer_key',true)?:($source?:hash('sha256',home_url().'#'.$post->ID));
    $data['questions'][]=['key'=>$key,'source'=>$source,'title'=>$post->post_title,'content'=>$post->post_content,'status'=>$post->post_status,'order'=>$post->menu_order,'terms'=>$terms,'votes'=>$counts[$post->ID]];
    $doc=new DOMDocument();libxml_use_internal_errors(true);$doc->loadHTML('<?xml encoding="UTF-8">'.$post->post_content);libxml_clear_errors();
    foreach ($doc->getElementsByTagName('img') as $img) {
        $id=0;
        if (preg_match('/\bwp-image-(\d+)\b/',$img->getAttribute('class'),$match)) { $id=(int)$match[1]; }
        if (!$id) { $id=attachment_url_to_postid($img->getAttribute('src')); }
        if (!$id || isset($data['media'][$id])) { continue; }
        $file=get_post_meta($id,'_wp_attached_file',true);
        if (!$file || !is_file(get_attached_file($id))) { WP_CLI::error('Brak obrazu '.$id); }
        $url=wp_get_attachment_url($id);$assetKey=hash('sha256',$url);
        $data['media'][$id]=['id'=>$id,'key'=>$assetKey,'file'=>$file,'destination'=>'media/wtf_faq/'.substr($assetKey,0,12).'-'.basename($file),'url'=>$url,'title'=>get_the_title($id),'alt'=>get_post_meta($id,'_wp_attachment_image_alt',true)];
    }
}
$data['media']=array_values($data['media']);
file_put_contents($destination,wp_json_encode($data,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
WP_CLI::success('Wyeksportowano '.count($posts).' pytań i '.count($data['media']).' obrazów.');
