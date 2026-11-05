<?php
if (!defined('ABSPATH')) { exit; }
require_once __DIR__.'/csv.php';
require_once __DIR__.'/schema.php';
require_once __DIR__.'/parsers.php';
require_once __DIR__.'/data.php';
require_once __DIR__.'/import.php';
require_once __DIR__.'/admin.php';
require_once __DIR__.'/diff.php';
require_once __DIR__.'/campaign-admin.php';

function wtfg_require_admin() {
    if (!current_user_can('manage_options')) { wp_die('Brak uprawnień.','',['response'=>403]); }
    if (get_option('wtfg_schema')!==WTFG_SCHEMA_VERSION) { wp_die('Struktura danych Gamefound wymaga przygotowania przez administratora. Nie zmieniono danych.'); }
}

function wtfg_valid_campaign($id) {
    return $id && get_post_type($id)==='wtfg_campaign' && !in_array(get_post_status($id),['trash','auto-draft'],true);
}

function wtfg_project_id($id) { return (string)get_post_meta($id,'_wtfg_project_id',true); }
function wtfg_selected_campaign() {
    $id=absint(get_user_meta(get_current_user_id(),'_wtfg_selected_campaign',true));
    return wtfg_valid_campaign($id)?$id:0;
}

function wtfg_check_project_binding($campaign,$project) {
    if ($project==='') { return; }
    $other=get_posts(['post_type'=>'wtfg_campaign','post_status'=>'any','numberposts'=>1,'fields'=>'ids','post__not_in'=>[$campaign],'meta_key'=>'_wtfg_project_id','meta_value'=>$project]);
    if ($other) { throw new RuntimeException('Ten ProjectID jest przypisany do innej zbiórki.'); }
}

function wtfg_project_form($campaign) {
    echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    wp_nonce_field('wtfg_project_'.$campaign);
    echo '<input type="hidden" name="action" value="wtfg_project"><input type="hidden" name="campaign_id" value="'.(int)$campaign.'"><p><label>ProjectID Gamefound <input name="project_id" inputmode="numeric" pattern="[0-9]{1,64}" maxlength="64" value="'.esc_attr(wtfg_project_id($campaign)).'"></label> <button class="button">Zapisz konfigurację</button></p><p>Ustaw ID przed importem Order items. Import Orders może odczytać je automatycznie, jeśli plik dotyczy jednej zbiórki.</p></form>';
}

add_action('admin_post_wtfg_project',function () {
    wtfg_require_admin(); $campaign=absint($_POST['campaign_id'] ?? 0); check_admin_referer('wtfg_project_'.$campaign);
    if (!wtfg_valid_campaign($campaign)) { wp_die('Nieprawidłowa zbiórka.'); }
    $project=isset($_POST['project_id']) && is_string($_POST['project_id'])?trim(wp_unslash($_POST['project_id'])):'';
    if ($project!=='' && !preg_match('/^[0-9]{1,64}$/D',$project)) { wp_die('Nieprawidłowy ProjectID.'); }
    global $wpdb; $lock=''; $message='';
    try {
        $lock=wtfg_lock(); wtfg_check_project_binding($campaign,$project);
        if ($project!==wtfg_project_id($campaign)) {
            foreach (['customers','orders','products','order_items'] as $table) {
                if ($wpdb->get_var($wpdb->prepare('SELECT id FROM '.wtfg_table($table).' WHERE campaign_id=%d LIMIT 1',$campaign))) { throw new RuntimeException('Zbiórka ma dane. Nie można zmienić jej ProjectID.'); }
                wtfg_query_ok(true);
            }
            update_post_meta($campaign,'_wtfg_project_id',$project); wtfg_query_ok(true);
        }
    } catch (Throwable $error) { $message=$error instanceof RuntimeException?$error->getMessage():'Nie można zapisać konfiguracji.'; }
    finally { if ($lock) { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock)); } }
    if ($message) { wp_die(esc_html($message)); }
    wp_safe_redirect(add_query_arg(['page'=>'wtfg','configure'=>$campaign,'saved'=>1],admin_url('admin.php'))); exit;
});

add_action('admin_init',function () {
    if (!current_user_can('manage_options') || get_option('wtfg_schema')===WTFG_SCHEMA_VERSION) { return; }
    // Pierwsza instalacja może utworzyć puste tabele. Starszy model wymaga jawnej przebudowy.
    if (!get_option('wtfg_catalog_schema') && !get_option('wtfg_orders_schema')) { wtfg_install_schema(); }
});

add_action('admin_menu',function () {
    add_menu_page('WTF Gamefound','WTF Gamefound','manage_options','wtfg','wtfg_campaigns_page','dashicons-megaphone');
    foreach (['wtfg'=>['Zbiórki','wtfg_campaigns_page'],'wtfg-products'=>['Produkty','wtfg_products_page'],'wtfg-supporters'=>['Wspierający','wtfg_customers_page'],'wtfg-orders'=>['Zamówienia','wtfg_orders_page'],'wtfg-banner'=>['Baner','wtfg_banner_page']] as $slug=>$page) {
        add_submenu_page('wtfg',$page[0],$page[0],'manage_options',$slug,$page[1]);
    }
});

add_action('admin_enqueue_scripts',function () {
    if (current_user_can('manage_options') && str_starts_with((string)($_GET['page'] ?? ''),'wtfg')) {
        wp_enqueue_script('wtfg-admin',plugins_url('assets/admin.js',dirname(__DIR__).'/wtf_gamefound.php'),[],filemtime(__DIR__.'/../assets/admin.js'),true);
        wp_enqueue_style('wtfg-admin',plugins_url('assets/admin.css',dirname(__DIR__).'/wtf_gamefound.php'),[],filemtime(__DIR__.'/../assets/admin.css'));
    }
});
