<?php
if (!defined('ABSPATH')) exit;

// Nie zgadujemy innej strony ani produktu dla nieistniejącego adresu.
add_filter('do_redirect_guess_404_permalink', '__return_false');

// EN zachowujemy w panelu; uruchomienie wymaga świadomej zmiany konfiguracji.
function wtf_core_english_enabled() {
    return defined('WTF_ENABLE_ENGLISH') && WTF_ENABLE_ENGLISH;
}

function wtf_core_theme_dev_controls() {
    return in_array(wp_get_environment_type(), ['local', 'development'], true);
}

add_action('template_redirect', function () {
    if (wtf_core_english_enabled() || !function_exists('pll_current_language')) return;
    if (pll_current_language('slug') !== 'en') return;
    $id = get_queried_object_id();
    $translated = $id && is_singular() ? pll_get_post($id, 'pl') : 0;
    $url = $translated && get_post_status($translated) === 'publish'
        ? get_permalink($translated) : pll_home_url('pl');
    // Ukrycie jest tymczasowe; nie utrwalamy przekierowania 301 do wersji PL.
    wp_safe_redirect($url, 302);
    exit;
}, 0);

add_filter('pll_rel_hreflang_attributes', function ($links) {
    if (wtf_core_english_enabled()) return $links;
    return array_filter($links, fn($lang) => $lang === 'pl' || str_starts_with($lang, 'pl-'), ARRAY_FILTER_USE_KEY);
});
