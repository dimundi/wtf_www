<?php
/**
 * Plugin Name: wtf_gamefound
 * Description: Wspólne komunikaty kampanii Gamefound przed i po zakończeniu zbiórki.
 * Version: 0.1.0
 * Requires PHP: 8.0
 */
if (!defined('ABSPATH')) { exit; }

add_action('init', function () {
    register_post_type('wtfg_campaign', [
        'labels'=>['name'=>'WTF Gamefound','singular_name'=>'Kampania','add_new_item'=>'Dodaj kampanię','edit_item'=>'Edytuj kampanię'],
        'public'=>false,'show_ui'=>true,'menu_icon'=>'dashicons-megaphone','supports'=>['title'],
        'capabilities'=>['edit_posts'=>'manage_options','edit_others_posts'=>'manage_options','publish_posts'=>'manage_options','read_private_posts'=>'manage_options','delete_posts'=>'manage_options','delete_others_posts'=>'manage_options','edit_published_posts'=>'manage_options','delete_published_posts'=>'manage_options'],
        'map_meta_cap'=>true,
    ]);
    wp_register_style('wtf_gamefound', plugins_url('assets/campaign.css', __FILE__), [], filemtime(__DIR__.'/assets/campaign.css'));
    wp_register_script('wtf_gamefound', plugins_url('assets/campaign.js', __FILE__), [], filemtime(__DIR__.'/assets/campaign.js'), true);
});

function wtfg_fields() {
    return ['url'=>'Adres kampanii Gamefound','end'=>'Koniec zbiórki (czas witryny)', 'active_title'=>'Nagłówek podczas zbiórki','active_text'=>'Treść podczas zbiórki','active_button'=>'Przycisk podczas zbiórki','ended_title'=>'Nagłówek po zakończeniu','ended_text'=>'Treść po zakończeniu','ended_button'=>'Przycisk po zakończeniu'];
}
add_action('add_meta_boxes', function () {
    add_meta_box('wtfg-settings','Komunikat kampanii',function ($post) {
        wp_nonce_field('wtfg_save','wtfg_nonce');
        echo '<p>Ustawienia wspólne dla wszystkich wystąpień. Strefa czasowa: <strong>'.esc_html(wp_timezone_string()).'</strong>.</p>';
        foreach (wtfg_fields() as $key=>$label) {
            $value=get_post_meta($post->ID,'_wtfg_'.$key,true);
            echo '<p><label for="wtfg-'.esc_attr($key).'"><strong>'.esc_html($label).'</strong></label><br>';
            if (str_ends_with($key,'_text')) {
                echo '<textarea class="widefat" rows="4" id="wtfg-'.esc_attr($key).'" name="wtfg['.esc_attr($key).']">'.esc_textarea($value).'</textarea>';
            } else {
                $type=$key==='end'?'datetime-local':($key==='url'?'url':'text');
                echo '<input class="widefat" type="'.$type.'" id="wtfg-'.esc_attr($key).'" name="wtfg['.esc_attr($key).']" value="'.esc_attr($value).'">';
            }
            echo '</p>';
        }
        echo '<p>Osadź blok „Krótki kod” w dowolnym miejscu: <code>[wtf_gamefound id="'.(int)$post->ID.'"]</code>. Pusty podpis przycisku ukrywa przycisk. Treść: zwykły tekst i akapity.</p>';
    },'wtfg_campaign');
});
add_action('save_post_wtfg_campaign',function ($id) {
    if (wp_is_post_revision($id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('manage_options') || !isset($_POST['wtfg_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtfg_nonce'])),'wtfg_save')) { return; }
    $values=isset($_POST['wtfg']) && is_array($_POST['wtfg'])?wp_unslash($_POST['wtfg']):[];
    foreach (wtfg_fields() as $key=>$label) {
        $value=isset($values[$key]) && is_string($values[$key])?$values[$key]:'';
        if ($key==='url') { $value=esc_url_raw($value,['https']); }
        elseif ($key==='end') {
            $date=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$value,wp_timezone());
            $value=$date && $date->format('Y-m-d\TH:i')===$value?$value:'';
        } else { $value=str_ends_with($key,'_text')?sanitize_textarea_field($value):sanitize_text_field($value); }
        update_post_meta($id,'_wtfg_'.$key,$value);
    }
});

function wtfg_end_timestamp($id) {
    $value=get_post_meta($id,'_wtfg_end',true);
    $date=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$value,wp_timezone());
    return $date && $date->format('Y-m-d\TH:i')===$value?$date->getTimestamp():0;
}
add_shortcode('wtf_gamefound',function ($attributes) {
    $a=shortcode_atts(['id'=>0],$attributes,'wtf_gamefound');$id=absint($a['id']);
    if (get_post_type($id)!=='wtfg_campaign' || get_post_status($id)!=='publish') { return ''; }
    $end=wtfg_end_timestamp($id);$ended=$end && time()>=$end;
    wp_enqueue_style('wtf_gamefound');wp_enqueue_script('wtf_gamefound');
    ob_start(); ?>
    <section class="wtf-gamefound" data-wtfg-end="<?php echo (int)$end; ?>">
    <img class="wtf-gamefound__logo" src="<?php echo esc_url(plugins_url('assets/gamefound-logo.webp',__FILE__)); ?>" alt="Gamefound" width="320" height="77" decoding="async">
    <?php foreach (['active','ended'] as $state): ?>
        <div data-wtfg-state="<?php echo esc_attr($state); ?>" <?php echo (($state==='ended')!== (bool)$ended)?'hidden':''; ?>>
            <?php $title=get_post_meta($id,'_wtfg_'.$state.'_title',true);if ($title): ?><h2><?php echo esc_html($title); ?></h2><?php endif; ?>
            <div class="wtf-gamefound__text"><?php echo wpautop(esc_html(get_post_meta($id,'_wtfg_'.$state.'_text',true))); ?></div>
            <?php if ($state==='active' && $end): ?><p class="wtf-gamefound__countdown" data-wtfg-countdown hidden></p><?php endif; ?>
            <?php $label=get_post_meta($id,'_wtfg_'.$state.'_button',true);$url=get_post_meta($id,'_wtfg_url',true);if ($label && $url): ?>
                <a class="wtf-gamefound__button" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?></section>
    <?php return ob_get_clean();
});
