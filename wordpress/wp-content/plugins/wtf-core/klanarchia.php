<?php
if (!defined('ABSPATH')) exit;
require_once __DIR__.'/includes/klanarchia-subpage-header.php';

function wtf_klan_local_game_link($url){
    if(!in_array(untrailingslashit($url),['https://whatthefrog.pl/klanarchia','http://whatthefrog.pl/klanarchia','https://www.whatthefrog.pl/klanarchia','http://localhost:8082/klanarchia'],true))return $url;
    $page=get_page_by_path('klanarchia',OBJECT,'page');
    if(!$page || $page->post_type!=='page' || $page->post_status!=='publish')return $url;
    if(function_exists('pll_get_post')){
        $translated=pll_get_post($page->ID,wtf_core_language());
        if($translated && get_post_status($translated)==='publish')return get_permalink($translated);
    }
    return get_permalink($page);
}
