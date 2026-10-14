<?php
if (!defined('ABSPATH')) { exit; }
$pages = get_posts(['post_type'=>'page','post_status'=>'any','numberposts'=>-1,'meta_key'=>'_wtf_tsu_kind','meta_value'=>'faction','suppress_filters'=>true]);
if (!$pages) {
    $pages = get_posts(['post_type'=>'page','post_status'=>'any','numberposts'=>-1,'meta_key'=>'_wtf_tsu_key','suppress_filters'=>true]);
    $pages = array_filter($pages, fn($page) => strpos(get_post_meta($page->ID, '_wtf_tsu_key', true), 'main:') !== 0);
}
$walk = function ($blocks) use (&$walk) {
    foreach ($blocks as $block) {
        if (($block['blockName'] ?? '') === 'wtf/tsu-text') {
            echo wp_strip_all_tags($block['attrs']['content'] ?? '') . "\n";
        } elseif (($block['blockName'] ?? '') === 'core/html') { echo wp_strip_all_tags($block['innerHTML']) . "\n"; }
        $walk($block['innerBlocks']);
    }
};
foreach ($pages as $page) {
    $key = get_post_meta($page->ID, '_wtf_tsu_key', true);
    if (substr($key, -3) !== ':pl') { continue; }
    if (isset($args[0]) && $args[0] === 'summary') {
        $lists = [];
        $remaining = [];
        $numbers = function ($blocks) use (&$numbers, &$lists, &$remaining) {
            $tokens = [];
            foreach ($blocks as $block) {
                $text = str_replace("\u{00a0}", ' ', wp_strip_all_tags(($block['attrs']['content'] ?? '') . ($block['innerHTML'] ?? '')));
                preg_match_all('/\d+/', $text, $matches);
                $tokens = array_merge($tokens, $matches[0], $numbers($block['innerBlocks']));
                if (strpos($block['attrs']['attributes']['class'] ?? '', 'tsu-contents-list') !== false) { $lists[] = count($block['innerBlocks']); }
                foreach (['Żarłoczny Rój opierają','Grając Nomadami należy','Wystawiona na planszy','zadawaniem','Tsukuyumi, sprawiło','efektu obrony, ten','Fireborn, przeciwnicy','Księżycu by zabić','eliminacja Tsukuyumi ma najwyższy'] as $error) {
                    if (strpos($text, $error) !== false) { $remaining[] = $block['attrs']['content'] ?? $block['innerHTML']; }
                }
            }
            return $tokens;
        };
        $current = $numbers(parse_blocks($page->post_content));
        $currentLists = $lists;
        $currentRemaining = $remaining;
        $same = null;
        foreach (wp_get_post_revisions($page->ID, ['posts_per_page'=>5]) as $revision) {
            if ($revision->post_content === $page->post_content) { continue; }
            $same = $numbers(parse_blocks($revision->post_content)) === $current;
            break;
        }
        echo wp_json_encode(['page'=>$page->ID,'title'=>$page->post_title,'columns'=>$currentLists,'numbers_unchanged'=>$same,'remaining'=>$currentRemaining], JSON_UNESCAPED_UNICODE) . "\n";
        continue;
    }
    echo "\n### {$page->ID} {$page->post_title} [$key]\n";
    $walk(parse_blocks($page->post_content));
}
