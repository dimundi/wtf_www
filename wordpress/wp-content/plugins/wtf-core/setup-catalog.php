<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
wp_set_current_user(1);
require_once ABSPATH.'wp-admin/includes/image.php';
$source=json_decode(file_get_contents(__DIR__.'/catalog-seed.json'),true,512,JSON_THROW_ON_ERROR);
$pages=[];
foreach($source as $entry) {
    foreach(['pl','en'] as $lang) {
        $key=$entry['key'].':'.$lang;
        $existing=get_posts(['post_type'=>'page','post_status'=>['publish','draft','private','pending'],'numberposts'=>1,'meta_key'=>'_wtf_catalog_key','meta_value'=>$key,'suppress_filters'=>true]);
        if(!$existing) {
            $slug=$entry['key']==='main'?'katalog'.($lang==='en'?'-en':''):$entry['key'];
            foreach(get_posts(['post_type'=>'attachment','post_status'=>'inherit','name'=>$slug,'numberposts'=>-1,'suppress_filters'=>true]) as $a) wp_update_post(['ID'=>$a->ID,'post_name'=>$slug.'_grafika']);
            $id=wp_insert_post(wp_slash(['post_type'=>'page','post_title'=>$entry['title'],'post_status'=>$lang==='pl'?'publish':'draft','post_name'=>$slug,'post_parent'=>$entry['key']==='main'?0:$pages['main:'.$lang]]),true);
        } else $id=$existing[0]->ID;
        if(is_wp_error($id)) WP_CLI::error($id->get_error_message());
        update_post_meta($id,'_wtf_catalog_key',$key);
        update_post_meta($id,'_wtf_catalog_kind',$entry['key']==='main'?'main':'expansion');
        update_post_meta($id,'_wp_page_template','catalog');
        pll_set_post_language($id,$lang);$pages[$key]=$id;
    }
    pll_save_post_translations(['pl'=>$pages[$entry['key'].':pl'],'en'=>$pages[$entry['key'].':en']]);
}
$assets = ['wtf-logo.svg'];
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
function wtf_catalog_seed_block($name, $attrs, $children=[]) {
    return ['blockName'=>$name, 'attrs'=>$attrs, 'innerBlocks'=>$children, 'innerHTML'=>'', 'innerContent'=>array_fill(0,count($children),null)];
}
function wtf_catalog_seed_attributes($node) {
    $attrs = [];
    foreach ($node->attributes as $attr) { if (!in_array($attr->name,['onclick'],true)) { $attrs[$attr->name] = $attr->value; } }
    return $attrs;
}
function wtf_catalog_seed_html($node, $inner=false) {
    if (!$inner) { return $node->ownerDocument->saveHTML($node); }
    $html = ''; foreach ($node->childNodes as $child) { $html .= $node->ownerDocument->saveHTML($child); } return $html;
}
function wtf_catalog_seed_nodes($parent, $media) {
    $blocks = [];
    foreach ($parent->childNodes as $node) {
        if ($node instanceof DOMText) {
            if (trim($node->textContent)) { $blocks[] = wtf_catalog_seed_block('wtf/catalog-text',['tag'=>'span','content'=>esc_html($node->textContent)]); }
            continue;
        }
        if (!$node instanceof DOMElement) { continue; }
        $tag = strtolower($node->tagName);
        $attrs = wtf_catalog_seed_attributes($node);
        // Nawigacja i tabele zachowują źródłowy HTML; treść rozdziałów jest w blokach.
        if (in_array($tag,['table','svg','button','br'],true)) {
            $html = wtf_catalog_seed_html($node);
            $blocks[] = ['blockName'=>'core/html','attrs'=>[], 'innerBlocks'=>[], 'innerHTML'=>$html,'innerContent'=>[$html]];
            continue;
        }
        if ($tag==='picture' || $tag==='img') {
            $img = $tag==='img' ? $node : $node->getElementsByTagName('img')->item(0);
            if (!$img) { continue; }
            $attrs = wtf_catalog_seed_attributes($img);
            $file = rawurldecode(basename(parse_url($img->getAttribute('src'),PHP_URL_PATH)));
            $data = ['image'=>wtf_core_media($file),'imageId'=>$media[$file]??0,'alt'=>$img->getAttribute('alt')];
            if($img->parentNode instanceof DOMElement && str_contains($img->parentNode->getAttribute('class'),'catalog-feature'))$attrs['class']='catalog-feature-art';
            unset($attrs['src'],$attrs['alt']); $data['attributes']=$attrs;
            if ($tag==='picture') {
                $mobile = $node->getElementsByTagName('source')->item(0);
                if ($mobile) { $data['mobileId']=$media[basename($mobile->getAttribute('srcset'))]??0; }
            }
            $blocks[] = wtf_catalog_seed_block('wtf/catalog-image',$data); continue;
        }
        
        if ($tag==='audio') {
            $audioSource=$node->getElementsByTagName('source')->item(0);
            if(!$audioSource)continue;
            $file=rawurldecode(basename(parse_url($audioSource->getAttribute('src'),PHP_URL_PATH)));
            $label=$node->ownerDocument->getElementsByTagName('label')->item(0);if($label&&!$node->getAttribute('aria-label'))$node->setAttribute('aria-label',trim($label->textContent));
            $blocks[]=wtf_catalog_seed_block('wtf/catalog-audio',['url'=>wtf_core_media($file),'fileId'=>$media[$file]??0,'label'=>$node->getAttribute('aria-label'),'mime'=>$audioSource->getAttribute('type'),'attributes'=>array_diff_key($attrs,['controls'=>1,'preload'=>1,'aria-label'=>1])]);
            continue;
        }
        if ($tag==='iframe') {
            $blocks[] = wtf_catalog_seed_block('wtf/catalog-video',['url'=>$node->getAttribute('src'),'title'=>$node->getAttribute('title')?:'Katalog','attributes'=>array_diff_key($attrs,['src'=>1,'title'=>1,'loading'=>1,'allow'=>1,'allowfullscreen'=>1])]); continue;
        }
        $hasLayout = false;
        foreach ($node->getElementsByTagName('*') as $child) {
            if (in_array($child->tagName,['p','div','section','article','img','iframe','details','table','ul','ol','dl','h1','h2','h3','h4'],true)) { $hasLayout=true; break; }
        }
        if (in_array($tag,['p','h1','h2','h3','h4','h5','h6','span','a','li','dt','dd','strong','em','small','figcaption','label'],true) && !$hasLayout) {
            $blocks[] = wtf_catalog_seed_block('wtf/catalog-text',['tag'=>$tag,'content'=>wtf_catalog_seed_html($node,true),'attributes'=>$attrs]); continue;
        }
        $label = $attrs['id'] ?? ($attrs['class'] ?? $tag);
        if ($tag==='section') { $heading=$node->getElementsByTagName('h2')->item(0); if ($heading) { $label=$heading->textContent; } }
        $blocks[] = wtf_catalog_seed_block('wtf/catalog-container',['tag'=>$tag,'attributes'=>$attrs,'label'=>$label,'metadata'=>['name'=>$label]],wtf_catalog_seed_nodes($node,$media));
    }
    return $blocks;
}
foreach ($source as $entry) {
    foreach (['pl','en'] as $lang) {
        $id = $pages[$entry['key'].':'.$lang];
        if (get_post_meta($id,'_wtf_catalog_imported',true)) { WP_CLI::log('Zachowano edycje: '.$id); continue; }
        
        $html = $entry[$lang];
        $gameKeys=['elekt'=>'_wtf_ele_key','tsukuyumi'=>'_wtf_tsu_key','kurzolaki'=>'_wtf_kurz_key','echo'=>'_wtf_echo_key'];
        foreach($entry['links'][$lang] as $game=>$old) {
            $linked=get_posts(['post_type'=>'page','post_status'=>'publish','numberposts'=>1,'meta_key'=>$gameKeys[$game],'meta_value'=>'main:pl','suppress_filters'=>true]);
            if(!$linked)WP_CLI::error('Brak gry: '.$game);
            $html=str_replace([esc_url($old),$old],get_permalink($linked[0]),$html);
        }
        foreach ($source as $linked) {
            $dest = get_permalink($pages[$linked['key'].':'.$lang]);
            $variants = [];
            foreach (['?','&','&amp;','&#038;','&#38;'] as $separator) {
                foreach (['pl','en'] as $oldLang) { $variants[]=$linked['sourceUrl'].$separator.'lang='.$oldLang; }
            }
            $variants[]=$linked['sourceUrl'];
            $html = str_replace($variants,$dest,$html);
        }
        $html = preg_replace('~http://localhost:8080/\?page_id=21(?:(?:&|&#038;|&amp;)lang=(?:pl|en))?~',pll_home_url($lang).'#games',$html);
        $html = str_replace('http://localhost:8080/wp-content/uploads/media/',wp_upload_dir()['baseurl'].'/media/',$html);
        $html = str_replace('http://localhost:8080/?lang='.$lang,pll_home_url($lang),$html);
        $html = str_replace('http://localhost:8080/',pll_home_url($lang),$html);
        $doc = new DOMDocument('1.0','UTF-8');
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $main = $doc->getElementsByTagName('main')->item(0);
        if (!$main) { WP_CLI::error('Brak main.'); }
        $block = wtf_catalog_seed_block('wtf/catalog-container',['tag'=>'main','attributes'=>wtf_catalog_seed_attributes($main),'label'=>$entry['title']],wtf_catalog_seed_nodes($main,$media));
        $result = wp_update_post(wp_slash(['ID'=>$id,'post_content'=>serialize_block($block)]),true);
        if (is_wp_error($result)) { WP_CLI::error($result->get_error_message()); }
        update_post_meta($id,'_wtf_catalog_imported',1);
        WP_CLI::log('Strona '.$lang.': '.$id.' / '.$entry['title']);
    }
}

// Update existing links without replacing edited graphics or text.
foreach(get_posts(['post_type'=>['page','wp_template_part'],'post_status'=>['publish','draft'],'numberposts'=>-1,'suppress_filters'=>true]) as $post) {
    $map=[];
    foreach(array_reverse($source) as $entry) $map['https://whatthefrog.pl/katalog/'.($entry['key']==='main'?'':$entry['key'].'/')]=get_permalink($pages[$entry['key'].':pl']);
    $content=strtr($post->post_content,$map);
    if($content!==$post->post_content) wp_update_post(wp_slash(['ID'=>$post->ID,'post_content'=>$content]));
}
function wtf_catalog_restore_returns($blocks,$url,$inside=false) {
    foreach($blocks as &$block) {
        $classes=explode(' ',$block['attrs']['attributes']['class']??'');
        $here=$inside||in_array('dominion-exit',$classes,true)||in_array('dust-end',$classes,true);
        if($here&&isset($block['attrs']['attributes']['href'])&&str_ends_with($block['attrs']['attributes']['href'],'#games'))$block['attrs']['attributes']['href']=$url;
        $block['innerBlocks']=wtf_catalog_restore_returns($block['innerBlocks'],$url,$here);
    }
    unset($block);return $blocks;
}
foreach(get_posts(['post_type'=>'page','post_status'=>['publish','draft'],'numberposts'=>-1,'meta_query'=>['relation'=>'OR',['key'=>'_wtf_ele_kind','value'=>'main'],['key'=>'_wtf_kurz_kind','value'=>'main']],'suppress_filters'=>true]) as $post) {
    $content=serialize_blocks(wtf_catalog_restore_returns(parse_blocks($post->post_content),get_permalink($pages['main:pl'])));
    if($content!==$post->post_content)wp_update_post(wp_slash(['ID'=>$post->ID,'post_content'=>$content]));
}
flush_rewrite_rules();
WP_CLI::success('Katalog: PL i powiązany szkic EN, grafiki i odsłuch.');
