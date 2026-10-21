<?php
// Opisy przygotowane po obejrzeniu wszystkich ośmiu fotografii.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$data=[
1=>['Jednostki na planszy','Pięć kartonowych jednostek Władców Utraconych Mórz na heksagonalnej planszy Tsukuyumi.','Zbliżenie na jednostki frakcji ustawione w przezroczystych podstawkach na planszy z fioletowymi znacznikami terytorium. W centrum znajduje się jasny wieloryb, a po bokach mniejsze i większe jednostki.'],
2=>['Armia wielorybów na planszy','Liczne jednostki wielorybów otaczające fioletowe znaczniki terytorium na planszy Tsukuyumi.','Szersze ujęcie armii Władców Utraconych Mórz. Kartonowe jednostki różnej wielkości stoją na sąsiadujących heksach planszy.'],
3=>['Oskar wśród wielorybów','Czerwona ośmiornica Oskar w przezroczystej podstawce pomiędzy jednostkami wielorybów.','Zbliżenie na jednostkę Oskara w otoczeniu wielorybów Władców Utraconych Mórz. W tle i po bokach widać pozostałe jednostki oraz fioletowe znaczniki na planszy Tsukuyumi.'],
4=>['Król i jednostki przy planszetce','Jednostki Króla, Cielaka i jasnego wieloryba za planszetką Władców Utraconych Mórz.','Trzy kartonowe jednostki ustawione za planszetką frakcji do gry Tsukuyumi. Na pierwszym planie widać ilustrację wielorybów oraz fragment zasad Relokacji.'],
5=>['Planszetka i znaczniki frakcji','Planszetka Władców Utraconych Mórz otoczona kartonowymi wielorybami i trójkątnymi znacznikami.','Ujęcie z góry przedstawiające elementy frakcji do gry Tsukuyumi: planszetkę, jednostki oraz fioletowe znaczniki z symbolem ogona i korony. Na planszetce widoczna jest zdolność Relokacja.'],
6=>['Woreczek i elementy frakcji','Płócienny woreczek z symbolem ogona i korony, otoczony jednostkami i znacznikami Władców Utraconych Mórz.','Fotografia elementów dodatku do Tsukuyumi ułożonych wokół płóciennego woreczka. Widać kartonowe wieloryby, czerwoną ośmiornicę Oskara i trójkątne znaczniki frakcji.'],
7=>['Efekt frakcji na planszetce','Zbliżenie na pole Efekt frakcji na planszetce Władców Utraconych Mórz i fioletowe znaczniki obok.','Fragment planszetki frakcji do gry Tsukuyumi z polem Efekt frakcji oraz zasadami Potęgi Natury, Oczyszczenia i Relokacji. Po lewej leżą trójkątne znaczniki z symbolem frakcji.'],
8=>['Planszetka z armią w tle','Planszetka Władców Utraconych Mórz z jednostkami wielorybów i Oskarem ustawionymi w tle.','Widok planszetki frakcji do gry Tsukuyumi z ilustracją wielorybów, Relokacją i celem Oczyszczony Raj. Za planszetką stoją kartonowe jednostki, a po prawej widoczny jest Oskar.'],
];
$alts=[];
foreach($data as $n=>[$title,$alt,$description]) {
    $items=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>2,'meta_key'=>'_wtf_source_asset','meta_value'=>'media/tsu-wum-WUM_'.$n.'.jpg','suppress_filters'=>true]);
    if(count($items)!==1) { WP_CLI::error('Niejednoznaczne zdjęcie '.$n); }
    $id=$items[0]->ID;
    $old=['title'=>$items[0]->post_title,'caption'=>$items[0]->post_excerpt,'description'=>$items[0]->post_content,'alt'=>get_post_meta($id,'_wp_attachment_image_alt',true)];
    if (!metadata_exists('post',$id,'_wtf_wum_before_descriptions')) { update_post_meta($id,'_wtf_wum_before_descriptions',$old); }
    $result=wp_update_post(['ID'=>$id,'post_title'=>wp_slash('Tsukuyumi — Władcy Utraconych Mórz — '.$title),'post_excerpt'=>wp_slash($title.'. Fot. @photoboardgamer.'),'post_content'=>wp_slash($description.' Fot. @photoboardgamer.')],true);
    if(is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
    update_post_meta($id,'_wp_attachment_image_alt',$alt);
    $term=get_term_by('name','Tsukuyumi','wtf_media_group');
    if (!$term) { WP_CLI::error('Brak grupy Tsukuyumi.'); }
    wp_set_object_terms($id,[(int)$term->term_id],'wtf_media_group',true);
    $alts[$id]=$alt;
}
$page=get_post(82);
if (!$page || get_post_meta(82,'_wtf_tsu_key',true)!=='wum:pl') { WP_CLI::error('Nie znaleziono strony WUM.'); }
$count=0;
$walk=function($blocks) use (&$walk,&$count,$alts) {
    foreach($blocks as &$block) {
        $id=$block['attrs']['imageId']??0;
        if($block['blockName']==='wtf/tsu-image' && isset($alts[$id])) { $block['attrs']['alt']=$alts[$id]; $count++; }
        if($block['innerBlocks']) { $block['innerBlocks']=$walk($block['innerBlocks']); }
    }
    return $blocks;
};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if($count!==8) { WP_CLI::error('Oczekiwano 8 zdjęć w galerii, znaleziono '.$count); }
if (!metadata_exists('post',82,'_wtf_wum_before_gallery_alts')) { update_post_meta(82,'_wtf_wum_before_gallery_alts',$page->post_content); }
$result=wp_update_post(['ID'=>82,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
WP_CLI::success('Poprawiono tytuły, ALT, podpisy i opisy 8 zdjęć; zsynchronizowano ALT w galerii.');
