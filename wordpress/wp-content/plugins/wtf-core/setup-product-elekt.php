<?php
if(!defined('WP_CLI')||!WP_CLI)exit;
$map=get_option('wtf_shop_import_map'); $id=$map['products'][13255]??0;
if(!$id||!wc_get_product($id)) WP_CLI::error('Brak zaimportowanej podstawki Elekta.');
if(get_post_meta($id,'_wtf_product_template_seeded',true)){WP_CLI::success('Zachowano ustawienia szablonu produktu.');return;}
$p=wc_get_product($id);
if(!$p->get_meta('_wtf_product_lead')) {
    preg_match('~<em>(.*?)</em>~s',$p->get_short_description(),$intro);
    $p->update_meta_data('_wtf_product_lead',trim(wp_strip_all_tags($intro[1]??'')));
}
$p->update_meta_data('_wtf_product_editorial','yes');
$p->update_meta_data('_wtf_product_template_seeded',1);$p->save();
WP_CLI::success('Włączono roboczy szablon na podstawce Elekta.');
