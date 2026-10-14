<?php
if (!defined('ABSPATH')) { exit; }
function wtf_mc_correct_text($html) {
    $html = str_replace([
        'brykach, niestrudzenie szukają',
        'mogą posiąść nadludzką siłę, która pozwala im wykonać dodatkowe akcje walki.',
        'Poziom: średnia',
        'darmowa akcja walki <em>Uberclown</em>',
        "sieją chaos<em><br>\n</em>",
        'Zwiększony zasięg twoich pojazdów i zdolność do wystawiania jednostek w dowolnym obszarze przy krawędzi, a także darmowe akcje walki dzięki <em>Uberclownom </em>sprawiają, że jesteś bardzo nieprzewidywalną siłą.',
        "wprowadzają\u{00a0} jeszcze", 'wprowadzają  jeszcze',
        'Wybieraj karty akcji z efektem frakcji i punktami produkcji, utrzymasz w ten sposób stały napływ <em>Uberklaunów</em>.',
        'ZAWARTOŚĆ ', '2 karty zwycięstwa (tryb gry 2-osobowej),',
        'Nazwa frakcji oraz nazwy jednostek zostały zachowane jak w oryginale i są w języku angielskim.',
    ], [
        'brykach i niestrudzenie szukają',
        'mogą zyskać nadludzką siłę, która pozwala im wykonywać dodatkowe akcje walki.',
        'Poziom trudności: średni',
        'darmowa akcja walki jednostek <em>Uberclowns</em>',
        'sianie chaosu',
        'Zwiększony zasięg twoich pojazdów, możliwość wystawiania jednostek w dowolnym obszarze przy krawędzi planszy oraz darmowe akcje walki jednostek <em>Uberclowns</em> czynią twoją frakcję nieprzewidywalną.',
        'wprowadzają jeszcze', 'wprowadzają jeszcze',
        'Wybieraj karty akcji z efektem frakcji i punktami produkcji. Dzięki temu utrzymasz stały napływ jednostek <em>Uberclowns</em>.',
        'ZAWARTOŚĆ', '2 karty zwycięstwa (tryb dwuosobowy),',
        'Nazwę frakcji oraz nazwy jednostek zachowano w oryginalnej, angielskiej wersji.',
    ], $html);
    $parts = preg_split('/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $index => &$part) {
        if ($index % 2 === 0) {
            $part = preg_replace('/(?<![\p{L}\p{N}_])([aAiIoOuUwWzZ])(?:\s|&nbsp;)+/u', '$1' . "\u{00a0}", $part);
        }
    }
    return implode('', $parts);
}
function wtf_mc_correct_blocks(&$blocks) {
    foreach ($blocks as &$block) {
        if (($block['blockName'] ?? '') === 'wtf/tsu-text') {
            $block['attrs']['content'] = wtf_mc_correct_text($block['attrs']['content'] ?? '');
        }
        wtf_mc_correct_blocks($block['innerBlocks']);
    }
}
foreach (['pl','en'] as $language) {
    $pages = get_posts(['post_type'=>'page','post_status'=>'any','numberposts'=>1,'meta_key'=>'_wtf_tsu_key','meta_value'=>'moon-circus:'.$language,'suppress_filters'=>true]);
    if (!$pages) { throw new RuntimeException('Brak Moon Circus ' . $language); }
    $page = $pages[0];
    $blocks = parse_blocks($page->post_content);
    wtf_mc_correct_blocks($blocks);
    $updated = serialize_blocks($blocks);
    if ($updated === $page->post_content) { echo "Unchanged {$page->ID}\n"; continue; }
    $result = wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash($updated)], true);
    if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
    echo "Updated {$page->ID}: $language\n";
}
