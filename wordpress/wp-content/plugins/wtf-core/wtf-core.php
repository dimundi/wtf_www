<?php
/**
 * Plugin Name: WTF Core
 * Description: Edytowalne sekcje WTF i integracja interaktywnych modułów.
 * Version: 0.1.0
 * Requires PHP: 8.3
 */
if (!defined('ABSPATH')) { exit; }
require_once __DIR__.'/release.php';
require_once __DIR__.'/tsukuyumi.php';
require_once __DIR__.'/elekt.php';
require_once __DIR__.'/echo.php';
require_once __DIR__.'/kurzolaki.php';
require_once __DIR__.'/catalog.php';
require_once __DIR__.'/contact.php';
require_once __DIR__.'/klanarchia.php';
require_once __DIR__.'/media-groups.php';
require_once __DIR__.'/shop.php';
require_once __DIR__.'/shop-classification.php';
require_once __DIR__.'/shop-product.php';
require_once __DIR__.'/shop-account.php';
require_once __DIR__.'/shop-registration.php';
require_once __DIR__.'/shop-checkout.php';
require_once __DIR__.'/shop-cart.php';
require_once __DIR__.'/shop-history.php';
require_once __DIR__.'/customer-switch.php';

if (is_admin()) { require_once __DIR__ . '/admin.php'; }

function wtf_core_media($file) { return wp_upload_dir()['baseurl'] . '/media/' . $file; }
function wtf_core_language() { return function_exists('pll_current_language') ? (pll_current_language('slug') ?: 'pl') : substr(get_locale(), 0, 2); }
function wtf_core_text($text) { return function_exists('pll__') ? pll__($text) : $text; }
// Korzystamy z metadanych biblioteki: konwersję wykonuje Modern Image Formats.
function wtf_core_image_id($item) {
    static $ids = [];
    $id = absint($item['imageId'] ?? 0);
    if ($id && wp_attachment_is_image($id)) { return $id; }
    $url = $item['image'] ?? '';
    if (!$url) { return 0; }
    if (!isset($ids[$url])) { $ids[$url] = attachment_url_to_postid($url); }
    return $ids[$url];
}
function wtf_core_image($item, $size, $attributes = []) {
    $id = wtf_core_image_id($item);
    if ($id) {
        $html = wp_get_attachment_image($id, $size, false, $attributes);
        if ($html) { return $html; }
    }
    $attributes = array_merge(['src'=>$item['image'] ?? '', 'alt'=>''], $attributes);
    $html = '<img';
    foreach ($attributes as $key=>$value) {
        if ($value !== false && $value !== null) { $html .= ' '.esc_attr($key).'="'.esc_attr($value).'"'; }
    }
    return $html.'>';
}
function wtf_core_heading($text) {
    $lines=explode("\n",$text);
    $lines=array_map('esc_html',$lines);
    if(count($lines)>1)$lines[count($lines)-1]='<em>'.$lines[count($lines)-1].'</em>';
    return implode('<br>',$lines);
}
function wtf_core_schema() {
    $image = fn($name, $file) => ['title'=>$name, 'image'=>wtf_core_media($file), 'imageId'=>0, 'url'=>'https://whatthefrog.pl/' . ($name === 'Kurzołaki' ? 'kurzolaki' : ($name === 'Echo Światów' ? 'echo-swiatow' : strtolower($name))) . '/'];
    $games = [$image('Klanarchia','klanarchia.jpg'),$image('Kurzołaki','kurzolaki.jpg'),$image('Elekt','elekt.jpg'),$image('Tsukuyumi','tsukuyumi.png'),$image('Echo Światów','echo-swiatow.jpg')];
    return [
        'shop'=>[
            'title'=>'Sklep WTF',
            'fields'=>['eyebrow'=>'Nadtytuł','title'=>'Nagłówek','description'=>'Wprowadzenie','image'=>'Baner Elekta','elektTitle'=>'Elekt — nagłówek banera','elektDescription'=>'Elekt — opis banera','elektBaseDescription'=>'Elekt — wprowadzenie do podstawki'],
            'defaults'=>['eyebrow'=>'PROSTO OD WYDAWNICTWA','title'=>'Zabierz grę do domu.','description'=>'Zacznij od gry. Dobierz dodatki z jej świata.','image'=>wtf_core_media('elekt.jpg'),'imageId'=>0,'elektTitle'=>'Tron jest jeden.','elektDescription'=>'Przywołuj upiory. Rozsyłaj kultystów. Sięgnij po władzę w Dominacie.','elektBaseDescription'=>'Zacznij od gry podstawowej. Dodatki i akcesoria do swojego zestawu dobierzesz poniżej.'],
        ],
        'header'=>[
            'title'=>'Nagłówek WTF',
            'fields'=>['gamesLabel'=>'Napis menu gier','allLabel'=>'Link do wszystkich gier','allUrl'=>'Adres katalogu','contactLabel'=>'Napis Kontakt','contactUrl'=>'Adres kontaktu','shopLabel'=>'Napis Sklep','shopUrl'=>'Adres sklepu'],
            'defaults'=>['gamesLabel'=>'Nasze gry','allLabel'=>'Wszystkie gry','allUrl'=>'https://whatthefrog.pl/katalog/','contactLabel'=>'Kontakt','contactUrl'=>'https://whatthefrog.pl/kontakt/','shopLabel'=>'Sklep','shopUrl'=>'https://whatthefrog.pl/sklep/','items'=>[$games[0],$games[1],$games[2],$games[4],$games[3]]],
            'itemFields'=>['title'=>'Nazwa','image'=>'Grafika','url'=>'Adres'],
        ],
        'hero'=>[
            'title'=>'Baner główny WTF',
            'fields'=>['eyebrow'=>'Nadtytuł','title'=>'Nagłówek (Enter: nowa linia)','description'=>'Opis','buttonLabel'=>'Napis przycisku','buttonUrl'=>'Adres przycisku'],
            'defaults'=>['eyebrow'=>'WHAT THE FROG / WYDAWNICTWO GIER','title'=>"Małe wydawnictwo.\nWielkie światy.",'description'=>'Gry planszowe, karciane i fabularne. Odkryj światy, do których chce się wracać — i znajdź swoją następną przygodę.','buttonLabel'=>'Odkryj nasze gry','buttonUrl'=>'https://whatthefrog.pl/katalog/','items'=>[$games[0],$games[1],$games[2]]],
            'itemFields'=>['title'=>'Tytuł slajdu / opis grafiki','image'=>'Grafika','url'=>'Adres gry'],
        ],
        'cards'=>[
            'title'=>'Karty gier WTF',
            'fields'=>['eyebrow'=>'Nadtytuł','title'=>'Nagłówek','description'=>'Wprowadzenie','columns'=>'Kolumny desktop (2 lub 3)','anchor'=>'Kotwica sekcji'],
            'defaults'=>['eyebrow'=>'WYBIERZ NASTRÓJ NA WIECZÓR','title'=>'W co dziś gramy?','description'=>'','columns'=>'3','anchor'=>'games','items'=>[
                array_merge($games[2],['title'=>'Gramy bez sentymentów.','description'=>'Przywołaj sługi, ukryj zamiary i sięgnij po władzę.','linkLabel'=>'Zagraj w Elekta']),
                array_merge($games[3],['title'=>'Dziś podbijasz świat.','description'=>'Wybierz armię, która walczy po Twojemu.','linkLabel'=>'Zagraj w Tsukuyumi']),
                array_merge($games[1],['title'=>'Coś lekkiego? Tylko nie licz na zgodę.','description'=>'Jedna landrynka. Kilka głodnych stworów. Każdy ma swój plan.','linkLabel'=>'Zagraj w Kurzołaki']),
            ]],
            'itemFields'=>['title'=>'Nagłówek karty','description'=>'Opis','image'=>'Grafika','url'=>'Adres gry','linkLabel'=>'Napis linku'],
        ],
        'promotion'=>[
            'title'=>'Promocja WTF',
            'fields'=>['visible'=>'Pokaż promocję','eyebrow'=>'Nadtytuł','title'=>'Nagłówek','description'=>'Opis','couponLabel'=>'Etykieta kodu','coupon'=>'Kod','buttonLabel'=>'Napis przycisku','buttonUrl'=>'Adres przycisku','note'=>'Dopisek','badge'=>'Oznaczenie rabatu','image'=>'Grafika'],
            'defaults'=>['visible'=>true,'eyebrow'=>'PROMOCJA NA ELEKTA','title'=>"Sięgnij po tron.\nZapłać mniej.",'description'=>'Twój kolejny pojedynek zaczyna się tutaj. Z kodem rabatowym Elekt jest o 10% tańszy.','couponLabel'=>'KOD RABATOWY','coupon'=>'ELEKT10','buttonLabel'=>'Kup Elekta','buttonUrl'=>'https://whatthefrog.pl/sklep/','note'=>'Przykładowa promocja — kod do ustalenia.','badge'=>'−10%','image'=>wtf_core_media('elekt.jpg'),'imageId'=>0],
        ],
        'about'=>[
            'title'=>'O wydawnictwie WTF',
            'fields'=>['title'=>'Nagłówek','description'=>'Akapit główny','secondary'=>'Drugi akapit','buttonLabel'=>'Napis linku','buttonUrl'=>'Adres linku'],
            'defaults'=>['title'=>"Z wyobraźni.\nDla graczy.",'description'=>'Jesteśmy niezależnym wydawnictwem gier. Tworzymy i rozwijamy gry, które angażują graczy — ilustracją, klimatem i tym, co dzieje się przy stole.','secondary'=>'Nasza przygoda zaczęła się od Elekta. Dziś zapraszamy Cię do kolejnych światów. Wybierz swój i usiądź z nami do gry.','buttonLabel'=>'Porozmawiajmy','buttonUrl'=>'https://whatthefrog.pl/kontakt/'],
        ],
        'shop-invitation'=>[
            'title'=>'Zaproszenie do sklepu WTF',
            'fields'=>['eyebrow'=>'Nadtytuł','title'=>'Nagłówek','buttonLabel'=>'Napis przycisku','buttonUrl'=>'Adres sklepu'],
            'defaults'=>['eyebrow'=>'CZAS NA KOLEJNĄ PRZYGODĘ','title'=>'Twój następny świat czeka.','buttonLabel'=>'Odwiedź sklep','buttonUrl'=>'https://whatthefrog.pl/sklep/'],
        ],
        'footer'=>[
            'title'=>'Stopka WTF',
            'fields'=>['title'=>'Nazwa wydawnictwa','description'=>'Opis','email'=>'E-mail','navigationLabel'=>'Nagłówek nawigacji','informationLabel'=>'Nagłówek dokumentów','copyright'=>'Prawa / firma','image'=>'Banner płatności'],
            'defaults'=>['title'=>'What The Frog','description'=>'Gry planszowe, karciane i fabularne.','email'=>'biuro@whatthefrog.pl','navigationLabel'=>'Odkrywaj','informationLabel'=>'Informacje','copyright'=>'What The Frog · Dimundi sp. z o.o.','image'=>wtf_core_media('platnosci.png'),'imageId'=>0,'items'=>[
                ['title'=>'Gry i publikacje','url'=>'https://whatthefrog.pl/katalog/','group'=>'navigation'],['title'=>'Sklep','url'=>'https://whatthefrog.pl/sklep/','group'=>'navigation'],['title'=>'Kontakt','url'=>'https://whatthefrog.pl/kontakt/','group'=>'navigation'],['title'=>'Regulamin sklepu','url'=>'https://whatthefrog.pl/sklep-regulamin/','group'=>'information'],['title'=>'Polityka prywatności','url'=>'https://whatthefrog.pl/polityka-prywatnosci/','group'=>'information'],['title'=>'Polityka cookies','url'=>'https://whatthefrog.pl/polityka-cookies/','group'=>'information'],
            ]],
            'itemFields'=>['title'=>'Napis linku','url'=>'Adres','group'=>'Kolumna: navigation / information'],
        ],
    ];
}

add_action('init', function () {
    wp_register_script('wtf-block-editor', plugins_url('editor.js', __FILE__), ['wp-blocks','wp-element','wp-components','wp-block-editor','wp-server-side-render'], filemtime(__DIR__.'/editor.js'));
    wp_add_inline_script('wtf-block-editor','window.wtfBlockSchemas = '.wp_json_encode(wtf_core_schema()).';','before');
    foreach (wtf_core_schema() as $name=>$schema) {
        $attributes=[];
        foreach ($schema['defaults'] as $key=>$value) { $attributes[$key]=['type'=>is_array($value)?'array':(is_bool($value)?'boolean':(is_int($value)?'number':'string')),'default'=>$value]; }
        register_block_type('wtf/'.$name, ['api_version'=>3,'title'=>$schema['title'],'category'=>'wtf','attributes'=>$attributes,'editor_script'=>'wtf-block-editor','supports'=>['html'=>false],'render_callback'=>fn($attrs)=>wtf_core_render($name,$attrs)]);
    }
    // Kontrakt modułów React: język i oprawę przekazuje host; napisy należą do aplikacji.
    register_block_type('wtf/react-app', ['api_version'=>3,'attributes'=>['module'=>['type'=>'string','default'=>'']],'render_callback'=>function($attrs) {
        $modules=apply_filters('wtf_react_modules',[]);
        $module=$modules[$attrs['module']]??null;
        if (!$module || empty($module['script_handle'])) { return ''; }
        $id=wp_unique_id('wtf-react-');
        wp_enqueue_script($module['script_handle']);
        $config=['language'=>wtf_core_language(),'containerId'=>$id,'theme'=>'dark'];
        wp_add_inline_script($module['script_handle'],'window.wtfReactModules=window.wtfReactModules||{};window.wtfReactModules['.wp_json_encode($id).']='.wp_json_encode($config).';window.wtfReactModules['.wp_json_encode($id).'].theme=document.documentElement.dataset.theme||"dark";','before');
        return '<div id="'.esc_attr($id).'" data-wtf-react-module="'.esc_attr($attrs['module']).'"></div>';
    }]);
});
add_filter('block_categories_all', function($categories) { array_unshift($categories,['slug'=>'wtf','title'=>'WTF — sekcje strony']); return $categories; });
add_action('admin_init',function(){
    if(!function_exists('pll_register_string'))return;
    $strings=['Zaloguj się','Moje konto','Koszyk','Zmień motyw kolorystyczny','Przejdź do treści','Menu główne','Płatności'];
    $parts=get_posts(['post_type'=>'wp_template_part','post_status'=>'publish','numberposts'=>-1]);
    foreach($parts as $part)foreach(parse_blocks($part->post_content) as $block){
        if(!in_array($block['blockName'],['wtf/header','wtf/footer'],true))continue;
        $attrs=$block['attrs'];
        foreach(['gamesLabel','allLabel','contactLabel','shopLabel','title','description','navigationLabel','informationLabel'] as $key)if(!empty($attrs[$key]))$strings[]=$attrs[$key];
        foreach($attrs['items']??[] as $item)if(!empty($item['title']))$strings[]=$item['title'];
    }
    foreach(array_unique($strings) as $text)pll_register_string($text,$text,'WTF');
});

function wtf_core_render($type, $a) {
    $a = array_merge(wtf_core_schema()[$type]['defaults'], $a);
    if ($type==='promotion' && $a['visible'] === false) return '';
    if($type==='shop') return wtf_shop_render($a);
    if (isset($a['items'])) {
        foreach ($a['items'] as &$item) { if (isset($item['url'])) { $item['url']=wtf_klan_local_game_link(wtf_contact_local_game_link(wtf_catalog_local_game_link(wtf_kurz_local_game_link(wtf_echo_local_game_link(wtf_ele_local_game_link(wtf_tsu_local_game_link($item['url']))))))); } }
        unset($item);
    }
    $e=fn($key)=>esc_html(wtf_core_text($a[$key]??''));
    $url=fn($key)=>esc_url(wtf_klan_local_game_link(wtf_contact_local_game_link(wtf_catalog_local_game_link($a[$key]??''))));
    $chevron='<span class="games-chevron" aria-hidden="true"><svg viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m4 6 4 4 4-4"/></svg></span>';
    ob_start();
    if ($type==='header') {
        $lang=wtf_core_language();
        $languages=wtf_core_english_enabled() && function_exists('pll_the_languages') ? pll_the_languages(['raw'=>1,'hide_if_no_translation'=>1]) : [];
        ?>
        <a class="skip" href="#main"><?php echo esc_html(wtf_core_text('Przejdź do treści')); ?></a>
        <header class="header wrap">
            <?php $klan_download_header=is_page() && get_page_template_slug()==='klanarchia';if($klan_download_header): ?><div class="klan-header-logos"><?php endif; ?>
            <a class="brand" href="<?php echo esc_url(function_exists('pll_home_url')?pll_home_url():home_url('/')); ?>" aria-label="What The Frog"><span class="brand-svg"><img class="brand-two-tone" src="<?php echo esc_url($klan_download_header?get_theme_file_uri('assets/wtf-logo-gold.svg'):wtf_core_media('wtf-logo-white-green.svg')); ?>" alt=""></span></a>
            <?php if($klan_download_header): ?><nav class="klan-header-game-links" aria-label="Klanarchia"><a href="<?php echo esc_url(home_url('/klanarchia/')); ?>"<?php if(get_post_field('post_name',get_queried_object_id())==='klanarchia')echo ' aria-current="page"'; ?>>Klanarchia</a><a href="<?php echo esc_url(home_url('/klanarchia/download/')); ?>"<?php if(get_post_meta(get_queried_object_id(),'_wtf_klan_downloads',true))echo ' aria-current="page"'; ?>>Pobierz</a><a href="<?php echo esc_url(home_url('/klanarchia/edytor/')); ?>"<?php if(get_post_meta(get_queried_object_id(),'_wtf_klan_talent_editor',true))echo ' aria-current="page"'; ?>>Edytor</a></nav></div><?php endif; ?>
            <button class="mobile-menu-toggle" type="button" aria-expanded="false" aria-controls="main-navigation header-tools" hidden><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg><span>Menu</span></button>
            <nav id="main-navigation" aria-label="<?php echo esc_attr(wtf_core_text('Menu główne')); ?>"><details class="games-navigation"><summary><?php echo $e('gamesLabel').$chevron; ?></summary><div class="games-dropdown"><div class="games-links">
                <?php foreach($a['items'] as $item): ?><a class="navigation-game" href="<?php echo esc_url($item['url']??''); ?>"><?php echo wtf_core_image($item, 'medium', ['alt'=>'', 'loading'=>'lazy', 'sizes'=>'(max-width: 700px) calc((100vw - 56px) / 3), 260px']); ?><span><?php echo esc_html(wtf_core_text($item['title']??'')); ?></span></a><?php endforeach; ?>
            </div><a class="navigation-all" href="<?php echo $url('allUrl'); ?>"><?php echo $e('allLabel'); ?></a></div></details><a href="<?php echo $url('contactUrl'); ?>"><?php echo $e('contactLabel'); ?></a><a class="main-shop-link" href="<?php echo $url('shopUrl'); ?>"><?php echo $e('shopLabel'); ?></a></nav>
            <div class="tools" id="header-tools"><a class="header-login" href="<?php echo esc_url(function_exists('wc_get_page_permalink')?wc_get_page_permalink('myaccount'):wp_login_url()); ?>"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg><span><?php echo esc_html(wtf_core_text(is_user_logged_in()?'Moje konto':'Zaloguj się')); ?></span></a>
            <?php $count=function_exists('WC')&&WC()->cart?WC()->cart->get_cart_contents_count():0; ?><a class="header-cart" <?php if(!$count)echo 'hidden'; ?> href="<?php echo esc_url(function_exists('wc_get_cart_url')?wc_get_cart_url():$a['shopUrl']); ?>" aria-label="<?php echo esc_attr(wtf_core_text('Koszyk')); ?>"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3 4h2l2.2 11h10.6l2-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="17" cy="20" r="1"/></svg><span data-cart-count><?php echo (int)$count; ?></span></a>
            <?php if(wtf_core_english_enabled()): if(count($languages)>1): foreach($languages as $language): if($language['current_lang'])continue; ?><a class="language" href="<?php echo esc_url($language['url']); ?>" lang="<?php echo esc_attr($language['slug']); ?>"><?php echo esc_html(strtoupper($lang).' / '.strtoupper($language['slug'])); ?></a><?php endforeach; else: ?><span class="language"><?php echo esc_html(strtoupper($lang)); ?></span><?php endif; endif; ?>
            <?php if(!$klan_download_header && wtf_core_theme_dev_controls()): ?><button id="theme-toggle" aria-label="<?php echo esc_attr(wtf_core_text('Zmień motyw kolorystyczny')); ?>" aria-pressed="false">☀</button><?php endif; ?></div>
        </header>
        <?php
    } elseif ($type==='hero') {
        $slides=$a['items'];
        foreach ($slides as &$slide) {
            $id = wtf_core_image_id($slide);
            if (!$id) { continue; }
            $source = wp_get_attachment_image_src($id, 'full');
            if ($source) { $slide['image'] = $source[0]; }
            $slide['srcset'] = wp_get_attachment_image_srcset($id, 'full') ?: '';
            $slide['sizes'] = '(max-width: 700px) 100vw, (max-width: 1840px) calc(100vw - 40px), 1800px';
        }
        unset($slide);
        $first=$slides[0]??['image'=>'','title'=>'','url'=>'']; ?>
        <section class="hero wrap klanarchia" data-slides="<?php echo esc_attr(wp_json_encode($slides)); ?>"><?php echo wtf_core_image($first, 'full', ['class'=>'hero-art', 'alt'=>$first['title'], 'fetchpriority'=>'high', 'loading'=>'eager', 'sizes'=>$first['sizes'] ?? '100vw']); ?><div class="hero-shade"></div><div class="hero-copy"><span class="eyebrow"><?php echo $e('eyebrow'); ?></span><h1><?php echo wtf_core_heading($a['title']); ?></h1><p><?php echo $e('description'); ?></p><a class="button" href="<?php echo $url('buttonUrl'); ?>"><?php echo $e('buttonLabel'); ?> <span aria-hidden="true">↗</span></a></div><div class="hero-bottom"><a href="<?php echo esc_url($first['url']); ?>"><?php echo esc_html($first['title']); ?> <span aria-hidden="true">↗</span></a></div></section>
        <?php
    } elseif ($type==='cards') { ?>
        <section class="wrap games mood-games<?php if($a['columns']==='2')echo ' two-player-games'; ?>" <?php if($a['anchor'])echo 'id="'.esc_attr(sanitize_title($a['anchor'])).'"'; ?>><div class="section-heading"><div><?php if($a['eyebrow']): ?><span class="eyebrow"><?php echo $e('eyebrow'); ?></span><?php endif; ?><h2><?php echo $e('title'); ?></h2></div><?php if($a['description']): ?><p><?php echo $e('description'); ?></p><?php endif; ?></div><div class="game-grid">
        <?php foreach($a['items'] as $item): ?><a class="game-card" href="<?php echo esc_url($item['url']??''); ?>"><div class="game-image"><?php echo wtf_core_image($item, 'large', ['alt'=>$item['title']??'', 'loading'=>'lazy', 'sizes'=>$a['columns']==='2' ? '(max-width: 600px) calc(100vw - 40px), (max-width: 1464px) calc((100vw - 99px) / 2), 682px' : '(max-width: 600px) calc(100vw - 40px), (max-width: 1464px) calc((100vw - 134px) / 3), 443px']); ?></div><div class="mood-copy"><h3><?php echo esc_html($item['title']??''); ?></h3><p><?php echo esc_html($item['description']??''); ?></p><span class="text-link"><?php echo esc_html($item['linkLabel']??''); ?></span></div></a><?php endforeach; ?>
        </div></section>
        <?php
    } elseif ($type==='promotion') { ?>
        <section class="wrap home-promotion"><div class="promotion-copy"><span class="eyebrow"><?php echo $e('eyebrow'); ?></span><h2><?php echo wtf_core_heading($a['title']); ?></h2><p><?php echo $e('description'); ?></p><div class="promotion-actions"><div class="promotion-coupon"><span><?php echo $e('couponLabel'); ?></span><strong><?php echo $e('coupon'); ?></strong></div><a class="button" href="<?php echo $url('buttonUrl'); ?>"><?php echo $e('buttonLabel'); ?></a></div><small><?php echo $e('note'); ?></small></div><div class="promotion-image"><?php echo wtf_core_image($a, 'large', ['alt'=>'', 'loading'=>'lazy', 'sizes'=>'(max-width: 600px) calc(100vw - 40px), 700px']); ?><span class="promotion-discount"><?php echo $e('badge'); ?></span></div></section>
        <?php
    } elseif ($type==='about') { ?>
        <section class="about wrap" id="about"><div><h2><?php echo wtf_core_heading($a['title']); ?></h2></div><div class="about-copy"><p><?php echo $e('description'); ?></p><p class="muted"><?php echo $e('secondary'); ?></p><a class="text-link" href="<?php echo $url('buttonUrl'); ?>"><?php echo $e('buttonLabel'); ?></a></div></section>
        <?php
    } elseif ($type==='shop-invitation') { ?>
        <section class="shop-band wrap"><div><span class="eyebrow"><?php echo $e('eyebrow'); ?></span><h2><?php echo $e('title'); ?></h2></div><a class="button" href="<?php echo $url('buttonUrl'); ?>"><?php echo $e('buttonLabel'); ?></a></section>
        <?php
    } elseif ($type==='footer') { ?>
        <footer class="wrap footer site-footer"><div class="footer-main"><div class="footer-brand"><a href="<?php echo esc_url(home_url('/')); ?>"><?php echo $e('title'); ?></a><p><?php echo $e('description'); ?></p><a href="<?php echo esc_url('mailto:'.$a['email']); ?>"><?php echo $e('email'); ?></a></div><?php foreach(['navigation'=>'navigationLabel','information'=>'informationLabel'] as $group=>$label): ?><div class="footer-column"><h2><?php echo $e($label); ?></h2><?php foreach($a['items'] as $item): if(($item['group']??'')!==$group)continue; ?><a href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html(wtf_core_text($item['title'])); ?></a><?php endforeach; ?></div><?php endforeach; ?></div><div class="footer-bottom"><div>© <?php echo esc_html(wp_date('Y').' '.$a['copyright']); ?></div><div class="footer-payments"><span><?php echo esc_html(wtf_core_text('Płatności')); ?></span><img src="<?php echo $url('image'); ?>" alt="Visa, Mastercard, BLIK, Paynow" width="1024" height="138" loading="lazy"></div></div></footer>
        <?php
    }
    return ob_get_clean();
}
