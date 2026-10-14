<?php
if(!defined('ABSPATH')) exit;

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
    add_action('wp_footer','woocommerce_photoswipe');
},30);

add_action('woocommerce_product_options_general_product_data',function(){
    global $product_object; if(!$product_object) return;
    echo '<div class="options_group">';
    wp_nonce_field('wtf_product_presentation','wtf_product_presentation_nonce');
    woocommerce_wp_checkbox(['id'=>'_wtf_product_editorial','label'=>'Nowy szablon produktu','value'=>$product_object->get_meta('_wtf_product_editorial'),'description'=>'Włącz przygotowywany układ strony produktu.']);
    woocommerce_wp_textarea_input(['id'=>'_wtf_product_lead','label'=>'Krótka zajawka przy cenie','value'=>$product_object->get_meta('_wtf_product_lead'),'desc_tip'=>true,'description'=>'Jedno lub dwa zdania przy panelu zakupu. Pełne opisy pozostają w standardowych polach produktu.']);
    echo '</div>';
});
add_action('woocommerce_process_product_meta',function($id){
    if(!current_user_can('edit_post',$id)||empty($_POST['wtf_product_presentation_nonce'])||!is_string($_POST['wtf_product_presentation_nonce'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtf_product_presentation_nonce'])),'wtf_product_presentation'))return;
    update_post_meta($id,'_wtf_product_editorial',isset($_POST['_wtf_product_editorial'])?'yes':'no');
    if(isset($_POST['_wtf_product_lead'])&&is_string($_POST['_wtf_product_lead']))update_post_meta($id,'_wtf_product_lead',sanitize_textarea_field(wp_unslash($_POST['_wtf_product_lead'])));
});

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

function wtf_shop_product_render() {
    if(!function_exists('wc_get_product')) return '';
    global $product;
    $product=wc_get_product(get_queried_object_id());
    if(!$product)return '';
    if($product->get_meta('_wtf_product_editorial')!=='yes') {
        ob_start(); echo '<div class="woocommerce">';
        wc_get_template_part('content','single-product');
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
    $shortHtml=apply_filters('woocommerce_short_description',$short);
    $descriptionHtml=wtf_shop_unique_description(apply_filters('the_content',$product->get_description()),$shortHtml);
    $related=[];
    if($game)foreach(wc_get_products(['status'=>'publish','visibility'=>'catalog','stock_status'=>['instock','onbackorder'],'limit'=>-1,'orderby'=>'menu_order','order'=>'ASC']) as $item) {
        if($item->get_id()!==$product->get_id()&&wtf_shop_product_term($item,'wtf_product_game')===$game&&wtf_shop_product_term($item,'wtf_product_kind')==='dodatek')$related[]=$item;
        if(count($related)===3)break;
    }
    ob_start(); ?>
    <div class="woocommerce wtf-product <?php echo $game==='elekt'?'wtf-product--elekt':''; ?>">
      <?php echo $GLOBALS['wtf_product_notices_html']??''; do_action('woocommerce_before_single_product'); ?>
      <nav class="wtf-product-breadcrumb" aria-label="Ścieżka produktu"><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Sklep</a><span>/</span><?php if(isset($games[$game])): ?><a href="<?php echo esc_url($shopUrl); ?>"><?php echo esc_html($games[$game]); ?></a><span>/</span><?php endif; ?><span aria-current="page"><?php echo esc_html($kindLabel?:$product->get_name()); ?></span></nav>
      <div id="product-<?php echo absint($product->get_id()); ?>" <?php wc_product_class('wtf-product-frame',$product); ?>>
        <section class="wtf-product-top" aria-label="Produkt i zakup">
          <div class="wtf-product-visual"><div class="wtf-product-gallery-label"><span>Zdjęcia produktu</span><span><?php echo 1+count($product->get_gallery_image_ids()); ?></span></div><?php woocommerce_show_product_images(); ?></div>
          <div class="summary entry-summary wtf-product-summary">
            <div class="wtf-product-kicker"><?php echo esc_html(implode(' / ',array_filter([$games[$game]??'WTF',$kindLabel]))); ?></div>
            <?php woocommerce_template_single_title(); ?>
            <?php if($product->get_meta('_wtf_product_lead')): ?><p class="wtf-product-lead"><?php echo esc_html($product->get_meta('_wtf_product_lead')); ?></p><?php endif; ?>
            <div class="wtf-product-purchase">
              <span class="wtf-product-price-label">Cena</span><?php woocommerce_template_single_price(); ?>
              <?php if($priceNote): ?><p class="wtf-product-price-note"><?php echo esc_html($priceNote); ?></p><?php endif; ?>
              <?php woocommerce_template_single_add_to_cart(); ?>
            </div>
            <div class="wtf-product-section-links"><?php if($short): ?><a href="#product-edition">Informacje o produkcie</a><?php endif; ?><?php if($product->get_description()): ?><a href="#product-description">Opis i zawartość</a><?php endif; ?><?php if($related): ?><a href="#product-expansions">Dobierz dodatki</a><?php endif; ?></div>
          </div>
        </section>
        <?php if($short): ?>
        <section class="wtf-product-content-section wtf-product-edition" id="product-edition" aria-labelledby="product-edition-title"><header><span class="wtf-product-section-number">01</span><h2 id="product-edition-title">Informacje<br> o produkcie</h2></header><div class="wtf-product-copy"><?php echo $shortHtml; ?></div></section>
        <?php endif; ?>
        <?php if($product->get_description()): ?>
        <section class="wtf-product-content-section" id="product-description" aria-labelledby="product-description-title"><header><span class="wtf-product-section-number">02</span><h2 id="product-description-title">Opis<br> i zawartość</h2><?php if($game==='elekt'): ?><a class="wtf-product-game-link" href="<?php echo esc_url(wtf_ele_local_game_link('https://whatthefrog.pl/elekt/')); ?>">Pełna prezentacja Elekta</a><?php endif; ?></header><div class="wtf-product-copy wtf-product-description"><?php echo $descriptionHtml; ?></div></section>
        <?php endif; ?>
        <?php if($product->has_attributes()): ?><section class="wtf-product-attributes"><h2>Dane produktu</h2><?php wc_display_product_attributes($product); ?></section><?php endif; ?>
        <?php if($related): ?>
        <section class="wtf-product-expansions" id="product-expansions" aria-labelledby="product-expansions-title"><header><div><span class="wtf-product-section-number">ROZBUDUJ SWOJĄ GRĘ</span><h2 id="product-expansions-title">Kolejny ruch należy do Ciebie.</h2></div><a href="<?php echo esc_url($shopUrl.'#elekt-dodatek'); ?>">Zobacz wszystkie dodatki</a></header><div class="shop-products"><?php foreach($related as $item)wtf_shop_card($item); ?></div></section>
        <?php endif; ?>
        <?php if(comments_open($product->get_id())): ?><section class="wtf-product-reviews"><?php comments_template(); ?></section><?php endif; ?>
      </div>
      <?php WC()->structured_data->generate_product_data($product); ?>
      <?php do_action('woocommerce_after_single_product'); ?>
    </div>
    <?php return ob_get_clean();
}
