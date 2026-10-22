<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(927);
if(!$page || get_post_meta(927,'_wtf_ele_key',true)!=='minstrel:pl')WP_CLI::error('Brak Minstrela.');
if(get_post_meta(927,'_wtf_minstrel_proofread',true)){WP_CLI::success('Korekta już zapisana.');return;}
$changes=[
'Dodatek do gry Elekt osnuty'=>'Dodatek do gry Elekt osnuty wokół mrocznych melodii. Zwerbuj Minstrela, który przywoła na pole bitwy zaklęte pieśni. Uważaj jednak — jeśli polegnie w boju, ucichną także jego nuty. Dodatek wprowadza nowy typ kart — Melodie, których zdolności działają we wszystkich Strefach.',
'Dodatek jest częścią albumu'=>'Dodatek jest częścią albumu <a href="'.esc_url(home_url('/echo-swiatow/')).'">„Echo Światów”</a>.',
'<strong>Dodatek dostępny tylko'=>'<strong>Dodatek jest dostępny wyłącznie z albumem „Echo Światów”.</strong>',
'<em>Widmo śmierci Cesarza'=>'<em>Widmo śmierci Cesarza przyciągnęło do stolicy wielu tajemniczych i podejrzanych gości. Wśród nich pojawili się Minstrele, którzy wypełniają ulice posępnym, złowróżbnym brzmieniem mrocznych melodii. Czy przybyli, by zapowiedzieć odejście władcy, czy po to, by grać do jego ostatniego tchu? Jedno jest pewne — ich nuty niosą w sobie potężną moc, którą chce zawładnąć każdy z arystokratycznych domów.</em>',
'<em>Ty również jej pożądasz'=>'<em>Ty również jej pożądasz. Moc melodii trwa, dopóki Minstrel gra…</em>',
'16 kart: Jednostki Podstawowe'=>'16 kart: Jednostki Podstawowe Minstrel oraz cztery zestawy Melodii. Każdy zestaw zawiera po jednej kopii każdego rodzaju Melodii.',
'Dodatek wprowadza nową Jednostkę Podstawową'=>'Dodatek wprowadza nową Jednostkę Podstawową — <strong>Minstrela — oraz cztery zestawy Melodii</strong>. Karty Melodii są oznaczone nutką w lewym górnym rogu. Zasady dodatku obowiązują, jeśli jedną z Jednostek Podstawowych używanych w waszej rozgrywce jest Minstrel. Każdy gracz otrzymuje wówczas zestaw Melodii zawierający po jednej kopii każdego ich rodzaju.',
'<strong>Karty Melodii</strong>'=>'<strong>Karty Melodii</strong> nie są Jednostkami — stanowią nowy typ kart. Dopóki nie zostaną zagrane, leżą przed graczem w stosie, rewersem do góry. Melodia może wejść do gry dzięki zdolności Minstrela. Melodie nigdy nie trafiają na rękę gracza ani nie są przydzielane do Stref. Odrzucona Melodia wraca na stos gracza.',
'Słowa kluczowe <strong>zagraj Melodię'=>'Słowa kluczowe <strong>zagraj Melodię</strong> oznaczają, że gracz wybiera jedną ze swoich niezagranych Melodii i wykłada ją przed sobą, awersem do góry, w miejscu widocznym dla wszystkich graczy. Zagrane Melodie są jawne. Melodia zapewnia graczowi, który ją zagrał, specjalną zdolność <strong>obowiązującą we wszystkich Strefach</strong>.',
'Gdy <strong>Minstrel</strong>'=>'Gdy <strong>Minstrel</strong> zostaje unicestwiony, wszystkie Melodie zagrane przez jego właściciela wracają na stos — nawet jeśli Minstrel zostanie ocalony. Melodie wracają na stos również wtedy, gdy Minstrel zostaje odrzucony ze Strefy lub z ręki gracza.',
'Na końcu <strong>fazy zakończenia rundy'=>'Na końcu <strong>fazy zakończenia rundy</strong> każdy gracz, który ma co najmniej trzy zagrane Melodie, może ogłosić, że układa <strong>Pieśń Zwycięstwa</strong>. Zdobywa w ten sposób 2 Tytuły. Aby je oznaczyć, wykłada swojego Minstrela przed sobą rewersem do góry. Następnie umieszcza pod nim wszystkie swoje Melodie tak, aby widoczne były nazwy dwóch dowolnie wybranych kart. Po ułożeniu Pieśni gracz nie może już używać Minstrela ani Melodii do końca gry. Zdobytych w ten sposób Tytułów nie można w żaden sposób utracić.',
];
$seen=[];
$walk=function($blocks)use(&$walk,$changes,&$seen){foreach($blocks as &$b){
 if($b['blockName']==='wtf/ele-text')foreach($changes as $prefix=>$new){if(str_starts_with($b['attrs']['content']??'',$prefix)){
  if(isset($seen[$prefix]))WP_CLI::error('Powtórzony akapit: '.$prefix);
  $b['attrs']['content']=$new;$seen[$prefix]=true;break;
 }}
 if($b['innerBlocks'])$b['innerBlocks']=$walk($b['innerBlocks']);
}return $blocks;};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if(count($seen)!==count($changes))WP_CLI::error('Nie znaleziono wszystkich akapitów.');
update_post_meta(927,'_wtf_before_minstrel_proofread',$page->post_content);
$saved=wp_update_post(['ID'=>927,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(927,'_wtf_minstrel_proofread',true);
WP_CLI::success('Korekta 11 akapitów zapisana.');
