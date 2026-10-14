<?php
if (!defined('ABSPATH')) { exit; }

function wtf_tsu_local_game_link($url) {
    if (untrailingslashit($url)!=='https://whatthefrog.pl/tsukuyumi') { return $url; }
    $languages = array_unique([wtf_core_language(),'pl']);
    foreach ($languages as $lang) {
        $pages=get_posts(['post_type'=>'page','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_wtf_tsu_key','meta_value'=>'main:'.$lang,'suppress_filters'=>true]);
        if ($pages) { return get_permalink($pages[0]); }
    }
    return $url;
}

add_action('init', function () {
    wp_register_script('wtf-tsu-editor', plugins_url('tsukuyumi-editor.js', __FILE__), ['wp-blocks','wp-element','wp-components','wp-block-editor'], filemtime(__DIR__.'/tsukuyumi-editor.js'), true);
    $common = ['api_version'=>3, 'category'=>'wtf', 'editor_script'=>'wtf-tsu-editor', 'supports'=>['html'=>false]];
    register_block_type('wtf/tsu-container', $common + [
        'title'=>'Układ Tsukuyumi',
        'attributes'=>['tag'=>['type'=>'string','default'=>'div'], 'attributes'=>['type'=>'object','default'=>[]], 'label'=>['type'=>'string','default'=>'Układ']],
        'render_callback'=>function ($a, $content) {
            $tag = in_array($a['tag'], ['main','section','div','article','aside','header','nav','details','summary','a','ul','ol','li','dl','dt','dd','picture'], true) ? $a['tag'] : 'div';
            return '<'.$tag.wtf_tsu_attributes($a['attributes']).'>'.$content.'</'.$tag.'>';
        },
    ]);
    register_block_type('wtf/tsu-text', $common + [
        'title'=>'Tekst Tsukuyumi',
        'attributes'=>['tag'=>['type'=>'string','default'=>'p'], 'content'=>['type'=>'string','default'=>''], 'attributes'=>['type'=>'object','default'=>[]]],
        'render_callback'=>function ($a) {
            $tag = in_array($a['tag'], ['p','h1','h2','h3','h4','h5','h6','span','a','li','dt','dd','strong','em','small','figcaption'], true) ? $a['tag'] : 'p';
            return '<'.$tag.wtf_tsu_attributes($a['attributes']).'>'.wp_kses_post($a['content']).'</'.$tag.'>';
        },
    ]);
    register_block_type('wtf/tsu-image', $common + [
        'title'=>'Grafika Tsukuyumi',
        'attributes'=>['image'=>['type'=>'string','default'=>''], 'imageId'=>['type'=>'number','default'=>0], 'mobileId'=>['type'=>'number','default'=>0], 'alt'=>['type'=>'string','default'=>''], 'attributes'=>['type'=>'object','default'=>[]]],
        'render_callback'=>function ($a) {
            $attrs = array_merge($a['attributes'], ['alt'=>$a['alt'], 'sizes'=>'(max-width: 650px) 100vw, 1200px']);
            $html = wtf_core_image($a, 'full', $attrs);
            if ($a['mobileId']) {
                $src = wp_get_attachment_image_src($a['mobileId'], 'full');
                if ($src) {
                    $set = wp_get_attachment_image_srcset($a['mobileId'], 'full') ?: $src[0];
                    return '<picture class="tsu-panorama"><source media="(max-width:650px)" srcset="'.esc_attr($set).'" sizes="100vw">'.$html.'</picture>';
                }
            }
            return $html;
        },
    ]);
    register_block_type('wtf/tsu-video', $common + [
        'title'=>'Film Tsukuyumi',
        'attributes'=>['url'=>['type'=>'string','default'=>''], 'title'=>['type'=>'string','default'=>'Film Tsukuyumi']],
        'render_callback'=>function ($a) {
            if (!preg_match('~^https://www\.youtube-nocookie\.com/embed/[a-zA-Z0-9_-]+(?:\?.*)?$~', $a['url'])) { return ''; }
            return '<iframe src="'.esc_url($a['url']).'" title="'.esc_attr($a['title']).'" loading="lazy" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
        },
    ]);
});

function wtf_tsu_attributes($attrs) {
    $html = '';
    foreach ($attrs as $key=>$value) {
        if (!in_array($key, ['class','id','lang','href','title','role','open','aria-label','aria-current','aria-controls','aria-expanded'], true)) { continue; }
        if ($key === 'href') { $value = esc_url($value); }
        $html .= ' '.esc_attr($key).'="'.esc_attr($value).'"';
    }
    return $html;
}
