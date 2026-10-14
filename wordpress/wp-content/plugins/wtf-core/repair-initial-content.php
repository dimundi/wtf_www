<?php
// Jednorazowa korekta pierwszego importu dev; nie nadpisuje innych zmian treści.
if(!defined('WP_CLI')||!WP_CLI)exit;
$fixes=['Małe wydawnictwo.nWielkie światy.'=>"Małe wydawnictwo.\nWielkie światy.",'Sięgnij po tron.nZapłać mniej.'=>"Sięgnij po tron.\nZapłać mniej.",'Z wyobraźni.nDla graczy.'=>"Z wyobraźni.\nDla graczy."];
$english=json_decode(file_get_contents(__DIR__.'/english.json'),true);
foreach(pll_get_post_translations((int)get_option('page_on_front')) as $lang=>$id){
    $blocks=parse_blocks(get_post_field('post_content',$id));
    foreach($blocks as &$block){
        if(isset($block['attrs']['title'],$fixes[$block['attrs']['title']]))$block['attrs']['title']=$fixes[$block['attrs']['title']];
        if($lang==='en' && isset($block['attrs']['title'],$english[$block['attrs']['title']]))$block['attrs']['title']=$english[$block['attrs']['title']];
    }
    unset($block);
    wp_update_post(wp_slash(['ID'=>$id,'post_content'=>serialize_blocks($blocks)]));
}
WP_CLI::success('Skorygowano zapis nowych linii w pierwszym imporcie.');
