<?php
if (!defined('ABSPATH')) { exit; }
echo 'Environment: ' . wp_get_environment_type() . "\n";
foreach ([13,16,68,69,76,77] as $id) {
    $page = get_post($id);
    echo wp_json_encode(['id'=>$id,'title'=>$page->post_title,'status'=>$page->post_status,'language'=>pll_get_post_language($id),'translations'=>pll_get_post_translations($id),'url'=>get_permalink($id)],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . "\n";
}
