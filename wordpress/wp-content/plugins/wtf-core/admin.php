<?php
if (!defined('ABSPATH')) { exit; }

add_action('admin_menu', function () {
    add_menu_page('WTF Core', 'WTF Core', 'manage_options', 'wtf-core', 'wtf_core_settings_screen', 'dashicons-admin-generic', 25);
    add_menu_page('Strony PL/EN', 'Strony PL/EN', 'edit_pages', 'wtf-pages', 'wtf_core_pages_screen', 'dashicons-translation', 21);
});

function wtf_core_settings_screen() {
    if (!current_user_can('manage_options')) { return; }
    ?>
    <div class="wrap"><h1>WTF Core</h1><p>Własne funkcje strony WTF. Motyw odpowiada za wygląd; ta wtyczka dostarcza bloki i integracje.</p>
    <p>Menu „Strony” otwiera standardową listę WordPressa. Osobne menu „Strony PL/EN” pokazuje te same strony w rozwijanych grupach gier, z połączonymi wersjami PL/EN.</p>
    <h2>Co robi wtyczka</h2><ul style="list-style:disc;padding-left:22px">
        <li>Udostępnia edytowalne bloki banera, kart gier, promocji, wydawnictwa i zaproszenia do sklepu.</li>
        <li>Udostępnia edytowalne bloki nagłówka i stopki.</li>
        <li>Udostępnia bloki układu, tekstów, grafik i filmów Tsukuyumi oraz ręcznie uruchamiany import prototypu.</li>
        <li>Dodaje wielokrotne grupy Mediów oraz filtry biblioteki i wyboru grafiki.</li><li>Renderuje bloki z treści zapisanych w WordPressie.</li>
        <li>Wyświetla grafiki z biblioteki w odpowiednich rozmiarach (srcset/sizes); konwersję do WebP wykonuje osobna wtyczka Modern Image Formats.</li>
        <li>Rejestruje wspólne napisy do tłumaczenia w panelu Polylang.</li>
        <li>Zawiera skrypty pierwszej inicjalizacji mediów i głównej PL/EN, uruchamiane przez WP-CLI.</li>
        <li>Przygotowuje punkt integracji przyszłych modułów React.</li>
        <li>Udostępnia listę stron PL/EN w grupach gier, z filtrem, wyszukiwaniem i ręcznym przypisaniem w edytorze.</li>
    </ul><p>Nie obsługuje płatności, walut ani zakupów. Dokumentacja: <code>wtfDoc/wtf-core.md</code>.</p></div>
    <?php
}

function wtf_core_page_groups() {
    $posts=get_posts(['post_type'=>'page','post_status'=>['publish','draft','pending','future','private'],'posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC','suppress_filters'=>true,'lang'=>'','perm'=>'readable']);
    $groups=[];
    foreach ($posts as $post) {
        if (!current_user_can('edit_post', $post->ID)) { continue; }
        $translations=pll_get_post_translations($post->ID);
        $ids=array_values($translations);$ids[]=$post->ID;
        $key=min(array_map('intval', $ids));
        if (isset($groups[$key])) { continue; }
        $versions=[];
        foreach (['pl','en'] as $lang) {
            $id=(int)($translations[$lang]??0);
            if (!$id && pll_get_post_language($post->ID)===$lang) { $id=$post->ID; }
            $version=$id?get_post($id):null;
            if ($version && $version->post_type==='page' && !in_array($version->post_status,['trash','auto-draft'],true) && current_user_can('edit_post',$id)) { $versions[$lang]=$version; }
        }
        $primary=$versions['pl']??$versions['en']??$post;
        $groups[$key]=['title'=>$primary->post_title?:'(bez tytułu)','primary'=>$primary,'versions'=>$versions];
    }
    uasort($groups,fn($a,$b)=>strnatcasecmp($a['title'],$b['title']));
    return array_values($groups);
}

require_once __DIR__.'/pages-admin.php';
