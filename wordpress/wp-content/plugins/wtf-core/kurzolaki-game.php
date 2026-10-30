<?php
if (!defined('ABSPATH')) exit;
add_action('wp_enqueue_scripts', function () {
    if (!is_page() || !has_shortcode(get_post_field('post_content', get_queried_object_id()), 'wtf_kurz_game')) return;
    foreach (['css', 'js'] as $ext) {
        $file = 'assets/kurzolaki-game.'.$ext;
        if ($ext === 'css') wp_enqueue_style('wtf-kurz-game', get_theme_file_uri($file), [], filemtime(get_theme_file_path($file)));
        else wp_enqueue_script('wtf-kurz-game', get_theme_file_uri($file), [], filemtime(get_theme_file_path($file)), true);
    }
});
add_shortcode('wtf_kurz_game', function () {
    ob_start(); ?>
    <main class="kurz-game" data-kurz-game>
      <a class="kg-back" href="<?php echo esc_url(home_url('/kurzolaki/')); ?>">Wróć do Kurzołaków</a>
      <p class="kg-eyebrow">MAŁA GRA. WIELKA ROZRÓBA.</p>
      <h1>Złap <em>Landrynkę!</em></h1>
      <p class="kg-intro">Jesteś Kichaczem. Zgarniaj landrynki, zanim dopadnie cię Puca. Masz 45 sekund. I&nbsp;bardzo dobry powód, żeby się nie myć.</p>
      <div class="kg-toolbar"><span>Landrynki <strong data-score>0</strong></span><span>Czas <strong data-time>45</strong> s</span></div>
      <div class="kg-arena" data-arena>
        <canvas data-canvas tabindex="0" aria-label="Plansza gry. Poruszaj Kichaczem strzałkami lub WASD. Spacja uruchamia zryw. Na telefonie przeciągaj palcem po planszy."></canvas>
        <div class="kg-overlay" data-overlay><span class="kg-tag" data-tag>PUCA JEST NA TROPIE</span><p data-message>Zgarnij słodkości.<br>Nie daj się wypucować.</p><span class="kg-overlay-hint">Strzałki / WASD lub przeciąganie palcem.<br>Spacja lub „Zryw!” pomaga uciec.</span><button class="kg-start" data-start type="button">Uciekamy!</button></div>
      </div>
      <div class="kg-controls"><span class="kg-help">Ruch: WASD / strzałki / palec</span><button type="button" data-pause disabled>Pauza</button><button type="button" class="kg-dash" data-dash disabled>Zryw! <small>SPACJA</small></button></div>
      <p class="kg-status" data-status role="status" aria-live="polite">Zbieraj cukierki. Unikaj mydła. Proste?</p>
      <div hidden data-art data-player="<?php echo esc_url(wtf_core_media('kurzolaki/historie/kichacz-clean.webp')); ?>" data-enemy="<?php echo esc_url(wtf_core_media('kurzolaki/historie/puca-karta.webp')); ?>"></div>
      <noscript>Do uruchomienia gry potrzebujesz włączonego JavaScriptu.</noscript>
    </main>
    <?php return ob_get_clean();
});
