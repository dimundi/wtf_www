<?php
if(!defined('ABSPATH'))exit;

function wtf_bundle_items($product){
    if($product->meta_exists('_wtf_bundle_items')){
        $items=$product->get_meta('_wtf_bundle_items');
        return is_array($items)?$items:[];
    }
    $legacy=(int)$product->get_meta('_wtf_bundle_gift_id');
    return $legacy?[['product_id'=>$legacy,'quantity'=>1,'is_gift'=>true]]:[];
}

function wtf_bundle_description($product){
    if(wtf_shop_product_term($product,'wtf_product_kind')!=='zestaw')return '';
    $html='<p>W&nbsp;skład zestawu wchodzą:</p><ul class="wtf-bundle-contents">';
    foreach(wtf_bundle_items($product) as $item){
        $id=(int)($item['product_id']??0);$component=$id?wc_get_product($id):false;
        if(!$component)continue;
        $name=esc_html($component->get_name());
        if($component->get_status()==='publish')$name='<a href="'.esc_url($component->get_permalink()).'">'.$name.'</a>';
        $html.='<li>'.$name.' — '.esc_html(max(1,(int)($item['quantity']??1))).' szt.'.(!empty($item['is_gift'])?' <strong class="wtf-bundle-contents-gift">Gratis</strong>':'').'</li>';
    }
    return $html.'</ul>';
}

function wtf_bundle_product_boxes($product){
    $html='<div class="wtf-bundle-product-boxes">';
    foreach(wtf_bundle_items($product) as $item){
        $id=(int)($item['product_id']??0);$component=$id?wc_get_product($id):false;
        if(!$component)continue;
        $url=$component->get_status()==='publish'?$component->get_permalink():'';
        $html.='<article class="wtf-bundle-product-box'.(!empty($item['is_gift'])?' is-gift':'').'">';
        $image=$component->get_image('medium_large',['loading'=>'lazy','alt'=>$component->get_name(),'sizes'=>'(max-width:700px) 85vw, 400px']);
        $html.=($url?'<a class="wtf-bundle-box-image" href="'.esc_url($url).'">':'<div class="wtf-bundle-box-image">').$image.($url?'</a>':'</div>');
        $html.='<div class="wtf-bundle-box-copy"><span class="wtf-bundle-box-quantity">'.esc_html(max(1,(int)($item['quantity']??1))).' szt. w zestawie</span>';
        if(!empty($item['is_gift']))$html.='<strong class="wtf-bundle-box-gift">GRATIS</strong>';
        $html.='<h3>'.($url?'<a href="'.esc_url($url).'">':'').esc_html($component->get_name()).($url?'</a>':'').'</h3>';
        if($url)$html.='<a class="wtf-bundle-box-link" href="'.esc_url($url).'">Poznaj produkt</a>';
        $html.='</div></article>';
    }
    return $html.'</div>';
}

function wtf_bundle_full_description($bundle){
    static $rendering=false;
    if($rendering)return '';
    $rendering=true;
    $html=wtf_bundle_product_boxes($bundle).'<div class="wtf-bundle-full-descriptions">';
    $previous_product=$GLOBALS['product']??null;
    try{
        foreach(wtf_bundle_items($bundle) as $item){
            $id=(int)($item['product_id']??0);$component=$id?wc_get_product($id):false;
            if(!$component||$id===$bundle->get_id()||$component->get_status()!=='publish')continue;
            $GLOBALS['product']=$component;
            $body=apply_filters('the_content',$component->get_description());
            $html.='<section class="wtf-bundle-component-description" aria-labelledby="bundle-component-title-'.esc_attr($id).'"><header><span class="wtf-bundle-box-quantity">'.esc_html(max(1,(int)($item['quantity']??1))).' szt. w zestawie</span>';
            if(!empty($item['is_gift']))$html.=' <strong class="wtf-bundle-box-gift">GRATIS</strong>';
            $html.='<h3 id="bundle-component-title-'.esc_attr($id).'">'.esc_html($component->get_name()).'</h3>'.$component->get_image('medium_large',['loading'=>'lazy','alt'=>$component->get_name(),'sizes'=>'(max-width:700px) 85vw, 300px']).'</header><div class="wtf-bundle-component-detail"><div class="wtf-bundle-component-body" id="bundle-component-body-'.esc_attr($id).'"><div class="wtf-bundle-component-text">'.$body.'</div></div><button type="button" class="wtf-bundle-expand" aria-expanded="false" aria-controls="bundle-component-body-'.esc_attr($id).'" hidden>Rozwiń</button></div></section>';
        }
    }finally{$GLOBALS['product']=$previous_product;$rendering=false;}
    return $html.'</div>';
}

add_action('woocommerce_single_product_summary',function(){
    global $product;
    if($product instanceof WC_Product)echo wtf_bundle_description($product);
},11);
add_filter('woocommerce_short_description',function($description){
    global $product;
    if(function_exists('is_product')&&is_product()&&$product instanceof WC_Product&&wtf_shop_product_term($product,'wtf_product_kind')==='zestaw')return '';
    return $description;
},40);

add_filter('woocommerce_product_tabs',function($tabs){
    unset($tabs['reviews']);
    global $product;
    if($product instanceof WC_Product&&wtf_shop_product_term($product,'wtf_product_kind')==='zestaw'){
        $tabs['description']=['title'=>'Zawartość zestawu','priority'=>10,'callback'=>function()use($product){echo wtf_bundle_full_description($product);}];
    }
    return $tabs;
},99);
add_filter('comments_open',function($open,$id){return get_post_type($id)==='product'?false:$open;},20,2);

function wtf_bundle_validate_items($rows,$owner){
    if(!is_array($rows))return new WP_Error('bundle_rows','Nie zapisano składu zestawu: nieprawidłowa lista.');
    $result=[];$seen=[];
    foreach($rows as $row){
        if(!is_array($row))return new WP_Error('bundle_row','Nie zapisano składu zestawu: nieprawidłowy wiersz.');
        $raw=$row['product_id']??'';
        if($raw===''||$raw==='0'||$raw===0)continue;
        if(!is_scalar($raw)||!ctype_digit((string)$raw))return new WP_Error('bundle_id','Nie zapisano składu zestawu: wybierz produkt.');
        $id=(int)$raw;$product=wc_get_product($id);
        if($id===$owner||!$product||!$product->is_type('simple')||$product->get_status()!=='publish'||wtf_shop_product_term($product,'wtf_product_kind')==='zestaw')return new WP_Error('bundle_product','Nie zapisano składu zestawu: wybierz opublikowany produkt prosty, inny niż zestaw.');
        if(isset($seen[$id]))return new WP_Error('bundle_duplicate','Nie zapisano składu zestawu: produkt może wystąpić tylko raz. Zmień jego ilość.');
        $qty=$row['quantity']??'';
        if(!is_scalar($qty)||!ctype_digit((string)$qty)||(int)$qty<1||(float)$qty>9999)return new WP_Error('bundle_quantity','Nie zapisano składu zestawu: ilość musi być całkowita, od 1 do 9999.');
        $seen[$id]=true;
        $result[]=['product_id'=>$id,'quantity'=>(int)$qty,'is_gift'=>isset($row['is_gift'])&&in_array($row['is_gift'],['1',1,true],true)];
    }
    return $result;
}

function wtf_bundle_admin_fields($product){
    $items=wtf_bundle_items($product);
    $options=[];
    foreach(wc_get_products(['status'=>'publish','type'=>'simple','limit'=>-1,'orderby'=>'title','order'=>'ASC']) as $candidate){
        if($candidate->get_id()!==$product->get_id()&&wtf_shop_product_term($candidate,'wtf_product_kind')!=='zestaw')$options[$candidate->get_id()]=$candidate->get_name();
    }
    $row=function($index,$item)use($options){ ?>
      <tr><td><select name="wtf_bundle_items[<?php echo esc_attr($index); ?>][product_id]" aria-label="Produkt w zestawie"><option value="">— Wybierz produkt —</option><?php
      $id=(int)($item['product_id']??0);
      if($id&&!isset($options[$id]))echo '<option value="'.esc_attr($id).'" selected>'.esc_html(get_the_title($id)?:'Niedostępny produkt #'.$id).'</option>';
      foreach($options as $value=>$label): ?><option value="<?php echo esc_attr($value); ?>" <?php selected($id,$value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td>
      <td><input type="number" min="1" max="9999" step="1" name="wtf_bundle_items[<?php echo esc_attr($index); ?>][quantity]" value="<?php echo esc_attr($item['quantity']??1); ?>" aria-label="Liczba sztuk"></td>
      <td><input type="checkbox" name="wtf_bundle_items[<?php echo esc_attr($index); ?>][is_gift]" value="1" <?php checked(!empty($item['is_gift'])); ?> aria-label="Gratis"></td>
      <td><button type="button" class="button" data-bundle-remove>Usuń</button></td></tr>
    <?php }; ?>
    <div class="wtf-bundle-config" <?php if(wtf_shop_product_term($product,'wtf_product_kind')!=='zestaw')echo 'hidden'; ?>>
      <h4>Zawartość zestawu</h4><input type="hidden" name="wtf_bundle_items_present" value="1">
      <table class="widefat"><thead><tr><th>Produkt</th><th>Sztuk</th><th>Gratis</th><th></th></tr></thead><tbody><?php foreach($items as $i=>$item)$row($i,$item); ?></tbody></table>
      <button type="button" class="button" data-bundle-add>Dodaj produkt</button>
      <template data-bundle-template><?php $row('__INDEX__',[]); ?></template>
      <div class="description">Cena zestawu jest ustalana osobno. Zaznaczone gratisy pokazują swoje grafiki na karcie zestawu. Zdjęcia bez kadrowania; zalecane minimum 600 × 600 px. Skład nie zmienia stanów magazynowych ani pozycji zamówienia.</div>
    </div>
<?php }

add_action('admin_enqueue_scripts',function(){
    $screen=get_current_screen();if(!$screen||$screen->post_type!=='product')return;
    wp_enqueue_script('wtf-shop-bundles',plugins_url('shop-bundles.js',__FILE__),[],filemtime(__DIR__.'/shop-bundles.js'),true);
    wp_enqueue_style('wtf-shop-bundles',plugins_url('shop-bundles.css',__FILE__),[],filemtime(__DIR__.'/shop-bundles.css'));
});
