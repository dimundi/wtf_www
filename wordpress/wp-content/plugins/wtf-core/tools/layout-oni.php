<?php
// Ręczna zmiana układu istniejących bloków; uruchamiać przez wp eval-file.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$pages = get_posts(['post_type'=>'page', 'post_status'=>'publish', 'meta_key'=>'_wtf_tsu_key', 'meta_value'=>'oni:pl', 'numberposts'=>1, 'suppress_filters'=>true]);
if (count($pages) !== 1) { WP_CLI::error('Nie znaleziono strony ONI.'); }
$page = $pages[0];
if (strpos($page->post_content, 'tsu-oni-character') !== false) { WP_CLI::success('Układ już zapisany.'); return; }
$count = 0;
$container = static function ($class, $children) {
    return ['blockName'=>'wtf/tsu-container', 'attrs'=>['tag'=>'div', 'attributes'=>['class'=>$class]], 'innerBlocks'=>$children, 'innerHTML'=>'', 'innerContent'=>array_fill(0, count($children), null)];
};
$walk = function ($blocks) use (&$walk, &$count, $container) {
    $result = [];
    for ($i=0; $i<count($blocks); $i++) {
        $block = $blocks[$i];
        if ($block['blockName']==='wtf/tsu-image' && preg_match('~tsu_oni_(abyssal|wyrocznia|what|jaga|mroczna_trojca|defiler)~i', wp_json_encode($block['attrs']))) {
            $text = [];
            while (isset($blocks[$i+1]) && $blocks[$i+1]['blockName']!=='wtf/tsu-image') { $text[]=$blocks[++$i]; }
            $result[]=$container('tsu-oni-character', [$block, $container('tsu-oni-character-copy', $text)]);
            $count++;
        } else {
            if ($block['innerBlocks']) {
                $oldCount=count($block['innerBlocks']);
                $block['innerBlocks']=$walk($block['innerBlocks']);
                if (count($block['innerBlocks'])!==$oldCount) {
                    if ($block['blockName']!=='wtf/tsu-container') { WP_CLI::error('Nieoczekiwany kontener.'); }
                    $block['innerHTML']='';
                    $block['innerContent']=array_fill(0, count($block['innerBlocks']), null);
                }
            }
            $result[]=$block;
        }
    }
    return $result;
};
$updated = serialize_blocks($walk(parse_blocks($page->post_content)));
if ($count!==6) { WP_CLI::error('Oczekiwano 6 postaci, znaleziono: '.$count); }
// Zachowaj pełną kopię przed zmianą struktury.
update_post_meta($page->ID, '_wtf_oni_before_character_layout', $page->post_content);
$saved=wp_update_post(['ID'=>$page->ID, 'post_content'=>wp_slash($updated)], true);
if (is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
WP_CLI::success('Zapisano 6 wierszy postaci ONI, strona '.$page->ID.'.');
