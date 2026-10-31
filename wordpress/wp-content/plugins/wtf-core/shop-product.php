<?php
if(!defined('ABSPATH')) exit;
require_once __DIR__.'/shop-product-urls.php';

add_filter('woocommerce_sale_flash',function($html,$post,$product){
    if(!$product instanceof WC_Product)return $html;
    $regular=(float)$product->get_regular_price();
    $sale=$product->get_sale_price();
    if($sale===''||$regular<=0||(float)$sale>=$regular)return $html;
    $discount=(int)round(100*($regular-(float)$sale)/$regular);
    if($discount<1)return $html;
    return '<span class="onsale wtf-sale-badge" aria-label="'.esc_attr('Rabat '.$discount.'%').'">−'.esc_html($discount).'%</span>';
},10,3);

// Własny szablon wywołuje funkcje PHP WooCommerce; nie używa nowych bloków produktu.
// Warstwa zgodności tych bloków usuwałaby potrzebne standardowemu widokowi hooki.
add_filter('woocommerce_disable_compatibility_layer',function($disabled){
    return function_exists('is_product')&&is_product()&&get_template()==='wtf' ? true : $disabled;
});

add_action('init',function(){
    register_block_type('wtf/shop-product',['api_version'=>3,'render_callback'=>'wtf_shop_product_render']);
});
// Komunikaty zachowujemy przed wstępnym renderowaniem szablonów blokowych.
add_action('template_redirect',function(){
    if(!function_exists('is_product')||!is_product()||get_post_meta(get_queried_object_id(),'_wtf_product_editorial',true)!=='yes')return;
    $GLOBALS['wtf_product_notices_html']=wc_print_notices(true);
},20);
// Motyw blokowy nie ładuje sam skryptów klasycznej galerii WooCommerce.
add_action('wp_enqueue_scripts',function(){
    if(!function_exists('is_product')||!is_product())return;
    wp_enqueue_script('wc-flexslider');
    wp_enqueue_script('wc-photoswipe-ui-default');
    wp_enqueue_style('photoswipe-default-skin');
    wp_enqueue_script('wc-single-product');
    wp_enqueue_script('wtf-product-gallery',get_theme_file_uri('assets/product-gallery.js'),['jquery','wc-single-product'],filemtime(get_theme_file_path('assets/product-gallery.js')),true);
    wp_enqueue_script('wtf-bundle-descriptions',get_theme_file_uri('assets/bundle-descriptions.js'),[],filemtime(get_theme_file_path('assets/bundle-descriptions.js')),true);
    add_action('wp_footer','woocommerce_photoswipe');
},30);
add_filter('woocommerce_single_product_carousel_options',function($options){
    if(function_exists('is_product') && is_product() && get_template()==='wtf') {
        $options['controlNav']=false;
        $options['slideshow']=false;
        $options['animationSpeed']=450;
    }
    return $options;
});

add_action('woocommerce_product_options_general_product_data',function(){
    global $product_object; if(!$product_object) return;
    echo '<div class="options_group">';
    wp_nonce_field('wtf_product_presentation','wtf_product_presentation_nonce');
    woocommerce_wp_text_input(['id'=>'_wtf_product_description_heading','label'=>'Nagłówek opisu','value'=>$product_object->get_meta('_wtf_product_description_heading'),'placeholder'=>'Opis i zawartość','desc_tip'=>true,'description'=>'Własny nagłówek sekcji opisu. Puste pole oznacza: Opis i zawartość.']);
    woocommerce_wp_checkbox(['id'=>'_wtf_product_editorial','label'=>'Nowy szablon produktu','value'=>$product_object->get_meta('_wtf_product_editorial'),'description'=>'Włącz przygotowywany układ strony produktu.']);
    woocommerce_wp_textarea_input(['id'=>'_wtf_product_lead','label'=>'Krótka zajawka przy cenie','value'=>$product_object->get_meta('_wtf_product_lead'),'desc_tip'=>true,'description'=>'Jedno lub dwa zdania przy panelu zakupu. Pełne opisy pozostają w standardowych polach produktu.']);
    woocommerce_wp_textarea_input(['id'=>'_wtf_product_purchase_notice','label'=>'Informacja pod ceną','value'=>$product_object->get_meta('_wtf_product_purchase_notice'),'desc_tip'=>true,'description'=>'Komunikat przy cenie i przycisku zakupu. Dopuszczalne proste formatowanie HTML. Puste pole ukrywa komunikat.']);
    echo '</div>';
});
add_action('woocommerce_process_product_meta',function($id){
    if(!current_user_can('edit_post',$id)||empty($_POST['wtf_product_presentation_nonce'])||!is_string($_POST['wtf_product_presentation_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtf_product_presentation_nonce'])),'wtf_product_presentation'))return;
    update_post_meta($id,'_wtf_product_editorial',isset($_POST['_wtf_product_editorial'])?'yes':'no');
    if(isset($_POST['_wtf_product_description_heading'])&&is_string($_POST['_wtf_product_description_heading']))update_post_meta($id,'_wtf_product_description_heading',sanitize_text_field(wp_unslash($_POST['_wtf_product_description_heading'])));
    if(isset($_POST['_wtf_product_lead'])&&is_string($_POST['_wtf_product_lead']))update_post_meta($id,'_wtf_product_lead',sanitize_textarea_field(wp_unslash($_POST['_wtf_product_lead'])));
    if(isset($_POST['_wtf_product_purchase_notice'])&&is_string($_POST['_wtf_product_purchase_notice']))update_post_meta($id,'_wtf_product_purchase_notice',wp_kses_post(wp_unslash($_POST['_wtf_product_purchase_notice'])));
});

function wtf_shop_product_purchase_notice() {
    global $product;
    if(!$product instanceof WC_Product)return;
    $notice=$product->get_meta('_wtf_product_purchase_notice');
    if($notice)echo '<div class="wtf-product-purchase-notice">'.wp_kses_post($notice).'</div>';
}
add_action('woocommerce_single_product_summary','wtf_shop_product_purchase_notice',11);

function wtf_shop_unique_description($description,$short) {
    // Identyczny akapit krótkiego opisu pokazujemy raz; treści w bazie pozostają edytowalne.
    $fingerprint=fn($html)=>trim(preg_replace('/\s+/u',' ',html_entity_decode(wp_strip_all_tags($html),ENT_QUOTES,'UTF-8')));
    preg_match_all('~<p\b[^>]*>.*?</p>~is',$short,$paragraphs);
    $known=[];foreach($paragraphs[0] as $paragraph){$key=$fingerprint($paragraph);if($key!=='')$known[$key]=true;}
    return preg_replace_callback('~<p\b[^>]*>.*?</p>~is',function($match)use($known,$fingerprint){
        if(preg_match('~<(?:img|iframe|audio|video)\b~i',$match[0]))return $match[0];
        return isset($known[$fingerprint($match[0])])?'':$match[0];
    },$description);
}

function wtf_shop_product_trim_empty_paragraphs($html) {
    return preg_replace_callback('~<p\b[^>]*>.*?</p>~is', function ($match) {
        if (preg_match('~<(?:img|picture|iframe|audio|video|svg|input|button|hr)\b~i',$match[0])) return $match[0];
        $text=html_entity_decode(wp_strip_all_tags($match[0]),ENT_QUOTES,'UTF-8');
        return preg_match('/^[\s\x{00A0}]*$/u',$text) ? '' : $match[0];
    },$html);
}

function wtf_shop_product_description_heading() {
    $current=wc_get_product(get_queried_object_id());
    if($current&&wtf_shop_product_term($current,'wtf_product_kind')==='zestaw')return 'Zawartość zestawu';
    $title=trim((string)get_post_meta(get_queried_object_id(),'_wtf_product_description_heading',true));
    return $title!==''?$title:'Opis i zawartość';
}

function wtf_shop_product_content_sections() {
    $tabs=apply_filters('woocommerce_product_tabs',[]);
    global $product;
    if($product instanceof WC_Product&&$product->get_meta('_wtf_specs_in_intro')==='yes'&&!$product->has_attributes())unset($tabs['additional_information']);
    $sections_count=count(array_filter($tabs,fn($tab)=>!empty($tab['callback'])&&is_callable($tab['callback'])));
    $index=0;
    foreach ($tabs as $key=>$tab) {
        if (empty($tab['callback']) || !is_callable($tab['callback'])) continue;
        $index++;
        $id='product-details-'.sanitize_html_class($key);
        $title=$key==='description'?wtf_shop_product_description_heading():wp_strip_all_tags($tab['title']??'');
        $split=$key==='description'&&$product instanceof WC_Product&&$product->get_meta('_wtf_contents_image_layout')==='yes';
        echo '<section class="wtf-product-content-section wtf-standard-content-section'.($split?' wtf-product-contents-image':'').'" id="'.esc_attr($id).'" aria-labelledby="'.esc_attr($id.'-title').'"><header>'.($sections_count>1?'<span class="wtf-product-section-number">'.esc_html(sprintf('%02d',$index)).'</span>':'').'<h2 id="'.esc_attr($id.'-title').'">'.esc_html($title).'</h2></header><div class="wtf-product-copy">';
        // Domyślne szablony WooCommerce nie powtarzają nagłówka naszej sekcji.
        add_filter('woocommerce_product_description_heading','__return_false');
        add_filter('woocommerce_product_additional_information_heading','__return_false');
        ob_start();
        try { call_user_func($tab['callback'],$key,$tab); }
        finally {
            $body=ob_get_clean();
            remove_filter('woocommerce_product_description_heading','__return_false');
            remove_filter('woocommerce_product_additional_information_heading','__return_false');
        }
        $wide='';
        if($key==='description'&&(!$product instanceof WC_Product||wtf_shop_product_term($product,'wtf_product_kind')!=='zestaw'))$body=preg_replace_callback('~<figure\b[^>]*class="[^"]*\bwtf-product-wide-image\b[^"]*"[^>]*>.*?</figure>~is',function($m)use(&$wide){$wide.=$m[0];return '';},$body);
        if($split&&$wide!=='')$wide='<div class="wtf-product-illustrations">'.$wide.'</div>';
        echo $body.'</div>'.$wide.'</section>';
    }
}

function wtf_shop_product_render() {
    if(!function_exists('wc_get_product')) return '';
    global $product;
    $product=wc_get_product(get_queried_object_id());
    if(!$product)return '';
    if($product->get_meta('_wtf_product_editorial')!=='yes') {
        ob_start(); echo '<div class="woocommerce wtf-product-standard">';
        $game=wtf_shop_product_term($product,'wtf_product_game');
        $games=wtf_shop_games();
        echo '<nav class="woocommerce-breadcrumb" aria-label="Ścieżka produktu"><a href="'.esc_url(wc_get_page_permalink('shop')).'">Sklep</a><span aria-hidden="true"> / </span>';
        if (isset($games[$game])) echo '<a href="'.esc_url(add_query_arg('game',$game,wc_get_page_permalink('shop'))).'">'.esc_html($games[$game]).'</a><span aria-hidden="true"> / </span>';
        echo '<span aria-current="page">'.esc_html($product->get_name()).'</span></nav>';
        $cart_priority=has_action('woocommerce_single_product_summary','woocommerce_template_single_add_to_cart');
        $meta_priority=has_action('woocommerce_single_product_summary','woocommerce_template_single_meta');
        if ($meta_priority!==false) remove_action('woocommerce_single_product_summary','woocommerce_template_single_meta',$meta_priority);
        // Na razie pokazujemy tylko automatyczne podobne produkty; przypisania up-sell zachowujemy.
        $upsell_priority=has_action('woocommerce_after_single_product_summary','woocommerce_upsell_display');
        if ($upsell_priority!==false) remove_action('woocommerce_after_single_product_summary','woocommerce_upsell_display',$upsell_priority);
        $tabs_priority=has_action('woocommerce_after_single_product_summary','woocommerce_output_product_data_tabs');
        if ($tabs_priority!==false) {
            remove_action('woocommerce_after_single_product_summary','woocommerce_output_product_data_tabs',$tabs_priority);
            add_action('woocommerce_after_single_product_summary','wtf_shop_product_content_sections',$tabs_priority);
        }
        if ($cart_priority!==false) {
            remove_action('woocommerce_single_product_summary','woocommerce_template_single_add_to_cart',$cart_priority);
            add_action('woocommerce_single_product_summary','woocommerce_template_single_add_to_cart',15);
        }
        add_filter('woocommerce_short_description','wtf_shop_product_trim_empty_paragraphs',30);
        try { wc_get_template_part('content','single-product'); }
        finally {
            remove_filter('woocommerce_short_description','wtf_shop_product_trim_empty_paragraphs',30);
            if ($meta_priority!==false) add_action('woocommerce_single_product_summary','woocommerce_template_single_meta',$meta_priority);
            if ($upsell_priority!==false) add_action('woocommerce_after_single_product_summary','woocommerce_upsell_display',$upsell_priority);
            if ($tabs_priority!==false) {
                remove_action('woocommerce_after_single_product_summary','wtf_shop_product_content_sections',$tabs_priority);
                add_action('woocommerce_after_single_product_summary','woocommerce_output_product_data_tabs',$tabs_priority);
            }
            if ($cart_priority!==false) {
                remove_action('woocommerce_single_product_summary','woocommerce_template_single_add_to_cart',15);
                add_action('woocommerce_single_product_summary','woocommerce_template_single_add_to_cart',$cart_priority);
            }
        }
        echo '</div>'; return ob_get_clean();
    }
    if(post_password_required())return get_the_password_form();
    $game=wtf_shop_product_term($product,'wtf_product_game'); $games=wtf_shop_games();
    $kind=wtf_shop_product_term($product,'wtf_product_kind');
    $kindLabel=['podstawka'=>'Podstawka','dodatek'=>'Dodatek','akcesorium'=>'Akcesorium'][$kind]??'';
    $shopUrl=add_query_arg('game',$game,wc_get_page_permalink('shop'));
    $short=$product->get_short_description(); $priceNote='';
    $parts=preg_split('/\R\s*\R/',trim($short),2);
    if(isset($parts[1])&&preg_match('/^najniższa cena/iu',trim(wp_strip_all_tags($parts[0])))) {$priceNote=wp_strip_all_tags($parts[0]);$short=$parts[1];}
    if(function_exists('wtfo_note') && wtfo_note($product)) $priceNote='';
    $shortHtml=apply_filters('woocommerce_short_description',$short);
    $bundle_description=wtf_shop_product_term($product,'wtf_product_kind')==='zestaw'?wtf_bundle_full_description($product):'';
    $descriptionHtml=$bundle_description?:wtf_shop_unique_description(apply_filters('the_content',$product->get_description()),$shortHtml);
    $related=[];
    if($game)foreach(wc_get_products(['status'=>'publish','visibility'=>'catalog','stock_status'=>['instock','onbackorder'],'limit'=>-1,'orderby'=>'menu_order','order'=>'ASC']) as $item) {
        if($item->get_id()!==$product->get_id()&&wtf_shop_product_term($item,'wtf_product_game')===$game&&wtf_shop_product_term($item,'wtf_product_kind')==='dodatek')$related[]=$item;
        if(count($related)===3)break;
    }
    ob_start(); ?>
    <div class="woocommerce wtf-product <?php echo $game?'wtf-product--'.esc_attr(sanitize_html_class($game)):''; ?>">
      <?php echo $GLOBALS['wtf_product_notices_html']??''; do_action('woocommerce_before_single_product'); ?>
      <nav class="wtf-product-breadcrumb" aria-label="Ścieżka produktu"><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Sklep</a><span>/</span><?php if(isset($games[$game])): ?><a href="<?php echo esc_url($shopUrl); ?>"><?php echo esc_html($games[$game]); ?></a><span>/</span><?php endif; ?><span aria-current="page"><?php echo esc_html($kindLabel?:$product->get_name()); ?></span></nav>
      <div id="product-<?php echo absint($product->get_id()); ?>" <?php wc_product_class('wtf-product-frame',$product); ?>>
        <section class="wtf-product-top" aria-label="Produkt i zakup">
          <div class="wtf-product-visual"><?php woocommerce_show_product_images(); ?></div>
          <div class="summary entry-summary wtf-product-summary">
            <div class="wtf-product-kicker"><?php echo esc_html(implode(' / ',array_filter([$games[$game]??'WTF',$kindLabel]))); ?></div>
            <?php woocommerce_template_single_title(); ?>
            <?php if($product->get_meta('_wtf_product_lead')): ?><p class="wtf-product-lead"><?php echo esc_html($product->get_meta('_wtf_product_lead')); ?></p><?php endif; ?>
            <div class="wtf-product-purchase">
              <span class="wtf-product-price-label">Cena</span><?php woocommerce_template_single_price(); ?>
              <?php wtf_shop_product_purchase_notice(); ?>
              <?php echo wtf_bundle_description($product); ?>
              <?php if($priceNote): ?><p class="wtf-product-price-note"><?php echo esc_html($priceNote); ?></p><?php endif; ?>
              <?php woocommerce_template_single_add_to_cart(); ?>
            </div>
            <div class="wtf-product-section-links"><?php if($short): ?><a href="#product-edition">Informacje o produkcie</a><?php endif; ?><?php if($product->get_description()): ?><a href="#product-description"><?php echo esc_html(wtf_shop_product_description_heading()); ?></a><?php endif; ?><?php if($related): ?><a href="#product-expansions">Dobierz dodatki</a><?php endif; ?></div>
          </div>
        </section>
        <?php if($short): ?>
        <section class="wtf-product-content-section wtf-product-edition" id="product-edition" aria-labelledby="product-edition-title"><header><span class="wtf-product-section-number">01</span><h2 id="product-edition-title">Informacje<br> o produkcie</h2></header><div class="wtf-product-copy"><?php echo $shortHtml; ?></div></section>
        <?php endif; ?>
        <?php if($product->get_description()||$bundle_description): ?>
        <section class="wtf-product-content-section" id="product-description" aria-labelledby="product-description-title"><header><span class="wtf-product-section-number">02</span><h2 id="product-description-title"><?php echo esc_html(wtf_shop_product_description_heading()); ?></h2><?php if($game==='elekt'): ?><a class="wtf-product-game-link" href="<?php echo esc_url(wtf_ele_local_game_link('https://whatthefrog.pl/elekt/')); ?>">Pełna prezentacja Elekta</a><?php endif; ?></header><div class="wtf-product-copy wtf-product-description"><?php echo $descriptionHtml; ?></div></section>
        <?php endif; ?>
        <?php if($product->has_attributes()): ?><section class="wtf-product-attributes"><h2>Dane produktu</h2><?php wc_display_product_attributes($product); ?></section><?php endif; ?>
        <?php if($related): ?>
        <section class="wtf-product-expansions" id="product-expansions" aria-labelledby="product-expansions-title"><header><div><span class="wtf-product-section-number">ROZBUDUJ SWOJĄ GRĘ</span><h2 id="product-expansions-title">Kolejny ruch należy do Ciebie.</h2></div><a href="<?php echo esc_url($shopUrl.'#'.$game.'-dodatek'); ?>">Zobacz wszystkie dodatki</a></header><div class="shop-products"><?php foreach($related as $item)wtf_shop_card($item); ?></div></section>
        <?php endif; ?>
      </div>
      <?php WC()->structured_data->generate_product_data($product); ?>
      <?php do_action('woocommerce_after_single_product'); ?>
    </div>
    <?php return ob_get_clean();
}
