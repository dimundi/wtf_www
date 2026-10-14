<?php
if (!defined('ABSPATH')) { exit; }

function wtf_contact_local_game_link($url) {
    if (untrailingslashit($url)!=='https://whatthefrog.pl/kontakt') { return $url; }
    $languages = array_unique([wtf_core_language(),'pl']);
    foreach ($languages as $lang) {
        $pages=get_posts(['post_type'=>'page','post_status'=>'publish','numberposts'=>1,'meta_key'=>'_wtf_contact_key','meta_value'=>'main:'.$lang,'lang'=>$lang,'suppress_filters'=>true]);
        if ($pages) { return get_permalink($pages[0]); }
    }
    return $url;
}

add_action('init', function () {
    wp_register_script('wtf-contact-editor', plugins_url('contact-editor.js', __FILE__), ['wp-blocks','wp-element','wp-components','wp-block-editor'], filemtime(__DIR__.'/contact-editor.js'), true);
    $common = ['api_version'=>3, 'category'=>'wtf', 'editor_script'=>'wtf-contact-editor', 'supports'=>['html'=>false]];
    register_block_type('wtf/contact-container', $common + [
        'title'=>'Układ kontaktu',
        'attributes'=>['tag'=>['type'=>'string','default'=>'div'], 'attributes'=>['type'=>'object','default'=>[]], 'label'=>['type'=>'string','default'=>'Układ']],
        'render_callback'=>function ($a, $content) {
            $tag = in_array($a['tag'], ['main','section','div','article','aside','header','nav','details','summary','a','ul','ol','li','dl','dt','dd','picture','label','blockquote','figure','h1','h2','h3','address'], true) ? $a['tag'] : 'div';
            return '<'.$tag.wtf_contact_attributes($a['attributes']).'>'.$content.'</'.$tag.'>';
        },
    ]);
    register_block_type('wtf/contact-text', $common + [
        'title'=>'Tekst kontaktu',
        'attributes'=>['tag'=>['type'=>'string','default'=>'p'], 'content'=>['type'=>'string','default'=>''], 'attributes'=>['type'=>'object','default'=>[]]],
        'render_callback'=>function ($a) {
            $tag = in_array($a['tag'], ['p','h1','h2','h3','h4','h5','h6','span','a','li','dt','dd','strong','em','small','figcaption','label','address'], true) ? $a['tag'] : 'p';
            return '<'.$tag.wtf_contact_attributes($a['attributes']).'>'.wp_kses_post($a['content']).'</'.$tag.'>';
        },
    ]);
});
function wtf_contact_attributes($attrs) {
    $html = '';
    foreach ($attrs as $key=>$value) {
        if ($key==='style' && preg_match('/^--expansion-color:#[a-fA-F0-9]{6}$/',trim($value))) { $html.=' style="'.esc_attr(trim($value)).'"'; continue; }
        if (!in_array($key, ['class','id','lang','href','for','target','rel','title','role','open','aria-label','aria-current','aria-controls','aria-expanded'], true)) { continue; }
        if ($key === 'href') { $value = esc_url($value); }
        $html .= ' '.esc_attr($key).'="'.esc_attr($value).'"';
    }
    return $html;
}

add_action('init',function(){
 register_block_type('wtf/contact-form',['api_version'=>3,'title'=>'Formularz kontaktowy — podgląd','category'=>'wtf','editor_script'=>'wtf-contact-editor','supports'=>['html'=>false],
 'attributes'=>array_fill_keys(['nameLabel','emailLabel','subjectLabel','messageLabel','buttonLabel'],['type'=>'string','default'=>'']),
 'render_callback'=>function($a){
  $e=fn($k)=>esc_html($a[$k]);
  return '<form class="contact-form" aria-describedby="contact-form-status"><fieldset disabled><div class="contact-form-row"><div><label for="contact-name">'.$e('nameLabel').'</label><input id="contact-name" type="text" name="name" autocomplete="given-name"></div><div><label for="contact-mail">'.$e('emailLabel').'</label><input id="contact-mail" type="email" name="email" autocomplete="email"></div></div><label for="contact-subject">'.$e('subjectLabel').'</label><input id="contact-subject" type="text" name="subject"><label for="contact-message">'.$e('messageLabel').'</label><textarea id="contact-message" name="message" rows="7"></textarea><button type="button" class="button" disabled>'.$e('buttonLabel').'</button></fieldset></form>';
 }]);
});
