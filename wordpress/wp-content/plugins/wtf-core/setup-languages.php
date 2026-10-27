<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if(!function_exists('PLL'))WP_CLI::error('Najpierw zainstaluj darmowy Polylang.');
if(get_option('wtf_languages_initialized')){WP_CLI::success('Wersje językowe istnieją — zachowane.');return;}
$model=PLL()->model;
foreach(['pl_PL','en_US'] as $locale){
    if(!$model->get_language($locale)){
        $language=$model->add_language(['locale'=>$locale]);
        if(is_wp_error($language))WP_CLI::error($language->get_error_message());
    }
}
$model->update_default_lang('pl');
PLL()->options['browser']=0;
PLL()->options['hide_default']=1;
PLL()->options['force_lang']=1;
$pl=(int)get_option('page_on_front');
pll_set_post_language($pl,'pl');
$translations=json_decode(file_get_contents(__DIR__.'/english.json'),true);
function wtf_dev_translate($value,$translations){
    if(is_array($value))return array_map(fn($item)=>wtf_dev_translate($item,$translations),$value);
    return is_string($value)?($translations[$value]??$value):$value;
}
$blocks=parse_blocks(get_post_field('post_content',$pl));
foreach($blocks as &$block)$block['attrs']=wtf_dev_translate($block['attrs'],$translations);
unset($block);
$en=wp_insert_post(wp_slash(['post_type'=>'page','post_status'=>wtf_core_english_enabled()?'publish':'draft','post_title'=>'Home','post_name'=>'home','post_content'=>serialize_blocks($blocks)]),true);
if(is_wp_error($en))WP_CLI::error($en->get_error_message());
pll_set_post_language($en,'en');pll_save_post_translations(['pl'=>$pl,'en'=>$en]);
// Import pierwszych tłumaczeń napisów interfejsu; dalsza edycja w panelu Polylang.
$mo=new PLL_MO();$mo->import_from_db($model->get_language('en'));
foreach($translations as $source=>$translation)$mo->add_entry(new Translation_Entry(['singular'=>$source,'translations'=>[$translation]]));
$mo->export_to_db($model->get_language('en'));
flush_rewrite_rules();update_option('wtf_languages_initialized',1);
WP_CLI::success('Połączono stronę główną PL/EN i zapisano tłumaczenia interfejsu.');
