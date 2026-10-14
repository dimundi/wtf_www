<?php
if (!defined('ABSPATH')) { exit; }
$apply = isset($args[0]) && $args[0] === 'apply';
function wtf_faction_text_key($text) {
    $text = html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/u', ' ', str_replace("\u{00a0}", ' ', $text)));
}
function wtf_faction_find_intro($blocks) {
    foreach ($blocks as $block) {
        if (strpos($block['attrs']['attributes']['class'] ?? '', 'tsu-faction-header') !== false) {
            $paragraphs = [];
            $collect = function ($items) use (&$collect, &$paragraphs) {
                foreach ($items as $item) {
                    if (($item['blockName'] ?? '') === 'wtf/tsu-text' && ($item['attrs']['tag'] ?? 'p') === 'p') { $paragraphs[] = wtf_faction_text_key($item['attrs']['content'] ?? ''); }
                    $collect($item['innerBlocks']);
                }
            };
            $collect($block['innerBlocks']);
            return implode(' ', $paragraphs);
        }
        $intro = wtf_faction_find_intro($block['innerBlocks']);
        if ($intro !== '') { return $intro; }
    }
    return '';
}
function wtf_faction_remove_intro(&$block, $intro, $key, &$removed) {
    if (strpos($block['attrs']['attributes']['class'] ?? '', 'tsu-faction-description') !== false) {
        foreach ($block['innerBlocks'] as $index => $child) {
            if (($child['blockName'] ?? '') !== 'wtf/tsu-text' || ($child['attrs']['tag'] ?? 'p') !== 'p') { continue; }
            $text = wtf_faction_text_key($child['attrs']['content'] ?? '');
            $duplicate = strlen($text) > 60 && strpos($intro, $text) !== false;
            // W Reef powielono ten sam akapit, zmieniając tylko dwa słowa otwarcia.
            if (strpos($key, 'reef:') === 0 && strpos($text, 'Powoli. Sukcesywnie. Bez skrupułów.') === 0) { $duplicate = true; }
            if (strpos($key, 'fireborn:') === 0 && strpos($text, 'Jaja ukryte przez samego Tsukuyumi') === 0) { $duplicate = true; }
            if (strpos($key, 'fg:') === 0 && strpos($text, 'Potomkowie First Guardians muszą odnaleźć') === 0) { $duplicate = true; }
            if (strpos($key, 'oni:') === 0 && strpos($text, 'ONI się przebudzili.') === 0) {
                $start = strpos($child['attrs']['content'], 'Starożytne kroniki');
                if ($start !== false) {
                    $block['innerBlocks'][$index]['attrs']['content'] = substr($child['attrs']['content'], $start);
                    $removed[] = 'Powielone dwa zdania zajawki ONI; dalsza historia zachowana.';
                    continue;
                }
                $duplicate = true;
            }
            if (strpos($key, 'cos:') === 0 && strpos($text, 'Wraz z rozszerzeniem Sunrise Kingdom') === 0 && strpos($text, 'Ci rdzenni mieszkańcy') !== false) {
                $html = $child['attrs']['content'];
                $firstEnd = strpos($html, 'Australia.');
                $newStart = strpos($html, 'Sama ');
                if ($firstEnd !== false && $newStart !== false) {
                    $block['innerBlocks'][$index]['attrs']['content'] = substr($html, 0, $firstEnd + strlen('Australia.')) . ' ' . substr($html, $newStart);
                    $removed[] = 'Powielona część zajawki Circle of the Sun; zachowano wprowadzenie do Sunrise Kingdom i opis Gospel Bearer.';
                }
                continue;
            }
            if (!$duplicate) { continue; }
            $removed[] = $text;
            unset($block['innerBlocks'][$index]);
        }
        if ($removed) {
            $block['innerBlocks'] = array_values($block['innerBlocks']);
            foreach ($block['innerBlocks'] as $index => $child) {
                if (($child['attrs']['tag'] ?? '') !== 'h3' || trim(wp_strip_all_tags($child['attrs']['content'] ?? '')) !== 'FABULARNIE') { continue; }
                $next = $block['innerBlocks'][$index + 1] ?? null;
                if (!$next || ($next['attrs']['tag'] ?? '') === 'h3') { unset($block['innerBlocks'][$index]); }
            }
            $block['innerBlocks'] = array_values($block['innerBlocks']);
            $block['innerContent'] = array_fill(0, count($block['innerBlocks']), null);
        }
        return;
    }
    foreach ($block['innerBlocks'] as &$child) { wtf_faction_remove_intro($child, $intro, $key, $removed); }
}
$pages = get_posts(['post_type'=>'page','post_status'=>'any','numberposts'=>-1,'meta_key'=>'_wtf_tsu_key','suppress_filters'=>true]);
foreach ($pages as $page) {
    $key = get_post_meta($page->ID, '_wtf_tsu_key', true);
    if (strpos($key, 'main:') === 0) { continue; }
    $blocks = parse_blocks($page->post_content);
    if ($apply && ($args[1] ?? '') === 'repair-oni' && strpos($key, 'oni:') === 0 && strpos($page->post_content, 'Starożytne kroniki') === false) {
        $recovered = null;
        $find = function ($items) use (&$find, &$recovered) {
            foreach ($items as $item) {
                $content = $item['attrs']['content'] ?? '';
                $start = strpos($content, 'Starożytne kroniki');
                if (($item['blockName'] ?? '') === 'wtf/tsu-text' && $start !== false) {
                    $recovered = $item;
                    $recovered['attrs']['content'] = substr($content, $start);
                    return;
                }
                $find($item['innerBlocks']);
            }
        };
        foreach (wp_get_post_revisions($page->ID, ['posts_per_page'=>10]) as $revision) {
            $find(parse_blocks($revision->post_content));
            if ($recovered) { break; }
        }
        if (!$recovered) { throw new RuntimeException('Nie odnaleziono historii ONI w wersjach wpisu'); }
        $insert = function (&$items) use (&$insert, $recovered) {
            foreach ($items as &$item) {
                if (strpos($item['attrs']['attributes']['class'] ?? '', 'tsu-faction-description') !== false) {
                    $position = 0;
                    foreach ($item['innerBlocks'] as $i=>$child) {
                        if (trim(wp_strip_all_tags($child['attrs']['content'] ?? '')) === 'FABULARNIE') { $position = $i+1; break; }
                    }
                    array_splice($item['innerBlocks'], $position, 0, [$recovered]);
                    $item['innerContent'] = array_fill(0,count($item['innerBlocks']),null);
                } else { $insert($item['innerBlocks']); }
            }
        };
        $insert($blocks);
        $result = wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
        if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
        echo "Restored unique ONI history: {$page->ID}\n";
    }
    $intro = wtf_faction_find_intro($blocks);
    $removed = [];
    foreach ($blocks as &$block) { wtf_faction_remove_intro($block, $intro, $key, $removed); }
    unset($block);
    if (!$removed) { continue; }
    echo wp_json_encode(['page'=>$page->ID,'key'=>$key,'removed'=>$removed], JSON_UNESCAPED_UNICODE) . "\n";
    if ($apply) {
        $result = wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash(serialize_blocks($blocks))], true);
        if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
    }
}
