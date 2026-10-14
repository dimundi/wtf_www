<?php
// Jednorazowa migracja treści prototypu. Ponowne uruchomienie zachowuje edycje.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
wp_set_current_user(1);
require_once ABSPATH.'wp-admin/includes/image.php';
$source = json_decode(file_get_contents(__DIR__.'/tsukuyumi-seed.json'), true, 512, JSON_THROW_ON_ERROR);
usort($source,static fn($a,$b)=>($a['key']==='main'?0:1)<=>($b['key']==='main'?0:1));
$mainExists=get_posts(['post_type'=>'page','post_status'=>['publish','draft'],'numberposts'=>1,'meta_key'=>'_wtf_tsu_key','meta_value'=>'main:pl','suppress_filters'=>true]);
if (!$mainExists) {
    foreach (get_posts(['post_type'=>'attachment','post_status'=>'inherit','name'=>'tsukuyumi','numberposts'=>-1,'suppress_filters'=>true]) as $attachment) {
        wp_update_post(['ID'=>$attachment->ID,'post_name'=>'tsukuyumi_pudelko']);
    }
}
$pages = [];
foreach ($source as $entry) {
    foreach (['pl','en'] as $lang) {
        $key = $entry['key'].':'.$lang;
        $existing = get_posts(['post_type'=>'page','post_status'=>['publish','draft','private','pending'],'numberposts'=>1,'meta_key'=>'_wtf_tsu_key','meta_value'=>$key,'suppress_filters'=>true]);
        $id = $existing ? $existing[0]->ID : wp_insert_post(wp_slash(['post_type'=>'page','post_status'=>$lang==='pl'?'publish':'draft','post_title'=>$entry['title'],'post_name'=>$entry['key']==='main'?'tsukuyumi'.($lang==='en'?'-en':''):$entry['key'],'post_parent'=>$entry['key']==='main'?0:$pages['main:'.$lang]]), true);
        if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
        update_post_meta($id, '_wtf_tsu_key', $key);
        update_post_meta($id, '_wtf_tsu_kind', $entry['key']==='main'?'main':'faction');
        update_post_meta($id, '_wp_page_template', 'tsukuyumi');
        if ($entry['key']!=='main') { update_post_meta($id, '_wtf_faction', $entry['key']); }
        pll_set_post_language($id, $lang);
        $pages[$key] = $id;
    }
    pll_save_post_translations(['pl'=>$pages[$entry['key'].':pl'], 'en'=>$pages[$entry['key'].':en']]);
}
$assets = ['tsu-weathered-hexes.svg','wtf-logo.svg'];
foreach ($source as $entry) {
    preg_match_all('~/uploads/media/([^"\x27?<>\s]+)~', $entry['pl'].$entry['en'], $matches);
    foreach ($matches[1] as $name) { $assets[] = rawurldecode($name); }
}
$media = [];
$existingMedia = [];
foreach (get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>-1,'orderby'=>'ID','order'=>'ASC','suppress_filters'=>true]) as $attachment) {
    $path = get_post_meta($attachment->ID,'_wp_attached_file',true);
    $meta = wp_get_attachment_metadata($attachment->ID);
    foreach (array_filter([$path, get_post_meta($attachment->ID,'_wtf_source_asset',true), !empty($meta['original_image']) ? dirname($path).'/'.$meta['original_image'] : '']) as $original) {
        if (!isset($existingMedia[$original])) { $existingMedia[$original]=$attachment->ID; }
    }
}
foreach (array_unique($assets) as $file) {
    $attached = 'media/'.$file;
    if (isset($existingMedia[$attached])) {
        $media[$file] = $existingMedia[$attached];
        update_post_meta($media[$file], '_wtf_source_asset', $attached);
        continue;
    }
    $path = wp_upload_dir()['basedir'].'/'.$attached;
    if (!is_file($path)) { WP_CLI::error('Brak pliku: '.$file); }
    $mime = str_ends_with($file, '.svg') ? 'image/svg+xml' : wp_check_filetype($file)['type'];
    $id = wp_insert_attachment(['post_title'=>pathinfo($file, PATHINFO_FILENAME),'post_mime_type'=>$mime,'post_status'=>'inherit'], $path, 0, true);
    if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
    update_attached_file($id, $path);
    update_post_meta($id, '_wtf_source_asset', $attached);
    if ($mime!=='image/svg+xml') { wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $path)); }
    $media[$file] = $id;
    WP_CLI::log('Media: '.$file);
}
function wtf_tsu_seed_block($name, $attrs, $children=[]) {
    return ['blockName'=>$name, 'attrs'=>$attrs, 'innerBlocks'=>$children, 'innerHTML'=>'', 'innerContent'=>array_fill(0,count($children),null)];
}
function wtf_tsu_seed_attributes($node) {
    $attrs = [];
    foreach ($node->attributes as $attr) { if (!in_array($attr->name,['style','onclick'],true)) { $attrs[$attr->name] = $attr->value; } }
    return $attrs;
}
function wtf_tsu_seed_html($node, $inner=false) {
    if (!$inner) { return $node->ownerDocument->saveHTML($node); }
    $html = ''; foreach ($node->childNodes as $child) { $html .= $node->ownerDocument->saveHTML($child); } return $html;
}
function wtf_tsu_seed_nodes($parent, $media) {
    $blocks = [];
    foreach ($parent->childNodes as $node) {
        if ($node instanceof DOMText) {
            if (trim($node->textContent)) { $blocks[] = wtf_tsu_seed_block('wtf/tsu-text',['tag'=>'span','content'=>esc_html($node->textContent)]); }
            continue;
        }
        if (!$node instanceof DOMElement) { continue; }
        $tag = strtolower($node->tagName);
        $attrs = wtf_tsu_seed_attributes($node);
        // Nawigacja i tabele zachowują źródłowy HTML; treść rozdziałów jest w blokach.
        if (in_array($tag,['aside','table','svg','button','br'],true)) {
            $html = wtf_tsu_seed_html($node);
            $blocks[] = ['blockName'=>'core/html','attrs'=>[], 'innerBlocks'=>[], 'innerHTML'=>$html,'innerContent'=>[$html]];
            continue;
        }
        if ($tag==='picture' || $tag==='img') {
            $img = $tag==='img' ? $node : $node->getElementsByTagName('img')->item(0);
            if (!$img) { continue; }
            $attrs = wtf_tsu_seed_attributes($img);
            $file = rawurldecode(basename(parse_url($img->getAttribute('src'),PHP_URL_PATH)));
            $data = ['image'=>wtf_core_media($file),'imageId'=>$media[$file]??0,'alt'=>$img->getAttribute('alt')];
            unset($attrs['src'],$attrs['alt']); $data['attributes']=$attrs;
            if ($tag==='picture') {
                $mobile = $node->getElementsByTagName('source')->item(0);
                if ($mobile) { $data['mobileId']=$media[basename($mobile->getAttribute('srcset'))]??0; }
            }
            $blocks[] = wtf_tsu_seed_block('wtf/tsu-image',$data); continue;
        }
        if ($tag==='iframe') {
            $blocks[] = wtf_tsu_seed_block('wtf/tsu-video',['url'=>$node->getAttribute('src'),'title'=>$node->getAttribute('title')?:'Tsukuyumi']); continue;
        }
        $hasLayout = false;
        foreach ($node->getElementsByTagName('*') as $child) {
            if (in_array($child->tagName,['p','div','section','article','img','iframe','details','table','ul','ol','dl','h1','h2','h3','h4'],true)) { $hasLayout=true; break; }
        }
        if (in_array($tag,['p','h1','h2','h3','h4','h5','h6','span','a','li','dt','dd','strong','em','small'],true) && !$hasLayout) {
            $blocks[] = wtf_tsu_seed_block('wtf/tsu-text',['tag'=>$tag,'content'=>wtf_tsu_seed_html($node,true),'attributes'=>$attrs]); continue;
        }
        $label = $attrs['id'] ?? ($attrs['class'] ?? $tag);
        if ($tag==='section') { $heading=$node->getElementsByTagName('h2')->item(0); if ($heading) { $label=$heading->textContent; } }
        $blocks[] = wtf_tsu_seed_block('wtf/tsu-container',['tag'=>$tag,'attributes'=>$attrs,'label'=>$label,'metadata'=>['name'=>$label]],wtf_tsu_seed_nodes($node,$media));
    }
    return $blocks;
}
foreach ($source as $entry) {
    foreach (['pl','en'] as $lang) {
        $id = $pages[$entry['key'].':'.$lang];
        if (get_post_meta($id,'_wtf_tsu_imported',true)) { WP_CLI::log('Zachowano edycje: '.$id); continue; }
        $html = $entry[$lang];
        foreach ($source as $linked) {
            $dest = get_permalink($pages[$linked['key'].':'.$lang]);
            $variants = [];
            foreach (['?','&','&amp;','&#038;','&#38;'] as $separator) {
                foreach (['pl','en'] as $oldLang) { $variants[]=$linked['sourceUrl'].$separator.'lang='.$oldLang; }
            }
            $variants[]=$linked['sourceUrl'];
            $html = str_replace($variants,$dest,$html);
        }
        $html = str_replace('http://localhost:8080/wp-content/uploads/media/',wp_upload_dir()['baseurl'].'/media/',$html);
        $html = str_replace('http://localhost:8080/?lang='.$lang,pll_home_url($lang),$html);
        $html = str_replace('http://localhost:8080/',pll_home_url($lang),$html);
        $doc = new DOMDocument('1.0','UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $main = $doc->getElementsByTagName('main')->item(0);
        if (!$main) { WP_CLI::error('Brak main.'); }
        $block = wtf_tsu_seed_block('wtf/tsu-container',['tag'=>'main','attributes'=>wtf_tsu_seed_attributes($main),'label'=>$entry['title']],wtf_tsu_seed_nodes($main,$media));
        $result = wp_update_post(wp_slash(['ID'=>$id,'post_content'=>serialize_block($block)]),true);
        if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
        update_post_meta($id,'_wtf_tsu_imported',1);
        WP_CLI::log('Strona '.$lang.': '.$id.' / '.$entry['title']);
    }
}
// Podmieniamy wyłącznie linki Tsukuyumi, zachowując pozostałe edycje użytkownika.
$url = get_permalink($pages['main:pl']);
foreach (get_posts(['post_type'=>['page','wp_template_part'],'post_status'=>['publish','draft'],'numberposts'=>-1,'suppress_filters'=>true]) as $post) {
    $content = str_replace('https://whatthefrog.pl/tsukuyumi/', $url, $post->post_content);
    if ($content!==$post->post_content) { wp_update_post(wp_slash(['ID'=>$post->ID,'post_content'=>$content])); }
}
flush_rewrite_rules();
WP_CLI::success('Tsukuyumi: 17 stron PL, powiązane szkice EN, media i linki.');
