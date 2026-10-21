<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$items=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>-1,'meta_query'=>[['key'=>'_wtf_source_asset','value'=>'media/tsu-wum-WUM_','compare'=>'LIKE']]]);
foreach($items as $p) {
    echo wp_json_encode(['id'=>$p->ID,'title'=>$p->post_title,'alt'=>get_post_meta($p->ID,'_wp_attachment_image_alt',true),'caption'=>$p->post_excerpt,'description'=>$p->post_content,'groups'=>wp_get_object_terms($p->ID,'wtf_media_group',['fields'=>'names'])],JSON_UNESCAPED_UNICODE).PHP_EOL;
}
