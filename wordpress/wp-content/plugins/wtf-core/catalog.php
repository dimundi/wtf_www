<?php
if (!defined('ABSPATH')) { exit; }

function wtf_catalog_local_game_link($url) {
    if (untrailingslashit($url)!=='https://whatthefrog.pl/katalog') { return $url; }
    $languages = array_unique([wtf_core_language(),'pl']);
    foreach ($languages as $lang) {
        $pages=get_posts(['post_type'=>'page','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_wtf_catalog_key','meta_value'=>'main:'.$lang,'lang'=>$lang,'suppress_filters'=>true]);
        if ($pages) { return get_permalink($pages[0]); }
    }
    return $url;
}

add_action('init', function () {
    wp_register_script('wtf-catalog-editor', plugins_url('catalog-editor.js', __FILE__), ['wp-blocks','wp-element','wp-components','wp-block-editor'], filemtime(__DIR__.'/catalog-editor.js'), true);
    $common = ['api_version'=>3, 'category'=>'wtf', 'editor_script'=>'wtf-catalog-editor', 'supports'=>['html'=>false]];
    register_block_type('wtf/catalog-container', $common + [
        'title'=>'Układ katalogu',
        'attributes'=>['tag'=>['type'=>'string','default'=>'div'], 'attributes'=>['type'=>'object','default'=>[]], 'label'=>['type'=>'string','default'=>'Układ']],
        'render_callback'=>function ($a, $content) {
            $tag = in_array($a['tag'], ['main','section','div','article','aside','header','nav','details','summary','a','ul','ol','li','dl','dt','dd','picture','label','blockquote','figure','h1','h2','h3'], true) ? $a['tag'] : 'div';
            return '<'.$tag.wtf_catalog_attributes($a['attributes']).'>'.$content.'</'.$tag.'>';
        },
    ]);
    register_block_type('wtf/catalog-text', $common + [
        'title'=>'Tekst katalogu',
        'attributes'=>['tag'=>['type'=>'string','default'=>'p'], 'content'=>['type'=>'string','default'=>''], 'attributes'=>['type'=>'object','default'=>[]]],
        'render_callback'=>function ($a) {
            $tag = in_array($a['tag'], ['p','h1','h2','h3','h4','h5','h6','span','a','li','dt','dd','strong','em','small','figcaption','label'], true) ? $a['tag'] : 'p';
            return '<'.$tag.wtf_catalog_attributes($a['attributes']).'>'.wp_kses_post($a['content']).'</'.$tag.'>';
        },
    ]);
    register_block_type('wtf/catalog-image', $common + [
        'title'=>'Grafika katalogu',
        'attributes'=>['image'=>['type'=>'string','default'=>''], 'imageId'=>['type'=>'number','default'=>0], 'mobileId'=>['type'=>'number','default'=>0], 'alt'=>['type'=>'string','default'=>''], 'attributes'=>['type'=>'object','default'=>[]]],
        'render_callback'=>function ($a) {
            $attrs = array_merge($a['attributes'], ['alt'=>$a['alt'], 'sizes'=>'(max-width: 600px) calc(100vw - 40px), 800px']);
            $html = wtf_core_image($a, 'full', $attrs);
            if ($a['mobileId']) {
                $src = wp_get_attachment_image_src($a['mobileId'], 'full');
                if ($src) {
                    $set = wp_get_attachment_image_srcset($a['mobileId'], 'full') ?: $src[0];
                    return '<picture class="ele-panorama"><source media="(max-width:650px)" srcset="'.esc_attr($set).'" sizes="100vw">'.$html.'</picture>';
                }
            }
            return $html;
        },
    ]);
    register_block_type('wtf/catalog-video', $common + [
        'title'=>'Film katalogu',
        'attributes'=>['url'=>['type'=>'string','default'=>''], 'title'=>['type'=>'string','default'=>'Film katalogu'],'attributes'=>['type'=>'object','default'=>[]]],
        'render_callback'=>function ($a) {
            if (!preg_match('~^https://www\.youtube-nocookie\.com/embed/[a-zA-Z0-9_-]+(?:\?.*)?$~', $a['url'])) { return ''; }
            return '<iframe'.wtf_catalog_attributes($a['attributes']).' src="'.esc_url($a['url']).'" title="'.esc_attr($a['title']).'" loading="lazy" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        },
    ]);
    register_block_type('wtf/catalog-audio', $common + [
        'title'=>'Nagranie katalogu',
        'attributes'=>['url'=>['type'=>'string','default'=>''],'fileId'=>['type'=>'number','default'=>0],'label'=>['type'=>'string','default'=>''],'mime'=>['type'=>'string','default'=>'audio/mpeg'],'attributes'=>['type'=>'object','default'=>[]]],
        'render_callback'=>function($a) {
            $url=$a['fileId']?wp_get_attachment_url($a['fileId']):$a['url'];
            $mime=$a['fileId']?get_post_mime_type($a['fileId']):$a['mime'];
            if(!$url||!str_starts_with((string)$mime,'audio/'))return '';
            return '<audio'.wtf_catalog_attributes($a['attributes']).' controls preload="none" aria-label="'.esc_attr($a['label']).'"><source src="'.esc_url($url).'" type="'.esc_attr($mime).'"></audio>';
        },
    ]);

});

function wtf_catalog_attributes($attrs) {
    $html = '';
    foreach ($attrs as $key=>$value) {
        if ($key==='style' && preg_match('/^--expansion-color:#[a-fA-F0-9]{6}$/',trim($value))) { $html.=' style="'.esc_attr(trim($value)).'"'; continue; }
        if (!in_array($key, ['class','id','lang','href','for','target','rel','title','role','open','aria-label','aria-current','aria-controls','aria-expanded'], true)) { continue; }
        if ($key === 'href') { $value = esc_url($value); }
        $html .= ' '.esc_attr($key).'="'.esc_attr($value).'"';
    }
    return $html;
}
