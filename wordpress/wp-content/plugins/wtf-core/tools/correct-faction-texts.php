<?php
// Ręcznie zatwierdzone błędy z aktualnych opisów; bez zmian zasad i liczb.
if (!defined('ABSPATH')) { exit; }
function wtf_faction_proofread($html) {
    $html = str_replace(["\u{00a0}", '&nbsp;'], ' ', $html);
    if (class_exists('Normalizer')) { $html = Normalizer::normalize($html, Normalizer::FORM_C); }
    $html = preg_replace('/(<em>Fireborn<\/em>),([ ]*przeciwnicy)/u', '$1$2', $html);
    $html = preg_replace('/(<em>Nomadami<\/em>)([ ]+należy)/u', '$1,$2', $html);
    $html = preg_replace('/(<(?:strong|em)>Żarłoczny Rój<\/(?:strong|em)>)([ ]+)opierają/u', '$1$2opiera', $html);
    if (strpos(wp_strip_all_tags($html), 'Wystawiona na planszy Hodowca oraz Siewca przynosi') !== false) {
        $html = str_replace(['Wystawiona na planszy', 'przynosi dodatkowy punkt produkcji'], ['Wystawieni na planszy', 'przynoszą dodatkowy punkt produkcji'], $html);
    }
    $changes = [
        '5 karty walki'=>'5 kart walki', '22 żetonów zwycięstwa'=>'22 żetony zwycięstwa',
        ', czy bonus za kontrolowanie'=>' czy bonus za kontrolowanie',
        ', ten bonus'=>'. Ten bonus', ', sprawiło'=>' – sprawiło',
        'zadawaniem'=>'zadaniem', 'W każdej rundzie, '=>'W każdej rundzie ',
        'jest jednostka latającą'=>'jest jednostką latającą',
        'ma najwyższy priorytet'=>'mają najwyższy priorytet',
        ', może wręcz zalać'=>' może wręcz zalać',
        'Księżycu by zabić'=>'Księżycu, by zabić',
        'w przypadku posiadania jedynie wersji podstawowej gry Tsukuyumi, maksymalna'=>'w przypadku posiadania jedynie wersji podstawowej gry Tsukuyumi maksymalna',
        'Poziom: trudna'=>'Poziom: trudny', 'Poziom: łatwa'=>'Poziom: łatwy',
        'Związku z powyższym'=>'W związku z powyższym',
        'własnego efektu obrony, ten bonus'=>'własnego efektu obrony. Ten bonus',
        'wystarczająco dużo z pewnością'=>'wystarczająco dużo, z pewnością',
        'zmusisz go zaatakowania'=>'zmusisz go do zaatakowania',
        'zdolnościami dzięki którym'=>'zdolnościami, dzięki którym',
        'wyruszyły w podróż szukając'=>'wyruszyły w podróż, szukając',
        'głęboko pod ziemią, przeleżały'=>'głęboko pod ziemią przeleżały',
        'Na długo przed erą ludzkości, władcami'=>'Na długo przed erą ludzkości władcami',
        'Tsukuyumi, sprawiło'=>'Tsukuyumi – sprawiło',
        'za eliminację Fireborn, przeciwnicy'=>'za eliminację Fireborn przeciwnicy',
        'mimo olbrzymiego ryzyka, dążyć'=>'mimo olbrzymiego ryzyka dążyć',
        'tej wydawałoby się neutralnej'=>'tej, wydawałoby się, neutralnej',
        'swoich ofiar unosząc'=>'swoich ofiar, unosząc', 'z swoim krukiem'=>'ze swoim krukiem',
        'Biada każdemu kto'=>'Biada każdemu, kto', 'z cienia prowadzą'=>'z cienia, prowadzą',
        'z wiedzą co'=>'z wiedzą, co', 'w ich rękach bo'=>'w ich rękach, bo',
        'eliminacja Fireborn, czy bonus'=>'eliminacja Fireborn czy bonus',
        'bądź pewien że'=>'bądź pewien, że', 'zakończysz grę kontrolując'=>'zakończysz grę, kontrolując',
        'z mobilnymi jednostkami zastanów'=>'z mobilnymi jednostkami, zastanów',
        'nas użyciem'=>'nad użyciem', 'Kontroluje czas.'=>'Kontrolujesz czas.',
        'po planszy, zamiast ruchu'=>'po planszy; zamiast ruchu',
        'w trzej wersjach'=>'w trzech wersjach', 'można znaleźć się'=>'można znaleźć',
        'obszarów więcej niż wynika'=>'obszarów więcej, niż wynika',
        'pieśni zataczając'=>'pieśni, zataczając', 'Formacje, które tworzą dają'=>'Formacje, które tworzą, dają',
        'w miejsce gdzie'=>'w miejsce, gdzie',
        'określonych obszarach, nie musisz'=>'określonych obszarach; nie musisz',
        'swoich jednostek, możesz je'=>'swoich jednostek. Możesz je',
        'wieloorganizmowy twór, pochłonie'=>'wieloorganizmowy twór pochłonie',
        'za wyjątkiem maskotki'=>'z wyjątkiem maskotki', 'rośniesz rozkładając'=>'rośniesz, rozkładając',
        'zdominujesz obszar jeżeli'=>'zdominujesz obszar, jeżeli',
        'odpowiednią ilość znaczników'=>'odpowiednią liczbę znaczników',
        'przez ciebie, tak długo jak'=>'przez ciebie tak długo, jak',
        'są masywne ich'=>'są masywne, ich', 'Jade Guardian zagrywając'=>'Jade Guardian, zagrywając',
        'stawią się by'=>'stawią się, by', 'Wezwani, stanęli'=>'Wezwani stanęli',
        'przybyły by stawić'=>'przybyły, by stawić',
        'nie tyle w obronie ludzkości co'=>'nie tyle w obronie ludzkości, co',
        'Na początku ruchu Sentinels, jednostki'=>'Na początku ruchu Sentinels jednostki',
        'zadecydować ile'=>'zadecydować, ile',
        'a co za tym idzie nie mogą podbijać'=>'a co za tym idzie, nie może podbijać',
        'W momencie kiedy'=>'W momencie, kiedy',
        'od ilości zdobytych'=>'od liczby zdobytych', 'Minimalna ilość graczy'=>'Minimalna liczba graczy',
        'zadawaniem Kampfgruppe'=>'zadaniem Kampfgruppe',
        'ocalałych, nie chroniły'=>'ocalałych nie chroniły',
        'z jądra Księżyca, ulepszyli'=>'z jądra Księżyca ulepszyli',
        'na krucjatę aby'=>'na krucjatę, aby', 'wszystko co stanie'=>'wszystko, co stanie',
        'większą mobilność'=>'większą mobilnością',
        'W każdej rundzie, Dark Crusade'=>'W każdej rundzie Dark Crusade',
        'przeciwników jak również'=>'przeciwników, jak również',
        'które poza potężnymi obrażeniami, powoduje'=>'która poza potężnymi obrażeniami powoduje',
        'na inny z własną jednostką'=>'na inny obszar z własną jednostką',
        'jak również zadanie obrażeń'=>'jak również na zadanie obrażeń',
        'nie do powstrzymania a może'=>'nie do powstrzymania, a może',
        'na glinianych nogach ..'=>'na glinianych nogach?',
        'przetrwanie weszli'=>'przetrwanie, weszli', 'Mimo, że'=>'Mimo że',
        'Jako potomkowie tych, którzy obudzili Białego Smoka, dzieje Cybersamurajów są'=>'Dzieje Cybersamurajów, potomków tych, którzy obudzili Białego Smoka, są',
        'Księżyc uderzył w ziemię'=>'Księżyc uderzył w Ziemię',
        'patrolują okolice próbując'=>'patrolują okolice, próbując',
        'wszystkiego co wiąże'=>'wszystkiego, co wiąże', 'jednostek można zostać'=>'jednostek może zostać',
        'Grając Nomadami należy'=>'Grając Nomadami, należy', 'mobilność by zaskakiwać'=>'mobilność, by zaskakiwać',
        'Istniaje'=>'Istnieje', 'Żarłoczny Rój opierają'=>'Żarłoczny Rój opiera',
        'siłą z którą'=>'siłą, z którą', 'Cechy specyficzne frakcji, pozwalają'=>'Cechy specyficzne frakcji pozwalają',
        'Osa jest jednostka'=>'Osa jest jednostką',
        'Wystawiona na planszy Hodowca oraz Siewca przynosi'=>'Wystawieni na planszy Hodowca oraz Siewca przynoszą',
        'podczas efektu frakcji, daje'=>'podczas efektu frakcji daje',
        'wyruszyła by położyć'=>'wyruszyła, by położyć',
        'Zakonu Marduk,'=>'Zakonu Marduka,', 'uznawani za gwarant władzy'=>'uznawani za gwarantów władzy',
        'Wykorzystując wszystkie posiadane zasoby ostatnich'=>'Wykorzystując wszystkie posiadane zasoby, ostatnich',
        'kombinezony bojowe ruszyło'=>'kombinezony bojowe, ruszyło',
        'kombinezony bojowe, zostało podzielonych'=>'kombinezony bojowe zostało podzielonych',
        'eliminacja Tsukuyumi ma'=>'eliminacja Tsukuyumi mają',
        'wygrywa grę skutecznie eliminując'=>'wygrywa grę, skutecznie eliminując',
        'nielicznym ale silnym'=>'nielicznym, ale silnym',
        'strzałów snajperskich, pozwoli'=>'strzałów snajperskich pozwolą',
        'Kampfruppe'=>'Kampfgruppe',
    ];
    // Poprawki dotyczą tekstu, a nie adresów, identyfikatorów i atrybutów HTML.
    $parts = preg_split('/(<[^>]+>)/u', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as $index => &$part) {
        if ($index % 2 !== 0) { continue; }
        $part = strtr($part, $changes);
        $part = preg_replace('/[ \t]{2,}/u', ' ', $part);
        $part = preg_replace('/(?<![\p{L}\p{N}_])([aAiIoOuUwWzZ])\s+/u', '$1' . "\u{00a0}", $part);
    }
    return implode('', $parts);
}
function wtf_faction_proofread_blocks(&$blocks) {
    foreach ($blocks as &$block) {
        if (($block['blockName'] ?? '') === 'wtf/tsu-text') {
            $block['attrs']['content'] = wtf_faction_proofread($block['attrs']['content'] ?? '');
        } elseif (($block['blockName'] ?? '') === 'core/html') {
            foreach ($block['innerContent'] as &$content) { if (is_string($content)) { $content = wtf_faction_proofread($content); } }
            $block['innerHTML'] = wtf_faction_proofread($block['innerHTML']);
        }
        wtf_faction_proofread_blocks($block['innerBlocks']);
    }
}
function wtf_faction_contents_columns(&$block) {
    if (strpos($block['attrs']['attributes']['class'] ?? '', 'tsu-faction-contents') !== false) { return; }
    $contents = false;
    foreach ($block['innerBlocks'] as &$child) {
        $heading = trim(wp_strip_all_tags($child['attrs']['content'] ?? ''));
        if (($child['attrs']['tag'] ?? '') === 'h3') { $contents = strpos($heading, 'ZAWARTOŚĆ') === 0; }
        if ($contents && ($child['attrs']['tag'] ?? '') === 'ul' && count($child['innerBlocks']) >= 8) {
            $classes = $child['attrs']['attributes']['class'] ?? '';
            if (strpos($classes, 'tsu-contents-list') === false) { $child['attrs']['attributes']['class'] = trim($classes . ' tsu-contents-list'); }
            foreach ($child['innerBlocks'] as $index => &$item) {
                if (($item['blockName'] ?? '') !== 'wtf/tsu-text') { continue; }
                $text = rtrim($item['attrs']['content'] ?? '');
                if (!preg_match('/[,.;:]$/u', wp_strip_all_tags($text))) {
                    $item['attrs']['content'] = $text . ($index === count($child['innerBlocks']) - 1 ? '.' : ',');
                }
            }
            unset($item);
        }
        wtf_faction_contents_columns($child);
    }
}
$pages = get_posts(['post_type'=>'page','post_status'=>'any','numberposts'=>-1,'meta_key'=>'_wtf_tsu_key','suppress_filters'=>true]);
$changed = 0;
foreach ($pages as $page) {
    $key = get_post_meta($page->ID, '_wtf_tsu_key', true);
    if (strpos($key, 'main:') === 0) { continue; }
    $blocks = parse_blocks($page->post_content);
    wtf_faction_proofread_blocks($blocks);
    foreach ($blocks as &$block) { wtf_faction_contents_columns($block); }
    unset($block);
    $content = serialize_blocks($blocks);
    if ($content === $page->post_content) { continue; }
    $result = wp_update_post(['ID'=>$page->ID,'post_content'=>wp_slash($content)], true);
    if (is_wp_error($result)) { throw new RuntimeException($result->get_error_message()); }
    echo "Updated {$page->ID}: $key\n";
    $changed++;
}
echo "Changed pages: $changed\n";
