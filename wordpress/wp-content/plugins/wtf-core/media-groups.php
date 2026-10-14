<?php
if(!defined('ABSPATH'))exit;
function wtf_core_media_groups_defaults(){return ['ogolne'=>'Ogólne','elekt'=>'Elekt','kurzolaki'=>'Kurzołaki','tsukuyumi'=>'Tsukuyumi','klanarchia'=>'Klanarchia','echo-swiatow'=>'Echo Światów','banery'=>'Banery','sklep'=>'Sklep'];}
add_action('init',function(){
    register_taxonomy('wtf_media_group','attachment',[
        'labels'=>['name'=>'Grupy mediów','singular_name'=>'Grupa mediów','search_items'=>'Szukaj grup','all_items'=>'Wszystkie grupy','edit_item'=>'Edytuj grupę','update_item'=>'Zapisz grupę','add_new_item'=>'Dodaj grupę','new_item_name'=>'Nazwa nowej grupy','menu_name'=>'Grupy mediów'],
        'public'=>false,'show_ui'=>true,'show_admin_column'=>false,'show_in_rest'=>true,'hierarchical'=>true,'rewrite'=>false,'query_var'=>false,'update_count_callback'=>'_update_generic_term_count',
        'capabilities'=>['manage_terms'=>'manage_categories','edit_terms'=>'manage_categories','delete_terms'=>'manage_categories','assign_terms'=>'upload_files'],
    ]);
    if(!get_option('wtf_media_groups_seeded')){
        foreach(wtf_core_media_groups_defaults() as $slug=>$name){$term=term_exists($slug,'wtf_media_group')?:wp_insert_term($name,'wtf_media_group',['slug'=>$slug]);if(is_wp_error($term))return;}
        update_option('wtf_media_groups_seeded',1,false);
    }
});
add_action('admin_menu',function(){add_media_page('Grupy mediów','Grupy mediów','manage_categories','edit-tags.php?taxonomy=wtf_media_group&post_type=attachment');});
add_filter('parent_file',function($parent){$screen=function_exists('get_current_screen')?get_current_screen():null;return $screen&&$screen->taxonomy==='wtf_media_group'?'upload.php':$parent;});
add_filter('submenu_file',function($submenu){$screen=function_exists('get_current_screen')?get_current_screen():null;return $screen&&$screen->taxonomy==='wtf_media_group'?'edit-tags.php?taxonomy=wtf_media_group&post_type=attachment':$submenu;});

add_filter('attachment_fields_to_edit',function($fields,$post){
    unset($fields['wtf_media_group']);if(!current_user_can('edit_post',$post->ID)||!current_user_can('upload_files'))return $fields;
    $terms=get_terms(['taxonomy'=>'wtf_media_group','hide_empty'=>false,'orderby'=>'name']);if(is_wp_error($terms))return $fields;
    $selected=wp_get_object_terms($post->ID,'wtf_media_group',['fields'=>'ids']);if(is_wp_error($selected))$selected=[];
    $html='<input type="hidden" name="attachments['.(int)$post->ID.'][wtf_media_groups_present]" value="1"><div class="wtf-media-groups-choices">';
    foreach($terms as $term)$html.='<label><input type="checkbox" name="attachments['.(int)$post->ID.'][wtf_media_groups][]" value="'.(int)$term->term_id.'" '.checked(in_array($term->term_id,$selected,true),true,false).'> '.esc_html($term->name).'</label>';
    $html.='</div>';$fields['wtf_media_groups']=['label'=>'Grupy','input'=>'html','html'=>$html,'helps'=>'Możesz zaznaczyć kilka grup.'];return $fields;
},10,2);
add_filter('attachment_fields_to_save',function($post,$attachment){
    $id=absint($post['ID']??0);
    if(!$id||!current_user_can('edit_post',$id)||!current_user_can('upload_files')||empty($attachment['wtf_media_groups_present']))return $post;
    $ids=is_array($attachment['wtf_media_groups']??null)?array_unique(array_map('absint',$attachment['wtf_media_groups'])):[];
    $ids=array_values(array_filter($ids,static fn($term)=>$term&&term_exists($term,'wtf_media_group')));
    wp_set_object_terms($id,$ids,'wtf_media_group');return $post;
},10,2);

function wtf_core_media_group_query($args,$raw){
    if(!is_scalar($raw))return $args;$id=filter_var($raw,FILTER_VALIDATE_INT);if($id===false||$id===0)return $args;
    if($id===-1)$condition=['taxonomy'=>'wtf_media_group','operator'=>'NOT EXISTS'];
    elseif($id>0&&term_exists($id,'wtf_media_group'))$condition=['taxonomy'=>'wtf_media_group','field'=>'term_id','terms'=>[$id]];
    else{$args['post__in']=[0];return $args;}
    $args['tax_query']=empty($args['tax_query'])?[$condition]:['relation'=>'AND',$args['tax_query'],$condition];return $args;
}
add_filter('ajax_query_attachments_args',function($args){return wtf_core_media_group_query($args,$_REQUEST['query']['wtf_media_group']??0);});
add_action('pre_get_posts',function($query){
    if(!is_admin()||!$query->is_main_query()||($GLOBALS['pagenow']??'')!=='upload.php')return;
    $args=wtf_core_media_group_query([],$_GET['wtf_media_group']??0);foreach($args as $key=>$value)$query->set($key,$value);
});
add_action('restrict_manage_posts',function($type){
    if($type!=='attachment')return;$terms=get_terms(['taxonomy'=>'wtf_media_group','hide_empty'=>false]);if(is_wp_error($terms))return;
    $selected=is_scalar($_GET['wtf_media_group']??0)?(int)($_GET['wtf_media_group']??0):0;
    echo '<label class="screen-reader-text" for="wtf-media-list-group">Grupa mediów</label><select name="wtf_media_group" id="wtf-media-list-group"><option value="0">Wszystkie grupy</option><option value="-1" '.selected($selected,-1,false).'>Bez grupy</option>';
    foreach($terms as $term)echo '<option value="'.(int)$term->term_id.'" '.selected($selected,$term->term_id,false).'>'.esc_html($term->name).'</option>';echo '</select>';
});
add_filter('manage_media_columns',function($columns){$columns['wtf_media_groups']='Grupy';return $columns;});
add_action('manage_media_custom_column',function($column,$id){
    if($column!=='wtf_media_groups')return;$terms=wp_get_object_terms($id,'wtf_media_group');if(is_wp_error($terms)||!$terms){echo '—';return;}
    $links=[];foreach($terms as $term)$links[]='<a href="'.esc_url(add_query_arg(['mode'=>'list','wtf_media_group'=>$term->term_id],admin_url('upload.php'))).'">'.esc_html($term->name).'</a>';echo implode(', ',$links);
},10,2);

add_action('wp_enqueue_media',function(){
    $terms=get_terms(['taxonomy'=>'wtf_media_group','hide_empty'=>false]);if(is_wp_error($terms))return;
    wp_enqueue_script('wtf-media-groups',plugins_url('media-groups.js',__FILE__),['media-views'],filemtime(__DIR__.'/media-groups.js'),true);
    wp_localize_script('wtf-media-groups','wtfMediaGroups',['terms'=>array_map(static fn($term)=>['id'=>$term->term_id,'name'=>$term->name],$terms)]);
    wp_enqueue_style('wtf-media-groups',plugins_url('media-groups.css',__FILE__),[],filemtime(__DIR__.'/media-groups.css'));
});
