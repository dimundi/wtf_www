<?php
if (!defined('ABSPATH')) { exit; }

add_action('admin_post_wtfg_select', function () {
    wtfg_require_admin(); check_admin_referer('wtfg_select');
    $id = absint($_POST['campaign_id'] ?? 0);
    if ($id && !wtfg_valid_campaign($id)) { wp_die('Nieprawidłowa zbiórka.'); }
    update_user_meta(get_current_user_id(), '_wtfg_selected_campaign', $id);
    $return=($_POST['return_page'] ?? '')==='wtfg' ? 'wtfg' : 'wtfg-products';
    wp_safe_redirect(admin_url('admin.php?page='.$return)); exit;
});

function wtfg_campaigns_page() {
    wtfg_require_admin();
    $configure=absint($_GET['configure'] ?? 0);
    if ($configure) {
        if (!wtfg_valid_campaign($configure)) { wp_die('Nieprawidłowa zbiórka.'); }
        echo '<div class="wrap"><h1>Konfiguracja: '.esc_html(get_the_title($configure)).' — ProjectID: '.esc_html(wtfg_project_id($configure) ?: 'nieustalone').'</h1>';
        echo '<p><a href="'.esc_url(admin_url('admin.php?page=wtfg')).'">Wróć do listy zbiórek</a></p>';
        if (isset($_GET['saved'])) { echo '<div class="notice notice-success inline"><p>Konfiguracja zapisana.</p></div>'; }
        wtfg_project_form($configure);
        echo '<p><a href="'.esc_url(get_edit_post_link($configure)).'">Edytuj nazwę lub status zbiórki</a></p></div>';
        return;
    }
    echo '<div class="wrap"><h1>Zbiórki</h1>';
    echo '<p>Zaznacz aktywną zbiórkę. Wybór jest zapamiętywany podczas przechodzenia między ekranami.</p>';
    echo '<p><a class="button" href="'.esc_url(admin_url('post-new.php?post_type=wtfg_campaign')).'">Dodaj zbiórkę</a></p>';
    $campaigns=get_posts(['post_type'=>'wtfg_campaign','post_status'=>['publish','draft','pending','private','future'],'numberposts'=>-1,'orderby'=>'title','order'=>'ASC']);
    $active=wtfg_selected_campaign();
    echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    wp_nonce_field('wtfg_select');
    echo '<input type="hidden" name="action" value="wtfg_select"><input type="hidden" name="return_page" value="wtfg"><table class="widefat striped"><thead><tr><th>Aktywna</th><th>Zbiórka</th><th>Ustawienia</th></tr></thead><tbody>';
    foreach ($campaigns as $campaign) {
        echo '<tr><td><input type="radio" name="campaign_id" id="wtfg-campaign-'.(int)$campaign->ID.'" value="'.(int)$campaign->ID.'" '.checked($active,$campaign->ID,false).' onchange="this.form.requestSubmit()"></td>';
        echo '<td><label for="wtfg-campaign-'.(int)$campaign->ID.'"><strong>'.esc_html($campaign->post_title).'</strong> — ProjectID: '.esc_html(wtfg_project_id($campaign->ID) ?: 'nieustalone').'</label></td>';
        echo '<td><a class="button" href="'.esc_url(add_query_arg('configure',$campaign->ID,admin_url('admin.php?page=wtfg'))).'">Konfiguruj</a></td></tr>';
    }
    if (!$campaigns) { echo '<tr><td colspan="3">Nie ma jeszcze zbiórek. Dodaj pierwszą zbiórkę.</td></tr>'; }
    echo '</tbody></table>';
    if ($campaigns) { echo '<p><button class="button">Ustaw aktywną zbiórkę</button></p>'; }
    echo '</form></div>';
}

function wtfg_banner_page() {
    $campaign=wtfg_page_start('Baner');
    if ($campaign) {
        echo '<p>Ustawienia dotyczą wybranej zbiórki. Strefa czasowa: '.esc_html(wp_timezone_string()).'.</p><form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
        wp_nonce_field('wtfg_banner_'.$campaign);
        wp_nonce_field('wtfg_save','wtfg_nonce');
        echo '<input type="hidden" name="action" value="wtfg_banner"><input type="hidden" name="campaign_id" value="'.(int)$campaign.'">';
        foreach (wtfg_fields() as $key=>$label) {
            $value=get_post_meta($campaign,'_wtfg_'.$key,true);
            echo '<p><label><strong>'.esc_html($label).'</strong><br>';
            if (str_ends_with($key,'_text')) {
                echo '<textarea class="large-text" rows="4" name="wtfg['.esc_attr($key).']">'.esc_textarea($value).'</textarea>';
            } else {
                $type=$key==='end'?'datetime-local':($key==='url'?'url':'text');
                echo '<input class="large-text" type="'.esc_attr($type).'" name="wtfg['.esc_attr($key).']" value="'.esc_attr($value).'">';
            }
            echo '</label></p>';
        }
        echo '<p>Osadzanie: <code>[wtf_gamefound id="'.(int)$campaign.'"]</code>. Pusty podpis ukrywa przycisk. Baner wyświetla się dla opublikowanej zbiórki.</p><button class="button button-primary">Zapisz baner</button></form>';
        echo '<p><a href="'.esc_url(get_edit_post_link($campaign)).'">Edytuj nazwę lub status zbiórki</a></p>';
    }
    echo '</div>';
}

add_action('admin_post_wtfg_banner', function () {
    wtfg_require_admin(); $campaign=absint($_POST['campaign_id'] ?? 0);
    check_admin_referer('wtfg_banner_'.$campaign);
    if (!wtfg_valid_campaign($campaign)) { wp_die('Nieprawidłowa zbiórka.'); }
    // Wykorzystujemy dotychczasowy zapis ustawień i jego osobną kontrolę nonce.
    $result=wp_update_post(['ID'=>$campaign],true);
    if (is_wp_error($result)) { wp_die('Nie można zapisać banera.'); }
    update_user_meta(get_current_user_id(),'_wtfg_selected_campaign',$campaign);
    wp_safe_redirect(admin_url('admin.php?page=wtfg-banner')); exit;
});

