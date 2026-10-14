<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
wp_set_current_user(1);
$shortcode=get_option('wtf_faq_elekt_shortcode');
if(!$shortcode)WP_CLI::error('Brak zaimportowanego FAQ Elekta.');
function ele_faq_block($name,$attrs,$children=[]) { return ['blockName'=>$name,'attrs'=>$attrs,'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)]; }
function ele_faq_shortcode($shortcode) { return ['blockName'=>'core/shortcode','attrs'=>[],'innerBlocks'=>[],'innerHTML'=>$shortcode,'innerContent'=>[$shortcode]]; }
function ele_faq_section($blocks,$shortcode,&$found) {
    foreach($blocks as &$block) {
        if(($block['attrs']['attributes']['id']??'')==='faq') {
            $block=ele_faq_block('wtf/ele-container',['tag'=>'section','attributes'=>['id'=>'faq','class'=>'elekt-section wrap'],'label'=>'FAQ'],[
                ele_faq_block('wtf/ele-text',['tag'=>'h2','content'=>'FAQ']),
                ele_faq_shortcode($shortcode),
            ]);
            $found=true;
        } else $block['innerBlocks']=ele_faq_section($block['innerBlocks'],$shortcode,$found);
    }
    unset($block);return $blocks;
}
$pages=get_posts(['post_type'=>'page','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_wtf_ele_key','meta_value'=>'main:pl','suppress_filters'=>true]);
if(!$pages)WP_CLI::error('Brak Elekta.');$main=$pages[0];
if(!str_contains($main->post_content,'[wtf_faq')) {
    $found=false;$blocks=ele_faq_section(parse_blocks($main->post_content),$shortcode,$found);
    if(!$found)WP_CLI::error('Brak sekcji FAQ na Elekcie.');
    file_put_contents('/tmp/elekt-before-faq.txt',$main->post_content);
    $result=wp_update_post(wp_slash(['ID'=>$main->ID,'post_content'=>serialize_blocks($blocks)]),true);
    if(is_wp_error($result))WP_CLI::error($result->get_error_message());
}
$faqPages=[];
foreach(['pl','en'] as $lang) {
    $existing=get_posts(['post_type'=>'page','post_status'=>['publish','draft'],'numberposts'=>1,'meta_key'=>'_wtf_faq_game_page','meta_value'=>'elekt:'.$lang,'suppress_filters'=>true]);
    if($existing){$faqPages[$lang]=$existing[0]->ID;continue;}
    $slug=$lang==='pl'?'elekt-faq':'elekt-faq-en';
    foreach(get_posts(['post_type'=>'attachment','post_status'=>'inherit','name'=>$slug,'numberposts'=>-1,'suppress_filters'=>true]) as $attachment)wp_update_post(['ID'=>$attachment->ID,'post_name'=>$slug.'_grafika']);
    $content=ele_faq_block('wtf/ele-container',['tag'=>'main','attributes'=>['id'=>'main','class'=>'elekt-world elekt-expansion-page wrap'],'label'=>'FAQ Elekt'],[
        ele_faq_block('wtf/ele-text',['tag'=>'a','attributes'=>['class'=>'elekt-expansion-back','href'=>get_permalink($main).'#faq'],'content'=>$lang==='pl'?'Wróć do Elekta':'Back to Elekt']),
        ele_faq_block('wtf/ele-text',['tag'=>'h1','content'=>'FAQ Elekt']),
        ele_faq_shortcode($shortcode),
    ]);
    $id=wp_insert_post(wp_slash(['post_type'=>'page','post_status'=>$lang==='pl'?'publish':'draft','post_title'=>'FAQ Elekt','post_name'=>$slug,'post_content'=>serialize_block($content)]),true);
    if(is_wp_error($id))WP_CLI::error($id->get_error_message());
    update_post_meta($id,'_wtf_faq_game_page','elekt:'.$lang);
    update_post_meta($id,'_wtf_ele_kind','expansion');update_post_meta($id,'_wp_page_template','elekt');
    pll_set_post_language($id,$lang);$faqPages[$lang]=$id;
}
pll_save_post_translations($faqPages);
flush_rewrite_rules();
WP_CLI::success(wp_json_encode(['main'=>get_permalink($main).'#faq','faqPage'=>get_permalink($faqPages['pl']),'ids'=>$faqPages,'shortcode'=>$shortcode],JSON_UNESCAPED_UNICODE));
