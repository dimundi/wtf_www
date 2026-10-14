<?php
if (!defined('ABSPATH')) { exit; }
require_once __DIR__.'/questions.php';
add_action('admin_menu', function () {
    add_menu_page('WTF FAQ','WTF FAQ','edit_posts','wtf_faq','wtf_faq_admin_screen','dashicons-editor-help',26);
    add_submenu_page('wtf_faq','Shortcode','Shortcode','edit_posts','wtf_faq','wtf_faq_admin_screen');
    add_submenu_page('wtf_faq','Pytania FAQ','Pytania','edit_posts','wtf_faq_questions','wtf_faq_questions_screen');
    add_submenu_page('wtf_faq','Grupy','Grupy','manage_categories','edit-tags.php?taxonomy=wtf_faq_group&post_type=wtf_faq');
    add_submenu_page('wtf_faq','Gry','Gry','manage_categories','edit-tags.php?taxonomy=wtf_faq_game&post_type=wtf_faq');
});
add_action('admin_menu',function () { remove_submenu_page('wtf_faq','edit.php?post_type=wtf_faq'); },99);
function wtf_faq_admin_screen() {
    if (!current_user_can('edit_posts')) { return; }
    $games=get_terms(['taxonomy'=>'wtf_faq_game','hide_empty'=>false]);
    $groups=get_terms(['taxonomy'=>'wtf_faq_group','hide_empty'=>false]);
    ?><div class="wrap"><h1>WTF FAQ</h1><p>Wybierz grę i grupy, a następnie wklej shortcode na stronie.</p>
    <p><label>Gra <select id="wtf-faq-game"><option value="">Wszystkie gry</option><?php foreach ($games as $game): ?><option value="<?php echo esc_attr($game->slug); ?>"><?php echo esc_html($game->name); ?></option><?php endforeach; ?></select></label></p>
    <fieldset id="wtf-faq-groups"><legend><strong>Grupy</strong></legend><?php foreach ($groups as $group): ?><p><label><input type="checkbox" value="<?php echo esc_attr($group->slug); ?>"> <?php echo esc_html($group->name); ?></label></p><?php endforeach; ?></fieldset>
    <p><label for="wtf-faq-shortcode">Shortcode</label><br><input id="wtf-faq-shortcode" type="text" readonly class="large-text code" aria-describedby="wtf-faq-shortcode-help"></p>
    <p id="wtf-faq-shortcode-help">Zaznacz co najmniej jedną grupę. Kolejność pytań zmienisz polem „Kolejność” podczas edycji pytania.</p></div><?php
}
add_action('admin_enqueue_scripts', function ($hook) {
    $screen=get_current_screen();
    $terms=$screen && $screen->base==='edit-tags' && in_array($screen->taxonomy,['wtf_faq_group','wtf_faq_game'],true);
    if ($hook==='toplevel_page_wtf_faq' || $terms) { wp_enqueue_script('wtf_faq_admin',plugins_url('../assets/admin.js',__FILE__),[],filemtime(WTF_FAQ_DIR.'/assets/admin.js'),true); }
    if ($terms || (isset($_GET['page']) && $_GET['page']==='wtf_faq_questions')) { wp_enqueue_style('wtf_faq_admin',plugins_url('../assets/admin.css',__FILE__),[],filemtime(WTF_FAQ_DIR.'/assets/admin.css')); }
});
add_filter('parent_file', function ($parent) {
    $screen=get_current_screen();
    if ($screen && ($screen->post_type==='wtf_faq' || in_array($screen->taxonomy,['wtf_faq_group','wtf_faq_game'],true))) { return 'wtf_faq'; }
    return $parent;
});
add_filter('submenu_file', function ($submenu) {
    $screen=get_current_screen();
    if (!$screen) { return $submenu; }
    if (in_array($screen->taxonomy,['wtf_faq_group','wtf_faq_game'],true)) { return 'edit-tags.php?taxonomy='.$screen->taxonomy.'&post_type=wtf_faq'; }
    if ($screen->post_type==='wtf_faq') { return 'wtf_faq_questions'; }
    return $submenu;
});
add_action('add_meta_boxes_wtf_faq', function () {
    add_meta_box('wtf_faq_assignment','Grupa i gra','wtf_faq_assignment_box','wtf_faq','side','high');
    add_meta_box('wtf_faq_votes','Głosy','wtf_faq_votes_box','wtf_faq','side');
});
function wtf_faq_assignment_box($post) {
    wp_nonce_field('wtf_faq_assignment','wtf_faq_assignment_nonce');
    foreach (['wtf_faq_group'=>'Grupa','wtf_faq_game'=>'Gra'] as $taxonomy=>$label) {
        $selected=wp_get_object_terms($post->ID,$taxonomy,['fields'=>'ids']);
        $selectedId=$selected&&!is_wp_error($selected)?(int)$selected[0]:0;
        $filter=$taxonomy==='wtf_faq_group'?'faq_group':'faq_game';
        if (!$selectedId && $post->post_status==='auto-draft' && isset($_GET[$filter])) {
            $requested=absint($_GET[$filter]);
            if ($requested && term_exists($requested,$taxonomy)) { $selectedId=$requested; }
        }
        echo '<p><label>'.esc_html($label).'<br>';
        wp_dropdown_categories(['taxonomy'=>$taxonomy,'hide_empty'=>false,'name'=>$taxonomy,'selected'=>$selectedId,'show_option_none'=>'Wybierz','option_none_value'=>'','required'=>true]);
        echo '</label></p>';
    }
}
function wtf_faq_votes_box($post) {
    $votes=wtf_faq_vote_counts([$post->ID])[$post->ID];
    echo '<p>👍 '.(int)$votes['up'].' &nbsp; 👎 '.(int)$votes['down'].'</p>';
}
add_action('save_post_wtf_faq', function ($id) {
    if (wp_is_post_revision($id) || (defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) || !current_user_can('edit_post',$id)) { return; }
    if (empty($_POST['wtf_faq_assignment_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtf_faq_assignment_nonce'])),'wtf_faq_assignment')) { return; }
    foreach (['wtf_faq_group','wtf_faq_game'] as $taxonomy) {
        $term=isset($_POST[$taxonomy])?absint($_POST[$taxonomy]):0;
        if ($term && term_exists($term,$taxonomy)) { wp_set_object_terms($id,[$term],$taxonomy); }
        else { wp_set_object_terms($id,[],$taxonomy); }
    }
});
add_filter('manage_wtf_faq_posts_columns',function ($columns) {$columns['wtf_faq_votes']='👍 / 👎';return $columns;});
add_action('manage_wtf_faq_posts_custom_column',function ($column,$id) {
    if ($column==='wtf_faq_votes') { $votes=wtf_faq_vote_counts([$id])[$id];echo (int)$votes['up'].' / '.(int)$votes['down']; }
},10,2);
