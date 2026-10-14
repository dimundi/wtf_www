<?php
// Ręczne, jednokrotne przypisanie początkowej oferty. Powtórzenie zachowuje edycje.
if(!defined('WP_CLI')||!WP_CLI) exit;
foreach(['wtf_product_game'=>['elekt'=>'Elekt','kurzolaki'=>'Kurzołaki','tsukuyumi'=>'Tsukuyumi','klanarchia'=>'Klanarchia'], 'wtf_product_kind'=>['podstawka'=>'Podstawka','dodatek'=>'Dodatek','akcesorium'=>'Akcesorium']] as $taxonomy=>$terms){
    foreach($terms as $slug=>$name) if(!term_exists($slug,$taxonomy)) {
        $result=wp_insert_term($name,$taxonomy,['slug'=>$slug]);
        if(is_wp_error($result)) WP_CLI::error($result->get_error_message());
    }
}
$map=get_option('wtf_shop_import_map',[]);
$kinds=[13255=>'podstawka',12184=>'dodatek',11776=>'dodatek',11775=>'dodatek',11772=>'dodatek',11769=>'dodatek',8279=>'dodatek',1232=>'akcesorium',1160=>'akcesorium',1141=>'akcesorium',1103=>'akcesorium',1090=>'akcesorium',986=>'akcesorium',900=>'akcesorium',828=>'akcesorium'];
foreach($map['products']??[] as $old=>$id){
    $p=wc_get_product($id); if(!$p||$p->is_type('variation')||$p->get_meta('_wtf_shop_classification_seeded')) continue;
    if(!wtf_shop_product_term($p,'wtf_product_game')) {
        foreach($p->get_category_ids() as $cat){
            $term=get_term($cat,'product_cat'); if(!$term||is_wp_error($term)) continue;
            while($term->parent) $term=get_term($term->parent,'product_cat');
            if(in_array($term->slug,['elekt','kurzolaki','tsukuyumi','klanarchia'],true)) {
                wp_set_object_terms($id,$term->slug,'wtf_product_game'); break;
            }
        }
    }
    if(isset($kinds[$old])&&!wtf_shop_product_term($p,'wtf_product_kind')) wp_set_object_terms($id,$kinds[$old],'wtf_product_kind');
    $p->update_meta_data('_wtf_shop_classification_seeded',1);$p->save();
}
$shop=wc_get_page_id('shop');
if($shop>0&&!get_post_meta($shop,'_wtf_shop_elekt_setup',true)){
    $blocks=parse_blocks(get_post_field('post_content',$shop));
    foreach($blocks as &$block) if($block['blockName']==='wtf/shop'&&empty($block['attrs']['imageId'])&&empty($block['attrs']['image'])) {
        $image=attachment_url_to_postid(wtf_core_media('elekt.webp')) ?: attachment_url_to_postid(wtf_core_media('elekt.jpg'));
        if($image) $block['attrs']['imageId']=$image;
    }
    unset($block);
    wp_update_post(['ID'=>$shop,'post_content'=>serialize_blocks($blocks)]);
    update_post_meta($shop,'_wtf_shop_elekt_setup',1);
}
WP_CLI::success('Przypisano gry i rodzaje oferty Elekta.');
