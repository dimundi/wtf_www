<?php
if (!defined('ABSPATH')) exit;

// Adresy wybrane przez użytkownika; bez przekierowań z dawnych adresów produktów.
function wtf_product_custom_paths() {
    return [
        'gra-karciana-elekt-wydanie-2'=>'elekt/podstawka',
        'elekt-4-karty-specjalne-edycja-2023'=>'elekt/karty-specjalne-2023',
        'dodatek-wiedzmy-2'=>'elekt/wiedzmy',
        'dodatek-rubiez'=>'elekt/rubiez',
        'dodatek-powolanie-2'=>'elekt/powolanie-2',
        'dodatek-wiedzmy2'=>'elekt/wiedzmy-2',
        'elekt-memo-cube'=>'elekt/memo-cube',
        'pocztowki-komplet'=>'elekt/pocztowki',
        'plakat-zlodziej-cial'=>'elekt/plakat-zlodziej-cial',
        'mata-do-gry-2'=>'elekt/mata-upiory',
        'czerwona-sakiewka'=>'elekt/czerwona-sakiewka',
        'plakat-demonolog'=>'elekt/plakat-demonolog',
        'mata-do-gry'=>'elekt/mata-rubiez',
        'szklany-zar'=>'elekt/szklany-zar',
        'kurzolaki'=>'kurzolaki/podstawka',
        'kurzolaki-akrylowe-plamy'=>'kurzolaki/akrylowe-plamy',
        'kurzolaki-fanpack'=>'kurzolaki/fanpack',
        'sweet-revenge'=>'kurzolaki/sweet-revenge',
        'tsukuyumi'=>'tsukuyumi/podstawka',
        'tsukuyumi-zestaw-planszetek-nr-4'=>'tsukuyumi/planszetki-4',
        'tsukuyumi-sunrise-kingdom'=>'tsukuyumi/sunrise-kingdom',
        'tsukuyumi-chronomasters'=>'tsukuyumi/chronomasters',
        'tsukuyumi-gracz-double'=>'tsukuyumi/gracz-plus-2',
        'tsukuyumi-nowe-lady'=>'tsukuyumi/nowe-lady',
        'plastikowe-podstawki-25-szt'=>'tsukuyumi/plastikowe-podstawki-25',
        'tsukuyumi-planszetki-podst'=>'tsukuyumi/planszetki-1',
        'sentinels'=>'tsukuyumi/sentinels',
        'tsukuyumi-wum'=>'tsukuyumi/wladcy-utraconych-morz',
        'tsukuyumi-fireborn'=>'tsukuyumi/fireborn',
        'kg3'=>'tsukuyumi/kampfgruppe-03',
        'tsukuyumi-komiks'=>'tsukuyumi/komiks',
        'echo-swiatow-minstrel'=>'echo-swiatow/album',
        'echo-mp3'=>'echo-swiatow/mp3',
    ];
}
function wtf_product_path_for_post($post) {
    $path=wtf_product_custom_paths()[$post->post_name]??null;
    if ($path) return $path;
    $terms=get_the_terms($post->ID,'wtf_product_game');
    if (!$terms || is_wp_error($terms)) return null;
    $game=$terms[0]->slug;
    return $game.'/'.$post->post_name;
}
function wtf_product_path_replacements() {
    $map=[];
    foreach (wtf_product_custom_paths() as $slug=>$path) $map['/produkt/'.$slug.'/']='/produkt/'.$path.'/';
    $map['/produkt/elekt/mata-1/']='/produkt/elekt/mata-rubiez/';
    $map['/produkt/elekt/mata-2/']='/produkt/elekt/mata-upiory/';
    return $map;
}
add_action('init',function(){
    foreach (wtf_product_custom_paths() as $slug=>$path) {
        add_rewrite_rule('^produkt/'.preg_quote($path,'~').'/?$','index.php?post_type=product&name='.$slug,'top');
    }
    add_rewrite_rule('^produkt/([^/]+)/([^/]+)/?$','index.php?post_type=product&name=$matches[2]','top');
});
add_filter('post_type_link',function($url,$post){
    if ($post->post_type!=='product') return $url;
    $path=wtf_product_path_for_post($post);
    return $path?home_url('/produkt/'.$path.'/'):$url;
},20,2);
add_action('template_redirect',function(){
    if (!in_array($_SERVER['REQUEST_METHOD']??'GET',['GET','HEAD'],true)) return;
    $path=untrailingslashit((string)wp_parse_url($_SERVER['REQUEST_URI']??'',PHP_URL_PATH));
    $prefix=(string)wp_parse_url(home_url('/produkt/'),PHP_URL_PATH);
    if (!str_starts_with($path,$prefix)) return;
    $canonical=is_product()?untrailingslashit((string)wp_parse_url(get_permalink(get_queried_object_id()),PHP_URL_PATH)):null;
    if ((is_product() && $path!==$canonical) || is_404()) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            remove_action('template_redirect','redirect_canonical');
            remove_action('template_redirect','wp_old_slug_redirect');
            return;
    }
},1);
