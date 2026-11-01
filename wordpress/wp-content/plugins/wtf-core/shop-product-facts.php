<?php
if(!defined('ABSPATH'))exit;

function wtf_product_fact_fields(){
    return ['language'=>'Język zawartości (np. PL / EN)','language_note'=>'Dopisek do języka (gwiazdka i dymek)','players_min'=>'Liczba graczy — od','players_max'=>'Liczba graczy — do','players_note'=>'Dopisek do liczby graczy (gwiazdka i dymek)','minutes_min'=>'Czas w minutach — od','minutes_max'=>'Czas w minutach — do','age'=>'Wiek minimalny','requires'=>'Wymaga podstawki (nazwa gry)','compatible'=>'Zgodność z grą (np. Kurzołaki)'];
}
add_filter('woocommerce_product_data_tabs',function($tabs){$tabs['wtf_facts']=['label'=>'Metryczka','target'=>'wtf-product-facts-panel','priority'=>75];return$tabs;});
add_action('woocommerce_product_data_panels',function(){
    global$product_object;if(!$product_object)return;
    echo '<div id="wtf-product-facts-panel" class="panel woocommerce_options_panel hidden">';wp_nonce_field('wtf_product_facts','wtf_product_facts_nonce');
    woocommerce_wp_checkbox(['id'=>'_wtf_facts_enabled','label'=>'Pokaż metryczkę','value'=>$product_object->get_meta('_wtf_facts_enabled'),'description'=>'Pod przyciskiem dodania do koszyka. Puste parametry pomijamy.']);
    foreach(wtf_product_fact_fields()as$key=>$label){$number=in_array($key,['players_min','players_max','minutes_min','minutes_max','age'],true);woocommerce_wp_text_input(['id'=>'_wtf_fact_'.$key,'label'=>$label,'type'=>$number?'number':'text','value'=>$product_object->get_meta('_wtf_fact_'.$key),'custom_attributes'=>$number?['min'=>'1','step'=>'1']:['maxlength'=>'100']]);}
    echo '<p class="form-field">Język dotyczy zawartości produktu, nie języka strony. Identyczne minimum i maksimum wyświetlą jedną liczbę. Przy czasie podaj orientacyjną długość rozgrywki. Dla akcesoriów nie wypełniaj liczby graczy, czasu ani wieku.</p></div>';
});
add_action('woocommerce_admin_process_product_object',function($product){
    if(!current_user_can('edit_post',$product->get_id())||empty($_POST['wtf_product_facts_nonce'])||!is_string($_POST['wtf_product_facts_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtf_product_facts_nonce'])),'wtf_product_facts'))return;
    $values=[];foreach(wtf_product_fact_fields()as$key=>$label){$raw=$_POST['_wtf_fact_'.$key]??'';$value=is_string($raw)?sanitize_text_field(wp_unslash($raw)):'';$values[$key]=in_array($key,['players_min','players_max','minutes_min','minutes_max','age'],true)?($value!==''&&ctype_digit($value)&&intval($value)>0?(string)intval($value):''):mb_substr($value,0,100);}
    foreach(['players','minutes']as$range)if($values[$range.'_max']!==''&&($values[$range.'_min']===''||(int)$values[$range.'_max']<(int)$values[$range.'_min'])){WC_Admin_Meta_Boxes::add_error('Metryczka: maksimum wymaga minimum i nie może być od niego mniejsze. Poprzednia metryczka została zachowana.');return;}
    $product->update_meta_data('_wtf_facts_enabled',isset($_POST['_wtf_facts_enabled'])?'yes':'no');foreach($values as$key=>$value)$product->update_meta_data('_wtf_fact_'.$key,$value);
});

function wtf_product_facts_render(){
    global$product;if(!$product instanceof WC_Product||$product->get_meta('_wtf_facts_enabled')!=='yes')return;
    static $rendered=[];if(isset($rendered[$product->get_id()]))return;
    $en=function_exists('wtf_core_language')&&wtf_core_language()==='en';
    $labels=$en?['language'=>'Product language','players'=>'Number of players','minutes'=>'Approximate playing time','age'=>'Minimum age','requires'=>'Requires the base game','compatible'=>'Compatible with','group'=>'Product details']:['language'=>'Wersja językowa produktu','players'=>'Liczba graczy','minutes'=>'Orientacyjny czas rozgrywki','age'=>'Minimalny wiek gracza','requires'=>'Wymaga podstawowej gry','compatible'=>'Zgodność z grą','group'=>'Parametry produktu'];
    $get=fn($key)=>trim((string)$product->get_meta('_wtf_fact_'.$key));$facts=[];
    if($get('language')!==''){
        $facts['language']=$get('language');
        if(preg_match('/\bPL\b/i',$facts['language'])&&preg_match('/\bEN\b/i',$facts['language']))$labels['language']=preg_match('/\bDE\b/i',$facts['language'])?($en?'Trilingual product — includes Polish, English and German versions':'Produkt trójjęzyczny — zawiera wersję polską, angielską i niemiecką'):($en?'Bilingual product — includes both Polish and English versions':'Produkt dwujęzyczny — zawiera wersję polską i angielską');
    }
    foreach(['players','minutes']as$key){$min=$get($key.'_min');$max=$get($key.'_max');if($min!=='')$facts[$key]=$min.($max!==''&&$max!==$min?'–'.$max:'').($key==='minutes'?' min':'');}
    if($get('age')!=='')$facts['age']=$get('age').'+';
    if($get('requires')!=='')$facts['requires']=($en?'Requires: ':'Wymaga: ').$get('requires');
    if($get('compatible')!=='')$facts['compatible']=($en?'For: ':'Do: ').$get('compatible');
    if(!$facts)return;
    $rendered[$product->get_id()]=true;
    foreach(['players','language']as$key)if(isset($facts[$key])&&$get($key.'_note')!=='')$facts[$key].='*';
    $icons=[
        'language'=>'<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a17 17 0 0 1 0 18 17 17 0 0 1 0-18Z"/>',
        'players'=>'<circle cx="9" cy="7" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M17 4a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 4v3"/>',
        'minutes'=>'<circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/>',
        'age'=>'<path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6Z"/><path d="m8 12 3 3 5-6"/>',
        'requires'=>'<path d="M4 4h16v16H4ZM4 9h16M9 4v16"/>',
        'compatible'=>'<path d="m9 15 6-6M8 16l-1 1a4 4 0 0 1-6-6l4-4a4 4 0 0 1 6 0M16 8l1-1a4 4 0 0 1 6 6l-4 4a4 4 0 0 1-6 0"/>',
    ];
    echo '<div class="wtf-product-facts" role="group" aria-label="'.esc_attr($labels['group']).'">';
    foreach($facts as$key=>$value){$id=wp_unique_id('wtf-fact-');$note=in_array($key,['players','language'],true)?$get($key.'_note'):'';echo '<div class="wtf-product-fact"><button type="button" class="wtf-product-fact__trigger" aria-label="'.esc_attr($labels[$key].': '.$value).'" aria-describedby="'.esc_attr($id).'" aria-expanded="false"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$icons[$key].'</svg><span>'.esc_html($value).'</span></button><span id="'.esc_attr($id).'" class="wtf-product-fact__tip" role="tooltip" hidden>'.esc_html($labels[$key]).($note!==''?'<span class="wtf-product-fact__note">• '.esc_html($note).'</span>':'').'</span></div>';}
    echo '</div>';
}
add_action('woocommerce_after_add_to_cart_form','wtf_product_facts_render');
add_action('woocommerce_single_product_summary','wtf_product_facts_render',31);
add_action('wp_enqueue_scripts',function(){
    if(!function_exists('is_product')||!is_product()||get_post_meta(get_queried_object_id(),'_wtf_facts_enabled',true)!=='yes')return;
    wp_enqueue_style('wtf-product-facts',plugins_url('assets/product-facts.css',__FILE__),[],filemtime(__DIR__.'/assets/product-facts.css'));
    wp_enqueue_script('wtf-product-facts',plugins_url('assets/product-facts.js',__FILE__),[],filemtime(__DIR__.'/assets/product-facts.js'),true);
});
