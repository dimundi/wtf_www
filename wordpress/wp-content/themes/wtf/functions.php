<?php
add_filter('body_class', function ($classes) {
    if (is_page() && get_post_meta(get_queried_object_id(), '_wtf_tsu_kind', true)) { $classes[] = 'tsukuyumi-theme'; }
    return $classes;
});
add_action('wp_enqueue_scripts', function () {
    if (!is_page() || !get_post_meta(get_queried_object_id(), '_wtf_tsu_kind', true)) { return; }
    wp_enqueue_style('wtf-tsukuyumi', get_theme_file_uri('assets/tsukuyumi.css'), ['wtf-blocks','wtf-mobile-navigation'], filemtime(get_theme_file_path('assets/tsukuyumi.css')));
    wp_enqueue_script('wtf-tsukuyumi', get_theme_file_uri('assets/tsukuyumi.js'), ['wtf-site'], filemtime(get_theme_file_path('assets/tsukuyumi.js')), true);
});
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_editor_style(['assets/site.css', 'assets/opening.css', 'assets/mobile-navigation.css', 'assets/blocks.css']);
});
add_action('wp_enqueue_scripts', function () {
    foreach (['site', 'opening', 'mobile-navigation', 'blocks'] as $file) {
        wp_enqueue_style('wtf-' . $file, get_theme_file_uri('assets/' . $file . '.css'), [], filemtime(get_theme_file_path('assets/' . $file . '.css')));
    }
    wp_enqueue_script('wtf-site', get_theme_file_uri('assets/site.js'), [], filemtime(get_theme_file_path('assets/site.js')), true);
});
// Oba logo są plikami media, dostępne niezależnie od aktualnej podstrony.
add_action('wp_head', function () {
    $url = wp_upload_dir()['baseurl'] . '/media/wtf-logo-navy-green.svg';
    echo '<style>:root[data-theme=light] .brand-svg{background-image:url(' . esc_url($url) . ')}</style>';
    echo '<script>try{document.documentElement.dataset.theme=localStorage.getItem("wtf-theme")||"dark"}catch(e){document.documentElement.dataset.theme="dark"}</script>';
}, 1);

add_filter('body_class', function ($classes) {
    if (is_page() && ($kind=get_post_meta(get_queried_object_id(), '_wtf_ele_kind', true))) { $classes[]='elekt-theme';if($kind==='expansion')$classes[]='elekt-expansion-theme'; }
    return $classes;
});
add_action('wp_enqueue_scripts', function () {
    if (!is_page() || !get_post_meta(get_queried_object_id(), '_wtf_ele_kind', true)) return;
    wp_enqueue_style('wtf-elekt',get_theme_file_uri('assets/elekt.css'),['wtf-blocks','wtf-mobile-navigation'],filemtime(get_theme_file_path('assets/elekt.css')));
    wp_enqueue_script('wtf-elekt',get_theme_file_uri('assets/elekt.js'),['wtf-site'],filemtime(get_theme_file_path('assets/elekt.js')),true);
});

add_filter('body_class',function($classes){if(is_page()&&get_post_meta(get_queried_object_id(),'_wtf_echo_kind',true))$classes[]='echo-theme';return $classes;});
add_action('wp_enqueue_scripts',function(){
    if(!is_page()||!get_post_meta(get_queried_object_id(),'_wtf_echo_kind',true))return;
    wp_enqueue_style('wtf-echo',get_theme_file_uri('assets/echo.css'),['wtf-blocks','wtf-mobile-navigation'],filemtime(get_theme_file_path('assets/echo.css')));
    wp_enqueue_script('wtf-echo',get_theme_file_uri('assets/echo.js'),['wtf-site'],filemtime(get_theme_file_path('assets/echo.js')),true);
});

add_filter('body_class',function($classes){if(is_page()&&get_post_meta(get_queried_object_id(),'_wtf_kurz_kind',true))$classes[]='kurzolaki-theme';return $classes;});
add_action('wp_enqueue_scripts',function(){
    if(!is_page()||!get_post_meta(get_queried_object_id(),'_wtf_kurz_kind',true))return;
    wp_enqueue_style('wtf-kurzolaki',get_theme_file_uri('assets/kurzolaki.css'),['wtf-blocks','wtf-mobile-navigation'],filemtime(get_theme_file_path('assets/kurzolaki.css')));
});

add_action('wp_enqueue_scripts',function(){
    if(!is_page()||!get_post_meta(get_queried_object_id(),'_wtf_catalog_kind',true))return;
    wp_enqueue_style('wtf-catalog',get_theme_file_uri('assets/catalog.css'),['wtf-blocks','wtf-mobile-navigation'],filemtime(get_theme_file_path('assets/catalog.css')));
    wp_enqueue_script('wtf-catalog',get_theme_file_uri('assets/catalog.js'),['wtf-site'],filemtime(get_theme_file_path('assets/catalog.js')),true);
});

add_action('wp_enqueue_scripts',function(){
 if(!is_page()||!get_post_meta(get_queried_object_id(),'_wtf_contact_key',true))return;
 wp_enqueue_style('wtf-contact',get_theme_file_uri('assets/contact.css'),['wtf-blocks','wtf-mobile-navigation'],filemtime(get_theme_file_path('assets/contact.css')));
});
