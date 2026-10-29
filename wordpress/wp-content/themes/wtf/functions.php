<?php
add_action('wp_enqueue_scripts', function () {
    if (!function_exists('is_wc_endpoint_url') || !is_wc_endpoint_url('order-received')) return;
    wp_enqueue_style('wtf-order-confirmation', get_theme_file_uri('assets/order-confirmation.css'), ['wtf-shop'], filemtime(get_theme_file_path('assets/order-confirmation.css')));
});
add_action('wp_enqueue_scripts', function () {
    if (!is_page(['sklep-regulamin', 'polityka-prywatnosci', 'polityka-cookies'])) return;
    wp_enqueue_style('wtf-legal', get_theme_file_uri('assets/legal.css'), ['wtf-blocks'], filemtime(get_theme_file_path('assets/legal.css')));
});
add_action('after_setup_theme', function () {
    add_editor_style('assets/legal.css');
});
// Wspólna wektorowa ikona zakładki: łapka z logo WTF.
function wtf_favicon() {
    echo '<link rel="icon" type="image/png" sizes="96x96" href="'.esc_url(get_theme_file_uri('assets/favicon-96.png')).'">'."\n";
    echo '<link rel="icon" type="image/svg+xml" href="'.esc_url(get_theme_file_uri('assets/favicon.svg')).'">'."\n";
}
add_action('wp_head','wtf_favicon',100);
add_action('admin_head','wtf_favicon',100);
add_action('login_head','wtf_favicon',100);
add_action('wp_enqueue_scripts', function () {
    if (!is_page() || get_page_template_slug() !== 'klanarchia') return;
    wp_enqueue_style('wtf-klanarchia', get_theme_file_uri('assets/klanarchia.css'), ['wtf-blocks','wtf-mobile-navigation'], filemtime(get_theme_file_path('assets/klanarchia.css')));
    wp_enqueue_style('wtf-klanarchia-subpage-header',get_theme_file_uri('assets/klanarchia-subpage-header.css'),['wtf-klanarchia'],filemtime(get_theme_file_path('assets/klanarchia-subpage-header.css')));
    wp_enqueue_style('wtf-klanarchia-header',get_theme_file_uri('assets/klanarchia-header.css'),['wtf-klanarchia-subpage-header'],filemtime(get_theme_file_path('assets/klanarchia-header.css')));
    if(get_post_meta(get_queried_object_id(),'_wtf_klan_note',true))wp_enqueue_style('wtf-klanarchia-note',get_theme_file_uri('assets/klanarchia-note.css'),['wtf-klanarchia-header'],filemtime(get_theme_file_path('assets/klanarchia-note.css')));
    wp_enqueue_script('wtf-klanarchia', get_theme_file_uri('assets/tsukuyumi.js'), ['wtf-site'], filemtime(get_theme_file_path('assets/tsukuyumi.js')), true);
    if(get_post_meta(get_queried_object_id(),'_wtf_klan_downloads',true)){
        wp_enqueue_style('wtf-klanarchia-downloads',get_theme_file_uri('assets/klanarchia-downloads.css'),['wtf-klanarchia-subpage-header'],filemtime(get_theme_file_path('assets/klanarchia-downloads.css')));
        wp_enqueue_script('wtf-klanarchia-downloads',get_theme_file_uri('assets/klanarchia-downloads.js'),[],filemtime(get_theme_file_path('assets/klanarchia-downloads.js')),true);
    }
});
add_filter('body_class', function ($classes) {
    if (is_page() && get_page_template_slug() === 'klanarchia') {
        $classes[] = 'klan-page';
        if(get_post_field('post_name',get_queried_object_id())!=='klanarchia')$classes[]='klan-subpage';
    }
    if (is_page() && get_post_meta(get_queried_object_id(),'_wtf_klan_downloads',true)) $classes[] = 'klan-download-page';
    return $classes;
});
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
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_editor_style(['assets/site.css', 'assets/opening.css', 'assets/mobile-navigation.css', 'assets/blocks.css']);
});
add_action('wp_enqueue_scripts',function(){
    if(!function_exists('is_woocommerce') || !(is_woocommerce()||is_cart()||is_checkout()||is_account_page())) return;
    wp_enqueue_style('wtf-shop',get_theme_file_uri('assets/shop.css'),['wtf-blocks'],filemtime(get_theme_file_path('assets/shop.css')));
    wp_enqueue_script('wtf-shop',get_theme_file_uri('assets/shop.js'),[],filemtime(get_theme_file_path('assets/shop.js')),true);
    if(is_product()) wp_enqueue_style('wtf-product',get_theme_file_uri('assets/product.css'),['wtf-shop'],filemtime(get_theme_file_path('assets/product.css')));
    if(is_cart()) {
        wp_enqueue_style('wtf-cart',get_theme_file_uri('assets/cart.css'),['wtf-shop'],filemtime(get_theme_file_path('assets/cart.css')));
        wp_enqueue_script('wtf-cart',get_theme_file_uri('assets/cart.js'),['jquery','wc-cart'],filemtime(get_theme_file_path('assets/cart.js')),true);
    }
    if(is_account_page()) wp_enqueue_style('wtf-account',get_theme_file_uri('assets/account.css'),['wtf-shop'],filemtime(get_theme_file_path('assets/account.css')));
    if(is_account_page()) wp_enqueue_script('wtf-account',get_theme_file_uri('assets/account.js'),['jquery','wc-country-select'],filemtime(get_theme_file_path('assets/account.js')),true);
    if(is_checkout() && !is_wc_endpoint_url()) {
        wp_enqueue_style('wtf-checkout',get_theme_file_uri('assets/checkout.css'),['wtf-shop'],filemtime(get_theme_file_path('assets/checkout.css')));
        wp_enqueue_script('wtf-checkout',get_theme_file_uri('assets/checkout.js'),['jquery','wc-checkout'],filemtime(get_theme_file_path('assets/checkout.js')),true);
    }
});
add_action('wp_enqueue_scripts',function () {
    if (function_exists('wtf_customer_switch_context') && wtf_customer_switch_context()) {
        wp_enqueue_style('wtf-customer-switch',get_theme_file_uri('assets/customer-switch.css'),['wtf-site'],filemtime(get_theme_file_path('assets/customer-switch.css')));
    }
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
    $dev = function_exists('wtf_core_theme_dev_controls') && wtf_core_theme_dev_controls();
    $fixed = is_page() && get_page_template_slug() === 'klanarchia' ? 'dark' : '';
    wp_print_inline_script_tag(file_get_contents(get_theme_file_path('assets/theme-init.js')), ['id'=>'wtf-theme-init', 'data-dev-controls'=>$dev?'1':'0', 'data-fixed-theme'=>$fixed]);
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
    wp_enqueue_script('wtf-kurzolaki',get_theme_file_uri('assets/kurzolaki.js'),['wtf-site'],filemtime(get_theme_file_path('assets/kurzolaki.js')),true);
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
