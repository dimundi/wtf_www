<?php
/**
 * Plugin Name: wtf_cookies
 * Description: Zgody na opcjonalne technologie, blokowanie YouTube i odroczone ładowanie mapy InPost.
 * Version: 0.1.0
 * Requires PHP: 8.0
 * Text Domain: wtf_cookies
 */
if (!defined('ABSPATH')) exit;

define('WTF_COOKIES_REVISION', 2);
require_once __DIR__.'/analytics.php';

function wtf_cookies_categories() {
    return apply_filters('wtf_cookies_categories', [
        'media'=>['label'=>'Filmy YouTube', 'description'=>'Zgoda na wczytanie odtwarzacza Google/YouTube, który otrzyma dane o urządzeniu i może stosować własne technologie zapisu informacji.'],
    ]);
}

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('wtf-cookies', plugins_url('assets/cookies.css', __FILE__), [], filemtime(__DIR__.'/assets/cookies.css'));
    wp_enqueue_script('wtf-cookies', plugins_url('assets/cookies.js', __FILE__), [], filemtime(__DIR__.'/assets/cookies.js'), false);
    wp_add_inline_script('wtf-cookies', 'window.wtfCookiesConfig='.wp_json_encode([
        'revision'=>WTF_COOKIES_REVISION, 'cookie'=>'wtf_cookies_consent', 'days'=>180,
        'categories'=>wtf_cookies_categories(), 'policy'=>home_url('/polityka-cookies/'),
    ], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';', 'before');
});

add_action('wp_footer', function () {
    ?>
    <section id="wtfc-banner" class="wtfc-panel" aria-labelledby="wtfc-banner-title" hidden>
        <h2 id="wtfc-banner-title">Twoja prywatność</h2>
        <p>Niezbędne cookies obsługują koszyk i logowanie. Ty decydujesz, czy zezwolić na statystyki Google Analytics i filmy YouTube. Odmowa nie blokuje zakupów.</p>
        <a class="wtfc-policy" href="<?php echo esc_url(home_url('/polityka-cookies/')); ?>">Polityka cookies</a>
        <div class="wtfc-actions">
            <button type="button" data-wtfc-accept>Akceptuję wszystkie</button>
            <button type="button" data-wtfc-reject>Tylko niezbędne</button>
            <button type="button" data-wtfc-open>Ustawienia</button>
        </div>
        <p id="wtfc-storage-error" role="status" hidden>Nie udało się zapamiętać wyboru. Ustawienia obowiązują do opuszczenia strony.</p>
    </section>
    <dialog id="wtfc-settings" class="wtfc-dialog" aria-labelledby="wtfc-settings-title">
        <div class="wtfc-dialog-content">
            <h2 id="wtfc-settings-title">Ustawienia cookies</h2>
            <p>Zgody są dobrowolne. Możesz je zmienić w każdej chwili przez „Ustawienia cookies” w stopce.</p>
            <div class="wtfc-category"><strong>Niezbędne — zawsze aktywne</strong><p>Koszyk, logowanie, bezpieczeństwo i zapamiętanie tego wyboru.</p></div>
            <?php foreach (wtf_cookies_categories() as $key=>$category): ?>
                <label class="wtfc-category"><span><input type="checkbox" data-wtfc-category="<?php echo esc_attr($key); ?>"> <strong><?php echo esc_html($category['label']); ?></strong></span><span class="wtfc-description"><?php echo esc_html($category['description']); ?></span></label>
            <?php endforeach; ?>
            <a class="wtfc-policy" href="<?php echo esc_url(home_url('/polityka-cookies/')); ?>">Polityka cookies</a>
            <div class="wtfc-actions">
                <button type="button" data-wtfc-save>Zapisz wybór</button>
                <button type="button" data-wtfc-reject>Tylko niezbędne</button>
                <button type="button" data-wtfc-close>Zamknij</button>
            </div>
            <p class="wtfc-revoke-note">Po wycofaniu zgody zatrzymamy wczytywanie materiałów. Informacje wcześniej zapisane przez zewnętrznego dostawcę możesz usunąć w ustawieniach przeglądarki.</p>
        </div>
    </dialog>
    <noscript><p class="wtfc-nojs">Opcjonalne materiały są wyłączone bez JavaScript. Koszyk i logowanie korzystają z niezbędnych cookies. <a href="<?php echo esc_url(home_url('/polityka-cookies/')); ?>">Polityka cookies</a>.</p></noscript>
    <?php
}, 30);

function wtf_cookies_media_placeholder($url, $title = 'Film YouTube') {
    $host = strtolower(wp_parse_url($url, PHP_URL_HOST) ?? '');
    $path = wp_parse_url($url, PHP_URL_PATH) ?? '';
    if (!in_array($host, ['www.youtube-nocookie.com','youtube-nocookie.com','www.youtube.com','youtube.com'], true)
        || !preg_match('~^/embed/([a-zA-Z0-9_-]+)$~D', $path, $matches)) return '';
    $url = 'https://www.youtube-nocookie.com/embed/'.$matches[1];
    return '<div class="wtfc-media" data-wtfc-media data-wtfc-src="'.esc_url($url).'" data-wtfc-title="'.esc_attr($title).'">'
        .'<div class="wtfc-media-notice"><p>'.esc_html($title).'</p><p>Ten film pochodzi z YouTube. Odtwarzacz wczytamy dopiero po Twojej zgodzie na połączenie z Google/YouTube i jego technologie zapisu informacji.</p>'
        .'<button type="button" data-wtfc-media-allow>Zezwól na YouTube</button></div>'
        .'<noscript><a href="https://www.youtube.com/watch?v='.esc_attr($matches[1]).'" rel="noopener noreferrer" target="_blank">Otwórz film w YouTube</a></noscript></div>';
}

add_filter('render_block', function ($html) {
    if (is_admin() || is_feed() || !is_string($html)) return $html;
    // Replace inert previews too: their remote thumbnails otherwise contact Google before consent.
    $html = preg_replace_callback('~<a\b([^>]*\bdata-elekt-video\s*=\s*(["\x27])(.*?)\2[^>]*)>.*?</a>~is', function ($m) {
        $title = preg_match('~aria-label\s*=\s*(["\x27])(.*?)\1~is', $m[1], $label) ? html_entity_decode($label[2], ENT_QUOTES, 'UTF-8') : 'Film YouTube';
        return wtf_cookies_media_placeholder(html_entity_decode($m[3], ENT_QUOTES, 'UTF-8'), $title) ?: $m[0];
    }, $html);
    $html = preg_replace_callback('~<iframe\b([^>]*)>.*?</iframe>~is', function ($m) {
        if (!preg_match('~\bsrc\s*=\s*(["\x27])(.*?)\1~is', $m[1], $src)) return $m[0];
        $title = preg_match('~\btitle\s*=\s*(["\x27])(.*?)\1~is', $m[1], $label) ? html_entity_decode($label[2], ENT_QUOTES, 'UTF-8') : 'Film YouTube';
        return wtf_cookies_media_placeholder(html_entity_decode($src[2], ENT_QUOTES, 'UTF-8'), $title) ?: $m[0];
    }, $html);
    return $html;
}, 100);

add_filter('render_block_wtf/footer', function ($html) {
    return str_replace('</footer>', '<button class="wtfc-footer-link" type="button" data-wtfc-open>Ustawienia cookies</button></footer>', $html);
});

// A single-language release needs no persistent language preference cookie.
add_filter('pll_cookie_args', function ($args) {
    if (function_exists('wtf_core_english_enabled') && !wtf_core_english_enabled()) $args['expires'] = time()-YEAR_IN_SECONDS;
    return $args;
}, 100);

// The map is a requested checkout feature: load its SDK only when the customer opens it.
add_filter('script_loader_tag', function ($tag, $handle, $src) {
    if (is_admin() || $handle !== 'geowidget-inpost') return $tag;
    return '<script type="text/plain" data-wtfc-inpost-src="'.esc_url($src).'"></script>';
}, 100, 3);
add_filter('style_loader_tag', function ($tag, $handle, $href) {
    if (is_admin() || $handle !== 'geowidget-css') return $tag;
    return '<template data-wtfc-inpost-css="'.esc_url($href).'"></template>';
}, 100, 3);

add_action('admin_menu', function () {
    add_options_page('WTF Cookies', 'WTF Cookies', 'manage_options', 'wtf-cookies', function () {
        if (!current_user_can('manage_options')) return;
        echo '<div class="wrap"><h1>WTF Cookies</h1><p>Baner zgód, Google Analytics po zgodzie, blokowanie YouTube przed zgodą i wczytywanie mapy InPost na żądanie klienta.</p><p>Wybór jest zapamiętywany na 180 dni. Meta Pixel nie jest uruchamiany.</p><p>Treść polityki edytujesz w Strony → Polityka cookies.</p></div>';
    });
});
