<?php
/**
 * Plugin Name: WTF Banery
 * Description: Uniwersalne banery z miejscami emisji, harmonogramem i dwoma układami.
 * Version: 0.1.0
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) exit;

add_action('init', function () {
    register_post_type('wtfb_banner', [
        'labels'=>['name'=>'WTF Banery','singular_name'=>'Baner','add_new_item'=>'Dodaj baner','edit_item'=>'Edytuj baner'],
        'public'=>false,'show_ui'=>true,'menu_icon'=>'dashicons-format-image','supports'=>['title','page-attributes'],
        'capabilities'=>array_fill_keys(['edit_posts','edit_others_posts','publish_posts','read_private_posts','delete_posts','delete_others_posts','edit_published_posts','delete_published_posts'],'manage_options'),
        'map_meta_cap'=>true,
    ]);
});

function wtfb_places() {
    $places=['glowna'=>'Strona główna','sklep'=>'Sklep','produkt'=>'Przy cenie wybranych produktów'];
    if (taxonomy_exists('wtf_product_game')) {
        $terms=get_terms(['taxonomy'=>'wtf_product_game','hide_empty'=>false]);
        if (!is_wp_error($terms)) foreach ($terms as $term) $places['gra:'.$term->slug]='Strona gry: '.$term->name;
    }
    return apply_filters('wtfb_places',$places);
}

function wtfb_date($value) {
    $date=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',(string)$value,wp_timezone());
    return $date && $date->format('Y-m-d\TH:i')===$value ? $date->getTimestamp() : 0;
}

function wtfb_text($value) {
    return wp_kses($value,['a'=>['href'=>true,'title'=>true],'strong'=>[],'em'=>[],'br'=>[],'p'=>[]],['http','https']);
}

add_action('add_meta_boxes',function () {
    add_meta_box('wtfb-settings','Zawartość i emisja banera',function ($post) {
        wp_nonce_field('wtfb_save','wtfb_nonce');
        $get=fn($key)=>get_post_meta($post->ID,'_wtfb_'.$key,true);
        echo '<p>Nazwa powyżej jest robocza. Kolejność ustawisz w polu „Kolejność” — niższa liczba wyświetla się wcześniej.</p>';
        echo '<p><label><input type="checkbox" name="wtfb[enabled]" value="1" '.checked($get('enabled'),'1',false).'> Włącz baner</label></p>';
        echo '<p><label><strong>Język emisji</strong><br><select name="wtfb[language]">';
        foreach(['pl'=>'Polski','en'=>'Angielski','all'=>'Wszystkie języki']as$value=>$label)echo '<option value="'.esc_attr($value).'" '.selected($get('language')?:'pl',$value,false).'>'.esc_html($label).'</option>';
        echo '</select></label></p><p>Osobne treści i grafiki PL/EN dodaj jako osobne banery z tym samym miejscem emisji. Istniejące banery domyślnie pozostają polskie.</p>';
        foreach (['heading'=>'Nagłówek (opcjonalny)','highlight'=>'Tekst w ramce (opcjonalny, np. kod kuponu)','text'=>'Treść (opcjonalna; dozwolone linki, pogrubienie i kursywa)','url'=>'Link docelowy','button'=>'Podpis przycisku (opcjonalny)','alt'=>'Opis grafiki dla czytników ekranu (opcjonalny)','coupon'=>'Powiązany kupon WooCommerce (opcjonalnie)'] as $key=>$label) {
            echo '<p><label><strong>'.esc_html($label).'</strong><br>';
            if ($key==='text') echo '<textarea class="widefat" rows="3" name="wtfb[text]">'.esc_textarea($get($key)).'</textarea>';
            else echo '<input class="widefat" type="'.($key==='url'?'url':'text').'" name="wtfb['.esc_attr($key).']" value="'.esc_attr($get($key)).'">';
            echo '</label></p>';
        }
        foreach (['horizontal'=>['Pozioma','Zalecane 1600 × 500 px (16:5). Obraz wyświetlamy w całości, bez kadrowania. Przy tekście grafika zajmuje lewą część paska.'], 'vertical'=>['Pionowa','Zalecane 600 × 800 px (3:4). Obraz wyświetlamy w całości, bez kadrowania. W kolumnie sklepu tekst musi być czytelny przy szerokości 180 px.']] as $key=>$info) {
            $image=absint($get($key));
            echo '<div class="wtfb-image-field"><h3>Grafika: '.esc_html($info[0]).'</h3><p>'.esc_html($info[1]).'</p><input type="hidden" name="wtfb['.esc_attr($key).']" value="'.$image.'"><div class="wtfb-image-preview">'.($image?wp_get_attachment_image($image,'medium',['style'=>'max-width:240px;height:auto']):'').'</div><button type="button" class="button wtfb-select">Wybierz grafikę</button> <button type="button" class="button wtfb-remove">Usuń grafikę</button></div>';
        }
        echo '<h3>Miejsca wyświetlania</h3><p>Baner pojawi się tylko tam, gdzie osadzono odpowiedni shortcode. Dla wybranej formy musi mieć grafikę lub treść.</p>';
        $selected=(array)$get('places');
        foreach(wtfb_places() as $key=>$label) echo '<p><label><input type="checkbox" name="wtfb[places][]" value="'.esc_attr($key).'" '.checked(in_array($key,$selected,true),true,false).'> '.esc_html($label).'</label></p>';
        if(function_exists('wc_get_products')){
            echo '<p><label for="wtfb-products"><strong>Produkty przy których wyświetlać baner</strong></label></p><select id="wtfb-products" class="wc-product-search" multiple="multiple" style="width:100%" name="wtfb[products][]" data-placeholder="Wyszukaj produkt…" data-action="woocommerce_json_search_products" data-minimum_input_length="2">';
            foreach((array)$get('products')as$product_id){$product=wc_get_product(absint($product_id));if($product)echo '<option value="'.$product->get_id().'" selected="selected">'.esc_html($product->get_formatted_name()).'</option>';}
            echo '</select><p>Wybierz miejsce „Przy cenie wybranych produktów” i wyszukaj produkty. Powiązany kupon automatycznie ukrywa baner po wygaśnięciu lub wyczerpaniu całkowitego limitu użyć. Nie sprawdza warunków indywidualnego koszyka.</p>';
        }
        echo '<h3>Termin (opcjonalny)</h3><p>Strefa czasowa: '.esc_html(wp_timezone_string()).'. Puste pola oznaczają brak ograniczenia.</p>';
        foreach(['start'=>'Od','end'=>'Do'] as $key=>$label) echo '<p><label>'.esc_html($label).' <input type="datetime-local" name="wtfb['.$key.']" value="'.esc_attr($get($key)).'"></label></p>';
        echo '<p>Osadzanie: <code>[wtf_banner miejsce="glowna" forma="pozioma"]</code>, <code>[wtf_banner miejsce="sklep" forma="pionowa"]</code>, <code>[wtf_banner miejsce="gra" gra="kurzolaki" forma="pozioma"]</code>. Wszystkie pasujące aktywne banery pokażą się kolejno.</p>';
    },'wtfb_banner');
});

add_action('admin_enqueue_scripts',function () {
    $screen=get_current_screen();
    if (!$screen || $screen->post_type!=='wtfb_banner' || $screen->base!=='post') return;
    wp_enqueue_media();
    if(function_exists('WC')){
        wp_enqueue_script('wc-enhanced-select');
        wp_enqueue_style('wtfb-select2',WC()->plugin_url().'/assets/css/select2.css',[],WC_VERSION);
    }
    wp_enqueue_style('wtfb-admin',plugins_url('assets/admin.css',__FILE__),[],filemtime(__DIR__.'/assets/admin.css'));
    wp_enqueue_script('wtfb-admin',plugins_url('assets/admin.js',__FILE__),['jquery'],filemtime(__DIR__.'/assets/admin.js'),true);
},20);

add_action('save_post_wtfb_banner',function ($id) {
    if (wp_is_post_revision($id) || (defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) || !current_user_can('manage_options') || !isset($_POST['wtfb_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtfb_nonce'])),'wtfb_save')) return;
    $raw=isset($_POST['wtfb'])&&is_array($_POST['wtfb'])?wp_unslash($_POST['wtfb']):[];
    foreach(['heading','highlight','text','url','button','alt','coupon','products','start','end','horizontal','vertical','enabled','places','language'] as $key) {
        $value=$raw[$key]??'';
        if ($key==='language') $value=in_array($value,['pl','en','all'],true)?$value:'pl';
        elseif ($key==='places') $value=array_values(array_intersect(array_keys(wtfb_places()),is_array($value)?array_filter($value,'is_string'):[]));
        elseif ($key==='products') $value=is_array($value)?array_values(array_filter(array_map('absint',array_filter($value,'is_scalar')))):[];
        elseif (in_array($key,['horizontal','vertical'],true)) {$value=is_scalar($value)?absint($value):0;if($value&&!wp_attachment_is_image($value))$value=0;}
        elseif ($key==='enabled') $value=$value==='1'?'1':'0';
        elseif (!is_string($value)) $value='';
        elseif ($key==='url') $value=esc_url_raw($value,['http','https']);
        elseif (in_array($key,['start','end'],true)) $value=wtfb_date($value)?$value:'';
        else $value=$key==='text'?wtfb_text($value):sanitize_text_field($value);
        update_post_meta($id,'_wtfb_'.$key,$value);
    }
});

add_action('wp_enqueue_scripts',function () {
    // Small shared stylesheet also covers shortcodes rendered inside dynamic shop blocks.
    wp_enqueue_style('wtfb-banner',plugins_url('assets/banner.css',__FILE__),[],filemtime(__DIR__.'/assets/banner.css'));
    wp_enqueue_script('wtfb-banner',plugins_url('assets/banner.js',__FILE__),[],filemtime(__DIR__.'/assets/banner.js'),true);
});

function wtfb_render($attributes) {
    $a=shortcode_atts(['miejsce'=>'','gra'=>'','produkt'=>0,'forma'=>'pozioma'],$attributes,'wtf_banner');
    if (!in_array($a['forma'],['pozioma','pionowa'],true)) return '';
    $place=sanitize_key($a['miejsce']);
    if ($place==='gra') {if(!$a['gra'])return ''; $place.=':'.sanitize_title($a['gra']);}
    if (!array_key_exists($place,wtfb_places())) return '';
    $banners=get_posts(['post_type'=>'wtfb_banner','post_status'=>'publish','numberposts'=>-1,'orderby'=>['menu_order'=>'ASC','ID'=>'ASC'],'meta_query'=>[['key'=>'_wtfb_enabled','value'=>'1']]]);
    $html='';$now=time();
    foreach($banners as $banner) {
        $get=fn($key)=>get_post_meta($banner->ID,'_wtfb_'.$key,true);
        $language=$get('language')?:'pl';$current=function_exists('pll_current_language')?(pll_current_language('slug')?:'pl'):'pl';
        if($language!=='all'&&$language!==$current)continue;
        if (!in_array($place,(array)$get('places'),true)) continue;
        if($place==='produkt'&&(!absint($a['produkt'])||!in_array(absint($a['produkt']),(array)$get('products'),true)))continue;
        $coupon_code=$get('coupon');if($coupon_code){
            if(!function_exists('wc_get_coupon_id_by_code'))continue;
            $coupon_id=wc_get_coupon_id_by_code($coupon_code);if(!$coupon_id||get_post_status($coupon_id)!=='publish')continue;
            $coupon=new WC_Coupon($coupon_id);$expiry=$coupon->get_date_expires();
            if(($expiry&&$now>$expiry->getTimestamp())||($coupon->get_usage_limit()&&$coupon->get_usage_count()>=$coupon->get_usage_limit()))continue;
        }
        $start=wtfb_date($get('start'));$end=wtfb_date($get('end'));
        if (($start&&$now<$start)||($end&&$now>=$end)) continue;
        $image=absint($get($a['forma']==='pozioma'?'horizontal':'vertical'));
        $heading=$get('heading');$highlight=$get('highlight');$text=$get('text');$url=$get('url');$button=$get('button');
        $art=$image?wp_get_attachment_image($image,'large',false,['class'=>'wtfb-banner__image','loading'=>'lazy','sizes'=>$a['forma']==='pionowa'?'(max-width: 650px) 100vw, 180px':'(max-width: 650px) 100vw, 600px','alt'=>$get('alt')?:get_post_meta($image,'_wp_attachment_image_alt',true)]):'';
        if (!$art&&!$heading&&!$highlight&&!$text) continue;
        $html.='<section class="wtfb-banner wtfb-banner--'.esc_attr($a['forma']).($heading||$highlight||$text||($button&&$url)?' has-copy':'').'" data-banner-id="'.(int)$banner->ID.'">';
        if($art) {$label=$get('alt')?:($heading?:$banner->post_title);$html.=$url?'<a class="wtfb-banner__art" href="'.esc_url($url).'" aria-label="'.esc_attr($label).'">'.$art.'</a>':'<div class="wtfb-banner__art">'.$art.'</div>';}
        if($heading||$highlight||$text||($button&&$url)) {
            $html.='<div class="wtfb-banner__copy">';if($heading)$html.='<h2>'.esc_html($heading).'</h2>';
            if($highlight){
                $html.='<div class="wtfb-banner__highlight"><span>'.esc_html($highlight).'</span>';
                if($coupon_code){$en=function_exists('wtf_core_language')&&wtf_core_language()==='en';$label=$en?'Copy coupon code':'Kopiuj kod kuponu';$copied=$en?'Copied':'Skopiowano';$failed=$en?'Copy failed. Select the code and copy it manually.':'Nie udało się skopiować. Zaznacz kod i skopiuj go ręcznie.';$html.='<button type="button" class="wtfb-banner__copy-code" data-copy-code="'.esc_attr($highlight).'" data-copy-label="'.esc_attr($label).'" data-copied-label="'.esc_attr($copied).'" data-copy-error="'.esc_attr($failed).'" aria-label="'.esc_attr($label).'" title="'.esc_attr($label).'" hidden><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8" y="8" width="12" height="13" rx="2"/><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/></svg></button><span class="wtfb-banner__copy-status" role="status" aria-live="polite"></span>';}
                $html.='</div>';
            }
            if($text)$html.='<div class="wtfb-banner__text">'.wpautop(wtfb_text($text)).'</div>';if($button&&$url)$html.='<a class="wtfb-banner__button" href="'.esc_url($url).'">'.esc_html($button).'</a>';$html.='</div>';
        }
        $html.='</section>';
    }
    return $html?'<div class="wtfb-banners wtfb-banners--'.esc_attr($a['forma']).'">'.$html.'</div>':'';
}
add_shortcode('wtf_banner','wtfb_render');

add_filter('manage_wtfb_banner_posts_columns',function($columns){$columns['wtfb_emission']='Emisja';$columns['wtfb_places']='Miejsca';$columns['wtfb_order']='Kolejność';return $columns;});
add_action('manage_wtfb_banner_posts_custom_column',function($column,$id){
    if($column==='wtfb_order')echo (int)get_post_field('menu_order',$id);
    if($column==='wtfb_places'){$labels=wtfb_places();echo esc_html(implode(', ',array_map(fn($key)=>$labels[$key]??$key,(array)get_post_meta($id,'_wtfb_places',true))));}
    if($column==='wtfb_emission'){
        $start=wtfb_date(get_post_meta($id,'_wtfb_start',true));$end=wtfb_date(get_post_meta($id,'_wtfb_end',true));
        echo esc_html(get_post_meta($id,'_wtfb_enabled',true)!=='1'?'Wyłączony':(($end&&time()>=$end)?'Zakończony':(($start&&time()<$start)?'Zaplanowany':'Włączony')));
    }
},10,2);
