<?php
if (!defined('ABSPATH')) exit;

function wtfo_note($product) {
    if ($product instanceof WC_Product_Variable && get_woocommerce_currency()==='PLN' && $product->is_on_sale()) {
        $notes=[];
        foreach ($product->get_visible_children() as $id) {
            $variant=wc_get_product($id);
            if (!$variant) continue;
            $note=wtfo_note($variant);
            if ($note) $notes[]='<span class="wtfo-variant-name">'.esc_html(wc_get_formatted_variation($variant,true,false,true)).'</span>'.$note;
        }
        return $notes ? '<span class="wtfo-variant-notes">'.implode('',$notes).'</span>' : '';
    }
    if (!wtfo_supported($product) || !$product->is_on_sale()) return '';
    $state=wtfo_state($product->get_id());
    if (!isset($state['reference']) || empty($state['sale'])) return '';
    $label=!empty($state['short']) ? 'Najniższa cena od rozpoczęcia sprzedaży przed obniżką:' : 'Najniższa cena z 30 dni przed obniżką:';
    $price=wc_get_price_to_display($product,['price'=>(float)$state['reference']]);
    return '<span class="wtfo-price-note">'.esc_html($label).' <span class="wtfo-reference">'.wc_price($price).'</span></span>';
}

add_filter('woocommerce_get_price_html', function ($html,$product) {
    if (is_admin() && !wp_doing_ajax()) return $html;
    return $html.wtfo_note($product);
}, 50, 2);

add_filter('woocommerce_available_variation', function ($data,$parent,$variation) {
    $note=wtfo_note($variation);
    if ($note && !str_contains($data['price_html'],'wtfo-price-note')) {
        $data['price_html']='<span class="price">'.$variation->get_price_html().'</span>';
    }
    return $data;
},50,3);

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('wtf-omnibus',plugins_url('../assets/front.css',__FILE__),[], '0.1.0');
});
