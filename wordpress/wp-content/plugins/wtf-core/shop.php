<?php
if (!defined('ABSPATH')) exit;

function wtf_shop_local_url($url) {
    if(!is_string($url)) return $url;
    $host=wp_parse_url($url,PHP_URL_HOST);
    if(!in_array($host,['whatthefrog.pl','www.whatthefrog.pl','localhost'],true)) return $url;
    $path=wp_parse_url($url,PHP_URL_PATH) ?: '/';
    $map=get_option('wtf_shop_import_map',[]);
    if($path!=='/sklep/' && empty($map['paths'][$path])) return $url;
    $query=wp_parse_url($url,PHP_URL_QUERY); $fragment=wp_parse_url($url,PHP_URL_FRAGMENT);
    return home_url($path).($query?'?'.$query:'').($fragment?'#'.$fragment:'');
}
// Adresy zakupów w istniejących blokach pozostają edytowalne w bazie.
add_filter('render_block',function($html){
    return preg_replace_callback('~href=(["\'])(https?://(?:www\.)?whatthefrog\.pl/[^"\']*)\1~',fn($m)=>'href='.$m[1].esc_url(wtf_shop_local_url(html_entity_decode($m[2],ENT_QUOTES,'UTF-8'))).$m[1],$html);
},20);

function wtf_shop_games() {
    $games=[];
    foreach(get_terms(['taxonomy'=>'wtf_product_game','hide_empty'=>true]) as $term) $games[$term->slug]=$term->name;
    return $games;
}

function wtf_shop_card($product,$layout='card',$introduction='') {
    $game=wtf_shop_product_term($product,'wtf_product_game');
    $games=wtf_shop_games();
    $kind=wtf_shop_product_term($product,'wtf_product_kind');
    $labels=['podstawka'=>'Podstawka','dodatek'=>'Dodatek','akcesorium'=>'Akcesorium'];
    ?>
    <article class="shop-card shop-card--<?php echo esc_attr($layout); ?>">
      <a class="shop-card-image" href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo $product->get_image('medium_large',['loading'=>'lazy','alt'=>$product->get_name(),'sizes'=>$layout==='base'?'(max-width: 700px) 85vw, 450px':'(max-width: 650px) 40vw, 320px']); ?></a>
      <div class="shop-card-copy"><span class="shop-kind"><?php echo esc_html($labels[$kind]??($games[$game]??'WTF')); ?></span>
        <h3><a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a></h3>
        <?php if($introduction): ?><p class="shop-base-intro"><?php echo esc_html($introduction); ?></p><?php endif; ?>
        <div class="shop-card-bottom"><span class="shop-price"><?php echo wp_kses_post($product->get_price_html()); ?></span><a class="shop-add" href="<?php echo esc_url($product->add_to_cart_url()); ?>" rel="nofollow" aria-label="<?php echo esc_attr($product->add_to_cart_description()); ?>"><?php echo esc_html($product->add_to_cart_text()); ?></a></div>
      </div>
    </article>
    <?php
}

function wtf_shop_elekt($products,$a,$overview=false) {
    $groups=['podstawka'=>[],'dodatek'=>[],'akcesorium'=>[],'pozostale'=>[]];
    foreach($products as $product) { $kind=wtf_shop_product_term($product,'wtf_product_kind'); $groups[isset($groups[$kind])?$kind:'pozostale'][]=$product; }
    $labels=['podstawka'=>'Podstawka','dodatek'=>'Dodatki','akcesorium'=>'Akcesoria','pozostale'=>'Pozostałe'];
    ?>
    <section class="shop-elekt <?php echo $overview?'is-overview':''; ?>" aria-label="Sklep gry Elekt">
      <header class="shop-elekt-hero">
        <?php echo wtf_core_image($a,'full',['class'=>'shop-elekt-art','alt'=>'','loading'=>'eager','sizes'=>'(max-width: 650px) 100vw, 1100px']); ?>
        <div class="shop-elekt-hero-copy"><span class="shop-elekt-label">ELEKT / GRA I DODATKI</span><h2><?php echo esc_html($a['elektTitle']); ?></h2><p><?php echo esc_html($a['elektDescription']); ?></p><a href="<?php echo esc_url(wtf_ele_local_game_link('https://whatthefrog.pl/elekt/')); ?>">Poznaj grę Elekt</a></div>
      </header>
      <nav class="shop-elekt-nav" aria-label="Oferta Elekta"><?php foreach($groups as $key=>$items): if(!$items)continue; ?><a href="#elekt-<?php echo esc_attr($key); ?>"><?php echo esc_html($labels[$key]); ?><span><?php echo count($items); ?></span></a><?php endforeach; ?></nav>
      <div class="shop-elekt-offer">
        <?php foreach($groups as $key=>$items): if(!$items)continue; ?>
        <section class="shop-elekt-section" id="elekt-<?php echo esc_attr($key); ?>" aria-labelledby="elekt-title-<?php echo esc_attr($key); ?>">
          <header class="shop-elekt-section-heading"><span><?php echo $key==='podstawka'?'01':($key==='dodatek'?'02':($key==='akcesorium'?'03':'04')); ?></span><h2 id="elekt-title-<?php echo esc_attr($key); ?>"><?php echo esc_html($labels[$key]); ?></h2></header>
          <div class="<?php echo $key==='podstawka'?'shop-base-products':($key==='akcesorium'?'shop-accessories':'shop-products'); ?>">
          <?php foreach($items as $product) wtf_shop_card($product,$key==='podstawka'?'base':($key==='akcesorium'?'accessory':'card'),$key==='podstawka'?$a['elektBaseDescription']:''); ?>
          </div>
        </section>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
}

function wtf_shop_render($a=[]) {
    if(!function_exists('wc_get_products')) return '';
    $a=array_merge(wtf_core_schema()['shop']['defaults'],$a);
    if(!(defined('REST_REQUEST')&&REST_REQUEST)) {
        foreach(parse_blocks(get_post_field('post_content',wc_get_page_id('shop'))) as $block) if($block['blockName']==='wtf/shop') $a=array_merge($a,$block['attrs']);
    }
    $rawGame=$_GET['game']??$_GET['world']??'';
    $selected=is_string($rawGame)?sanitize_title(wp_unslash($rawGame)):'';
    $search=sanitize_text_field(wp_unslash($_GET['search']??''));
    $order=sanitize_key($_GET['sort']??'menu_order');
    $allowed=['menu_order','price','price-desc','title']; if(!in_array($order,$allowed,true)) $order='menu_order';
    if($selected) $order='menu_order';
    $args=['status'=>'publish','visibility'=>'catalog','limit'=>-1,'stock_status'=>['instock','onbackorder'],'orderby'=>'menu_order','order'=>'ASC'];
    if(is_product_category()) $args['category']=[get_queried_object()->slug];
    if(is_product_tag()) $args['tag']=[get_queried_object()->slug];
    $products=wc_get_products($args);
    $games=wtf_shop_games();
    $products=array_values(array_filter($products,fn($p)=>(!$selected||wtf_shop_product_term($p,'wtf_product_game')===$selected)&&(!$search||mb_stripos($p->get_name().' '.wp_strip_all_tags($p->get_short_description()),$search)!==false)));
    if($order!=='menu_order') usort($products,function($a,$b)use($order){
        if($order==='title') return strnatcasecmp($a->get_name(),$b->get_name());
        $result=(float)$a->get_price()<=>(float)$b->get_price(); return $order==='price-desc'?-$result:$result;
    });
    ob_start(); ?>
    <div class="wrap shop-page <?php echo $selected?'has-game':''; ?>">
      <header class="shop-heading"><span class="eyebrow"><?php echo esc_html($a['eyebrow']); ?></span><h1><?php echo esc_html($a['title']); ?></h1><p><?php echo esc_html($a['description']); ?></p></header>
      <?php woocommerce_output_all_notices(); ?>
      <div class="shop-browser">
        <form id="shop-filters" class="shop-filters" action="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" method="get" data-shop-filters>
          <h2>Wybierz grę</h2><div class="shop-filter-buttons">
          <?php foreach([''=>'Wszystkie']+$games as $slug=>$label): ?><label class="shop-world <?php echo $selected===$slug?'active':''; ?>"><input type="radio" name="game" value="<?php echo esc_attr($slug); ?>" <?php checked($selected,$slug); ?>><?php echo esc_html($label); ?></label><?php endforeach; ?>
          </div><label for="shop-search">Szukaj produktu</label><div class="shop-search-field"><input id="shop-search" type="search" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Nazwa produktu"><button type="submit" class="shop-search-submit" aria-label="Szukaj produktu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m15.5 15.5 5 5"/></svg></button></div>
        </form>
        <section class="shop-results" aria-label="Produkty"><div class="shop-results-toolbar">
          <?php if(isset($games[$selected])): ?><h2 class="shop-game-preview-title"><?php echo esc_html($games[$selected]); ?></h2><?php endif; ?>
          <p class="shop-results-count"><?php echo count($products); ?> produktów</p>
          <?php if(!$selected): ?><div class="shop-sort"><label for="shop-sort">Sortowanie</label><select id="shop-sort" name="sort" form="shop-filters"><?php foreach(['menu_order'=>'Polecane','price'=>'Cena rosnąco','price-desc'=>'Cena malejąco','title'=>'Nazwa A–Z'] as $key=>$label): ?><option value="<?php echo esc_attr($key); ?>" <?php selected($order,$key); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></div><?php endif; ?>
        </div>
        <?php
        $elekt=$selected==='elekt'?array_filter($products,fn($p)=>wtf_shop_product_term($p,'wtf_product_game')==='elekt'):[];
        if($elekt) wtf_shop_elekt($elekt,$a,!$selected);
        $others=$selected==='elekt'?[]:$products;
        if($others): ?>
          <?php if($elekt): ?><h2 class="shop-other-heading">Pozostałe gry i publikacje</h2><?php endif; ?>
          <div class="shop-products"><?php foreach($others as $product)wtf_shop_card($product); ?></div>
        <?php endif; ?>
        <?php if(!$products): ?><p>Nie znaleziono produktów. Zmień filtry lub wyszukiwanie.</p><?php endif; ?></section>
      </div>
    </div>
    <?php return ob_get_clean();
}
