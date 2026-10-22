<?php
if(!defined('WP_CLI')||!WP_CLI) exit;
foreach(['shop'=>['sklep','Sklep','<!-- wp:wtf/shop /-->'],'cart'=>['koszyk','Koszyk','<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->'],'checkout'=>['zamowienie','Finalizowanie zamówienia','<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->'],'myaccount'=>['moje-konto','Moje konto','<!-- wp:shortcode -->[woocommerce_my_account]<!-- /wp:shortcode -->']] as $key=>$data){
    $id=(int)get_option('woocommerce_'.$key.'_page_id');
    if(!$id||!get_post($id)) {
        $page=get_page_by_path($data[0],OBJECT,['page']);
        $id=$page?$page->ID:wp_insert_post(['post_type'=>'page','post_status'=>'publish','post_name'=>$data[0],'post_title'=>$data[1],'post_content'=>$data[2]],true);
        if(is_wp_error($id)) WP_CLI::error($id->get_error_message());
        update_option('woocommerce_'.$key.'_page_id',$id);
        if(function_exists('pll_set_post_language')) pll_set_post_language($id,'pl');
    }
    if(!get_post_meta($id,'_wtf_shop_setup',true)) {
        wp_update_post(['ID'=>$id,'post_name'=>$data[0],'post_title'=>$data[1],'post_content'=>$data[2]]);
        update_post_meta($id,'_wtf_shop_setup',1);
        if(function_exists('pll_set_post_language')) pll_set_post_language($id,'pl');
    }
    WP_CLI::log($key.': '.$id);
}
WP_CLI::success('Strony sklepu gotowe.');
