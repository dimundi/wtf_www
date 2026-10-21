<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(423);
if(!$page || get_post_meta(423,'_wtf_ele_key',true)!=='wiedzmy2:pl') { WP_CLI::error('Brak strony.'); }
if(get_post_meta(423,'_wtf_wiedzmy2_media_restored',true)) { WP_CLI::success('Materiały już dodane.'); return; }
require_once ABSPATH.'wp-admin/includes/image.php';
$assets=[
'Elekt_Wiedzmy2_.png'=>['Wiedźmy 2 — pudełko','Pudełko dodatku Wiedźmy 2 do Elekta z portretem krzyczącej, jasnowłosej postaci.'],
'wiedzmy2-soldatka-zerczyni.png'=>['Wiedźmy 2 — żerczyni','Ilustracja żerczyni z dodatku Wiedźmy 2, z rogami i dwoma zakrzywionymi ostrzami.'],
'wiedzmy_box-1.png'=>['Wiedźmy — pierwsza część dodatku','Pudełko pierwszego dodatku Wiedźmy do Elekta z ciemnowłosą postacią na okładce.'],
'Elekt_dodatki_2023.png'=>['Elekt — cztery dodatki z 2023 roku','Pudełka dodatków Wiedźmy, Wiedźmy 2, Powołanie 2 i Rubież ustawione obok siebie.'],
'Wiedzmy2_3840x2160-scaled.jpg'=>['Wiedźmy 2 — tapeta panoramiczna 16:9','Tapeta z ilustracją dodatku Wiedźmy 2 do Elekta.'],
'Wiedzmy2_2560x1140-scaled.jpg'=>['Wiedźmy 2 — tapeta szeroka','Szeroka tapeta z ilustracją dodatku Wiedźmy 2 do Elekta.'],
];
$media=[];
foreach($assets as $file=>[$title,$alt]) {
    $relative='media/elekt-source-'.$file;$path=wp_get_upload_dir()['basedir'].'/'.$relative;
    if(!is_file($path)) { WP_CLI::error('Brak '.$file); }
    $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wtf_source_asset','meta_value'=>$relative,'suppress_filters'=>true]);
    if(!$existing) { $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wp_attached_file','meta_value'=>$relative,'suppress_filters'=>true]); }
    if($existing) { $id=$existing[0]->ID; }
    else {
        $id=wp_insert_attachment(['post_title'=>$title,'post_content'=>$alt,'post_mime_type'=>wp_check_filetype($file)['type'],'post_status'=>'inherit'],$path,0,true);
        if(is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
        update_post_meta($id,'_wtf_source_asset',$relative);update_post_meta($id,'_wp_attachment_image_alt',$alt);
        wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$path));
    }
    $term=get_term_by('name','Elekt','wtf_media_group');if($term) { wp_set_object_terms($id,[(int)$term->term_id],'wtf_media_group',true); }
    $media[$file]=['id'=>$id,'url'=>wp_get_attachment_url($id),'alt'=>$alt];
}
$make=static fn($name,$attrs,$children=[])=>['blockName'=>$name,'attrs'=>$attrs,'innerBlocks'=>$children,'innerHTML'=>'','innerContent'=>array_fill(0,count($children),null)];
$image=static fn($file)=>$make('wtf/ele-image',['imageId'=>$media[$file]['id'],'image'=>$media[$file]['url'],'alt'=>$media[$file]['alt'],'attributes'=>['loading'=>'lazy']]);
$text=static fn($tag,$content)=>$make('wtf/ele-text',['tag'=>$tag,'content'=>$content]);
$questions=get_posts(['post_type'=>'wtf_faq','post_status'=>'publish','numberposts'=>-1,'meta_query'=>[['key'=>'_wtf_faq_source_id','value'=>['elekt:12220','elekt:12219','elekt:12216'],'compare'=>'IN']],'suppress_filters'=>true]);
if(count($questions)!==3) { WP_CLI::error('Nie znaleziono trzech zaimportowanych pytań.'); }
$groups=[];foreach($questions as $q) { $groups=array_merge($groups,wp_get_object_terms($q->ID,'wtf_faq_group',['fields'=>'slugs'])); }
$shortcode='[wtf_faq game="elekt" groups="'.implode(',',array_unique($groups)).'"]';
$added=[
    $make('wtf/ele-container',['tag'=>'section','attributes'=>['class'=>'elekt-wiedzmy2-art'],'label'=>'Ilustracje Wiedźm'],[
        $image('wiedzmy2-soldatka-zerczyni.png'),
        $make('wtf/ele-container',['attributes'=>['class'=>'elekt-wiedzmy2-boxes'],'label'=>'Pudełka obu dodatków'],[$image('wiedzmy_box-1.png'),$image('Elekt_Wiedzmy2_.png')]),
    ]),
    $make('wtf/ele-container',['tag'=>'section','attributes'=>['class'=>'elekt-section'],'label'=>'FAQ Wiedźmy 2'],[
        $text('h2','Najczęściej zadawane pytania'),['blockName'=>'core/shortcode','attrs'=>[],'innerBlocks'=>[],'innerHTML'=>$shortcode,'innerContent'=>[$shortcode]],
    ]),
    $make('wtf/ele-container',['tag'=>'section','attributes'=>['class'=>'elekt-section'],'label'=>'Tapety Wiedźmy 2'],[
        $text('h2','Tapety do pobrania'),
        $text('p','Ilustracje: Justyna Szumczyk.'),
        $text('p','<a class="expansion-page-link" href="'.esc_url($media['Wiedzmy2_3840x2160-scaled.jpg']['url']).'">Tapeta 16:9</a>'),
        $text('p','<a class="expansion-page-link" href="'.esc_url($media['Wiedzmy2_2560x1140-scaled.jpg']['url']).'">Tapeta szeroka</a>'),
        $image('Elekt_dodatki_2023.png'),
    ]),
];
$blocks=parse_blocks($page->post_content);
if(count($blocks)!==1 || $blocks[0]['blockName']!=='wtf/ele-container') { WP_CLI::error('Nieoczekiwana struktura.'); }
$blocks[0]['innerBlocks']=array_merge($blocks[0]['innerBlocks'],$added);
$blocks[0]['innerHTML']='';$blocks[0]['innerContent']=array_fill(0,count($blocks[0]['innerBlocks']),null);
update_post_meta(423,'_wtf_before_restore_wiedzmy2_media',$page->post_content);
$saved=wp_update_post(['ID'=>423,'post_content'=>wp_slash(serialize_blocks($blocks))],true);
if(is_wp_error($saved)) { WP_CLI::error($saved->get_error_message()); }
update_post_meta(423,'_wtf_wiedzmy2_media_restored',11031);
WP_CLI::success('Dodano 4 grafiki, 2 tapety i źródłowe FAQ.');
