<?php
// Jawna, idempotentna poprawka układu ilustracji Moon Circus.
if (!defined('ABSPATH')) { exit; }
$inspect = isset($args[0]) && $args[0] === 'inspect';
function wtf_mc_art_matches($block, $name) {
    if (($block['blockName'] ?? '') !== 'wtf/tsu-image') { return false; }
    $attrs = $block['attrs'] ?? [];
    $file = ($attrs['image'] ?? '') . ' ' . get_attached_file($attrs['imageId'] ?? 0);
    $replacement = $name === 'MC_UC3' ? 'MoonCircus_Img2' : 'MoonCircus_Img1';
    return stripos($file, $name) !== false || stripos($file, $replacement) !== false;
}
function wtf_mc_art_layout(&$blocks) {
    $changed = false;
    foreach ($blocks as &$block) {
        if (preg_match('/tsu-faction-(?:art-pair|tactics|contents)/', $block['attrs']['attributes']['class'] ?? '')) { continue; }
        if (wtf_mc_art_layout($block['innerBlocks'])) {
            $block['innerContent'] = array_fill(0, count($block['innerBlocks']), null);
            $changed = true;
        }
    }
    unset($block);
    for ($i = 0; $i + 1 < count($blocks); $i++) {
        $heading = trim(wp_strip_all_tags($blocks[$i]['attrs']['content'] ?? ''));
        if (($blocks[$i]['attrs']['tag'] ?? '') === 'h3' && in_array($heading, ['TAKTYKA','ZAWARTOŚĆ'], true)) {
            $end = $i + 1;
            $class = $heading === 'TAKTYKA' ? 'tsu-faction-tactics' : 'tsu-faction-contents';
            if ($heading === 'TAKTYKA') {
                while ($end < count($blocks) && ($blocks[$end]['attrs']['tag'] ?? (($blocks[$end]['blockName'] ?? '') === 'wtf/tsu-text' ? 'p' : '')) === 'p') { $end++; }
            } elseif (($blocks[$end]['attrs']['tag'] ?? '') === 'ul' || strpos($blocks[$end]['innerHTML'] ?? '', '<ul') !== false) { $end++; }
            if ($end > $i + 1) {
                $children = array_slice($blocks, $i, $end - $i);
                $wrapper = ['blockName'=>'wtf/tsu-container','attrs'=>['tag'=>'section','label'=>$heading,'attributes'=>['class'=>$class]],'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
                array_splice($blocks, $i, $end - $i, [$wrapper]);
                $changed = true;
                continue;
            }
        }
        if (!wtf_mc_art_matches($blocks[$i], 'MC_UC3') || !wtf_mc_art_matches($blocks[$i + 1], 'MC_UC2')) { continue; }
        $pair = array_slice($blocks, $i, 2);
        $wrapper = ['blockName'=>'wtf/tsu-container', 'attrs'=>['tag'=>'div','label'=>'Ilustracje Moon Circus','attributes'=>['class'=>'tsu-faction-art-pair']], 'innerBlocks'=>$pair,'innerHTML'=>'','innerContent'=>[null,null]];
        array_splice($blocks, $i, 2, [$wrapper]);
        $changed = true;
    }
    return $changed;
}
foreach (['pl', 'en'] as $language) {
    $pages = get_posts(['post_type'=>'page','post_status'=>'any','numberposts'=>1,'meta_key'=>'_wtf_tsu_key','meta_value'=>'moon-circus:'.$language,'suppress_filters'=>true]);
    if (!$pages) { throw new RuntimeException('Brak strony Moon Circus: ' . $language); }
    $page = $pages[0];
    $blocks = parse_blocks($page->post_content);
    if ($inspect) {
        $walk = function ($items) use (&$walk) {
            foreach ($items as $item) {
                if (($item['blockName'] ?? '') === 'wtf/tsu-image') { echo wp_json_encode($item['attrs'], JSON_UNESCAPED_SLASHES) . "\n"; }
                $walk($item['innerBlocks']);
            }
        };
        echo "Page {$page->ID}: $language\n";
        $walk($blocks);
        continue;
    }
    if (wtf_mc_art_layout($blocks)) {
        $result = wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash(serialize_blocks($blocks))], true);
        if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
        echo "Updated {$page->ID}: $language\n";
    } else { echo "Unchanged {$page->ID}: $language\n"; }
}
