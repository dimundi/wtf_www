<?php
/**
 * Plugin Name: wtf_faq
 * Description: Grupy FAQ, shortcode i głosowanie przy odpowiedziach.
 * Version: 0.1.0
 * Requires PHP: 8.0
 * Text Domain: wtf_faq
 */
if (!defined('ABSPATH')) { exit; }
define('WTF_FAQ_DIR', __DIR__);
require_once __DIR__.'/includes/admin.php';
require_once __DIR__.'/includes/votes.php';

register_activation_hook(__FILE__, 'wtf_faq_install_votes');
add_action('init', function () {
    register_post_type('wtf_faq', [
        'labels'=>['name'=>'Pytania FAQ','singular_name'=>'Pytanie FAQ','add_new'=>'Dodaj pytanie','add_new_item'=>'Dodaj pytanie FAQ','edit_item'=>'Edytuj pytanie'],
        'public'=>false,'show_ui'=>true,'show_in_menu'=>'wtf_faq','supports'=>['title','editor','page-attributes'],'capability_type'=>'post','map_meta_cap'=>true,
    ]);
    foreach (['wtf_faq_group'=>['Grupy','Grupa'],'wtf_faq_game'=>['Gry','Gra']] as $taxonomy=>$labels) {
        register_taxonomy($taxonomy, 'wtf_faq', ['labels'=>['name'=>$labels[0],'singular_name'=>$labels[1]],'public'=>false,'show_ui'=>true,'show_admin_column'=>true,'hierarchical'=>false,'meta_box_cb'=>false]);
    }
    wp_register_style('wtf_faq', plugins_url('assets/faq.css',__FILE__), [], filemtime(__DIR__.'/assets/faq.css'));
    wp_register_script('wtf_faq', plugins_url('assets/faq.js',__FILE__), [], filemtime(__DIR__.'/assets/faq.js'), true);
    wp_localize_script('wtf_faq','wtfFaqConfig',['endpoint'=>rest_url('wtf-faq/v1/vote'),'restNonce'=>is_user_logged_in()?wp_create_nonce('wp_rest'):'','storageKey'=>'wtf-faq:'.substr(hash('sha256',home_url()),0,12)]);
});

// Także zapis programistyczny nie może nadać kilku grup lub gier.
add_action('set_object_terms', function ($id,$terms,$ttids,$taxonomy) {
    if (!in_array($taxonomy,['wtf_faq_group','wtf_faq_game'],true) || count($ttids)<2) { return; }
    $first=get_term_by('term_taxonomy_id',$ttids[0],$taxonomy);
    if ($first) { wp_set_object_terms($id,[(int)$first->term_id],$taxonomy); }
},10,4);

add_shortcode('wtf_faq', function ($attributes) {
    $a=shortcode_atts(['game'=>'','groups'=>''], $attributes, 'wtf_faq');
    $slugs=array_values(array_filter(array_map('sanitize_title',explode(',',$a['groups']))));
    if (!$slugs) { return ''; }
    $tax=[['taxonomy'=>'wtf_faq_group','field'=>'slug','terms'=>$slugs]];
    if ($a['game']) { $tax[]=['taxonomy'=>'wtf_faq_game','field'=>'slug','terms'=>sanitize_title($a['game'])]; }
    $questions=get_posts(['post_type'=>'wtf_faq','post_status'=>'publish','numberposts'=>-1,'orderby'=>['menu_order'=>'ASC','ID'=>'ASC'],'tax_query'=>$tax,'suppress_filters'=>true]);
    if (!$questions) { return ''; }
    wp_enqueue_style('wtf_faq');wp_enqueue_script('wtf_faq');
    $grouped=[];
    foreach ($questions as $question) {
        $terms=wp_get_object_terms($question->ID,'wtf_faq_group');
        if (!is_wp_error($terms) && $terms) { $grouped[$terms[0]->slug]['term']=$terms[0];$grouped[$terms[0]->slug]['questions'][]=$question; }
    }
    $counts=wtf_faq_vote_counts(wp_list_pluck($questions,'ID'));
    ob_start(); ?><div class="wtf-faq" data-wtf-faq>
    <?php foreach ($slugs as $slug): if (empty($grouped[$slug])) { continue; } $group=$grouped[$slug]; ?>
    <section class="wtf-faq__group"><h3 class="wtf-faq__heading"><?php echo esc_html($group['term']->name); ?></h3>
    <?php foreach ($group['questions'] as $question): $id=$question->ID; ?>
    <details class="wtf-faq__item" data-faq-id="<?php echo (int)$id; ?>"><summary class="wtf-faq__question"><?php echo esc_html($question->post_title); ?><svg aria-hidden="true" viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m4 6 4 4 4-4"/></svg></summary>
    <div class="wtf-faq__answer"><?php echo wp_kses_post(wpautop($question->post_content)); ?>
    <div class="wtf-faq__feedback" role="group" aria-label="Czy to było pomocne?"><span>Czy to było pomocne?</span>
    <?php foreach (['up'=>'Tak, pomocne','down'=>'Nie, niepomocne'] as $direction=>$label): ?>
    <button type="button" class="wtf-faq__vote" data-vote="<?php echo esc_attr($direction); ?>" aria-label="<?php echo esc_attr($label); ?>" aria-pressed="false"><svg class="wtf-faq__thumb<?php if ($direction==='down') { echo ' wtf-faq__thumb--down'; } ?>" aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M7 10v10H3V10h4Zm0 0 5-7c2 0 3 1 2 4l-1 3h6c2 0 2 1 2 2l-2 7c0 1-1 1-2 1H7"/></svg><span data-vote-count><?php echo (int)$counts[$id][$direction]; ?></span></button>
    <?php endforeach; ?><span class="wtf-faq__status" role="status" aria-live="polite"></span></div></div></details>
    <?php endforeach; ?></section><?php endforeach; ?></div><?php return ob_get_clean();
});
