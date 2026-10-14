<?php
// Uruchamiane ręcznie przez WP-CLI na kopii źródłowej. Bez danych klientów.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$products = array_filter(wc_get_products(['status'=>'publish','limit'=>-1]), function($product) {
    return in_array($product->get_catalog_visibility(), ['visible','catalog'], true)
        && !('yes' === get_option('woocommerce_hide_out_of_stock_items') && !$product->is_in_stock());
});
$out = ['products'=>[], 'media'=>[], 'terms'=>[], 'settings'=>[]];
$out['inline_files']=[];
$out['selection']=['visibility'=>['visible','catalog'],'hide_out_of_stock'=>get_option('woocommerce_hide_out_of_stock_items'),'published_total'=>count(wc_get_products(['status'=>'publish','limit'=>-1]))];
foreach (['woocommerce_permalinks','woocommerce_currency','woocommerce_currency_pos','woocommerce_price_thousand_sep','woocommerce_price_decimal_sep','woocommerce_price_num_decimals','woocommerce_prices_include_tax','woocommerce_tax_display_shop','woocommerce_calc_taxes','woocommerce_default_country'] as $key) $out['settings'][$key]=get_option($key);
foreach ($products as $product) {
    $ids=[$product->get_id()];
    if ($product->is_type('variable')) $ids=array_merge($ids,$product->get_children());
    foreach ($ids as $id) {
        $p=wc_get_product($id); if (!$p) continue;
        $data=$p->get_data(); $data['type']=$p->get_type(); $data['source_path']=wp_parse_url(get_permalink($id),PHP_URL_PATH);
        $data['attributes']=array_map(fn($a)=>$a instanceof WC_Product_Attribute ? $a->get_data() : $a,$p->get_attributes());
        $data['downloads']=array_map(fn($d)=>$d->get_data(),$p->get_downloads());
        $out['products'][]=$data;
        preg_match_all('~https?://(?:localhost:8082|(?:www\.)?whatthefrog\.pl)(/wp-content/uploads/[^\s"<>\']+)~',$p->get_description().' '.$p->get_short_description(),$urls);
        foreach($urls[0] as $url) {
            $path=rawurldecode(wp_parse_url(html_entity_decode($url),PHP_URL_PATH));
            if(str_contains($path,'woocommerce_uploads/')) continue;
            $file=ABSPATH.ltrim($path,'/');
            if(is_file($file)) $out['inline_files'][$url]=['file'=>$file,'key'=>md5($path).'-'.basename($file)];
        }
        preg_match_all('/wp-image-(\d+)/',$p->get_description().' '.$p->get_short_description(),$matches);
        foreach (array_filter(array_merge([$p->get_image_id()],$p->get_gallery_image_ids(),$matches[1])) as $image) {
            $file=get_attached_file($image);
            $out['media'][$image]=['file'=>$file,'url'=>wp_get_attachment_url($image),'title'=>get_the_title($image),'alt'=>get_post_meta($image,'_wp_attachment_image_alt',true)];
        }
    }
}
foreach (['product_cat','product_tag','product_shipping_class'] as $tax) foreach (get_terms(['taxonomy'=>$tax,'hide_empty'=>false]) as $term) $out['terms'][$tax][]=(array)$term;
file_put_contents('/tmp/wtf-shop-export.json',wp_json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
WP_CLI::success(count($products).' products; '.count($out['products']).' including variations; '.count($out['media']).' images.');
