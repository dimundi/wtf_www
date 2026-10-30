<?php
if (!defined('ABSPATH')) exit;
require_once __DIR__.'/shop-bundles.php';

add_action('init', function () {
    foreach (['wtf_product_game'=>['Gra','Gry'], 'wtf_product_kind'=>['Rodzaj produktu','Rodzaje produktów']] as $taxonomy=>$labels) {
        register_taxonomy($taxonomy, ['product'], [
            'labels'=>['name'=>$labels[1], 'singular_name'=>$labels[0], 'menu_name'=>$labels[1]],
            'public'=>false, 'show_ui'=>true, 'show_in_rest'=>true, 'show_admin_column'=>true,
            'hierarchical'=>false, 'rewrite'=>false, 'meta_box_cb'=>false,
            'capabilities'=>['manage_terms'=>'manage_product_terms','edit_terms'=>'edit_product_terms','delete_terms'=>'delete_product_terms','assign_terms'=>'assign_product_terms'],
        ]);
    }
});

function wtf_shop_product_term($product, $taxonomy) {
    $terms=get_the_terms($product->get_id(),$taxonomy);
    return $terms && !is_wp_error($terms) ? reset($terms)->slug : '';
}

add_action('woocommerce_product_options_general_product_data',function(){
    global $product_object;
    if(!$product_object) return;
    echo '<div class="options_group">';
    wp_nonce_field('wtf_shop_classification','wtf_shop_classification_nonce');
    foreach(['wtf_product_game'=>'Gra','wtf_product_kind'=>'Rodzaj produktu'] as $taxonomy=>$label){
        $terms=get_terms(['taxonomy'=>$taxonomy,'hide_empty'=>false]); $options=[''=>'— Nie przypisano —'];
        foreach($terms as $term) $options[$term->slug]=$term->name;
        woocommerce_wp_select(['id'=>$taxonomy,'label'=>$label,'value'=>wtf_shop_product_term($product_object,$taxonomy),'options'=>$options,'desc_tip'=>true,'description'=>$taxonomy==='wtf_product_game'?'Gra, do której należy produkt.':'Określa sekcję produktu w sklepie: zestaw, podstawka, dodatek lub akcesorium. Zestaw jest wyróżniony i widoczny tylko po wybraniu przypisanej gry.']);
    }
    wtf_bundle_admin_fields($product_object);
    echo '</div>';
});
add_action('woocommerce_process_product_meta',function($id){
    if(!current_user_can('edit_post',$id) || !isset($_POST['wtf_shop_classification_nonce']) || !is_string($_POST['wtf_shop_classification_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtf_shop_classification_nonce'])),'wtf_shop_classification')) return;
    foreach(['wtf_product_game','wtf_product_kind'] as $taxonomy){
        if(!isset($_POST[$taxonomy]) || !is_string($_POST[$taxonomy])) continue;
        $slug=sanitize_title(wp_unslash($_POST[$taxonomy])); $term=$slug?get_term_by('slug',$slug,$taxonomy):false;
        if($slug&&!$term) continue;
        wp_set_object_terms($id,$term?[$term->term_id]:[],$taxonomy);
    }
    if(isset($_POST['wtf_bundle_items_present'])){
        $items=wtf_bundle_validate_items(wp_unslash($_POST['wtf_bundle_items']??[]),$id);
        if(is_wp_error($items)){
            if(class_exists('WC_Admin_Meta_Boxes'))WC_Admin_Meta_Boxes::add_error($items->get_error_message());
        }else{
            update_post_meta($id,'_wtf_bundle_items',$items);
            delete_post_meta($id,'_wtf_bundle_gift_id');
        }
    }
});
