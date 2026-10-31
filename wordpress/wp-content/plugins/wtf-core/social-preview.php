<?php
if (!defined('ABSPATH')) exit;

add_action('admin_init', function() {
    foreach (['wtf_social_default_image','wtf_social_temporary_image'] as $option) register_setting('general', $option, ['type' => 'integer', 'sanitize_callback' => function($value) use ($option) {
        $id = absint($value);
        return !$id || in_array(get_post_mime_type($id), ['image/jpeg', 'image/png', 'image/webp'], true) ? $id : (int)get_option($option, 0);
    }, 'default' => 0]);
    register_setting('general', 'wtf_social_use_temporary', ['type' => 'boolean', 'sanitize_callback' => 'rest_sanitize_boolean', 'default' => false]);
    add_settings_field('wtf_social_default_image', 'Grafiki społecznościowe wydawnictwa', function() {
        echo '<div id="wtf-social-publisher">';
        foreach (['wtf_social_default_image' => 'Stała grafika wydawnictwa', 'wtf_social_temporary_image' => 'Grafika tymczasowa, np. zaproszenie na targi'] as $option => $label) {
            $id = (int)get_option($option, 0);
            echo '<div data-social-image-picker><p><strong>'.esc_html($label).'</strong></p><input type="hidden" name="'.esc_attr($option).'" value="'.esc_attr($id).'"><button type="button" class="button" data-select>Wybierz grafikę</button> <button type="button" class="button-link" data-remove>Usuń</button><p><img alt="" style="max-width:300px;height:auto" src="'.esc_url($id ? wp_get_attachment_image_url($id, 'full') : '').'"'.($id ? '' : ' hidden').'></p></div>';
        }
        echo '<p><input type="hidden" name="wtf_social_use_temporary" value="0"><label><input type="checkbox" id="wtf-social-use-temporary" name="wtf_social_use_temporary" value="1" '.checked(get_option('wtf_social_use_temporary', false), true, false).'> Używaj grafiki tymczasowej</label></p><p id="wtf-social-active" role="status"></p><p class="description">Zapisz zmiany, aby przełączyć grafikę. Po akcji wyłącz przełącznik — wróci stała grafika bez ponownego wybierania pliku. Własne grafiki stron i obrazki wyróżniające mają pierwszeństwo. Gdy brak grafiki tymczasowej, używana jest stała. Zalecane 1200 × 630 px (1,91:1), JPG, PNG lub WebP.</p><p class="description">Facebook przechowuje kopię podglądu; po zmianie może być potrzebne jego odświeżenie.</p></div>';
    }, 'general');
});

function wtf_social_default_image_id() {
    $temporary = (int)get_option('wtf_social_temporary_image', 0);
    if (get_option('wtf_social_use_temporary', false) && $temporary && wp_get_attachment_image_src($temporary, 'full')) return $temporary;
    return (int)get_option('wtf_social_default_image', 0);
}

function wtf_social_data($id, $fallbacks = false) {
    $title = get_post_meta($id, '_wtf_seo_title', true) ?: get_the_title($id);
    $description = get_post_meta($id, '_wtf_seo_description', true) ?: wp_trim_words(wp_strip_all_tags(strip_shortcodes(get_post_field('post_excerpt', $id))), 35, '…');
    $image = get_post_thumbnail_id($id) ?: wtf_social_default_image_id();
    if (!$fallbacks) {
        $title = get_post_meta($id, '_wtf_social_title', true) ?: $title;
        $description = get_post_meta($id, '_wtf_social_description', true) ?: $description;
        $image = (int)get_post_meta($id, '_wtf_social_image', true) ?: $image;
    }
    $src = $image ? wp_get_attachment_image_src($image, 'full') : false;
    return ['title' => html_entity_decode(wp_strip_all_tags($title), ENT_QUOTES, 'UTF-8'), 'description' => $description, 'image' => $src ? $src[0] : '', 'width' => $src ? $src[1] : 0, 'height' => $src ? $src[2] : 0, 'url' => get_permalink($id)];
}

function wtf_social_head($id) {
    $data = wtf_social_data($id);
    $tags = ['og:type' => 'website', 'og:site_name' => 'What The Frog', 'og:locale' => str_replace('-', '_', get_bloginfo('language')), 'og:title' => $data['title'], 'og:description' => $data['description'], 'og:url' => $data['url'], 'og:image' => $data['image']];
    if ($data['image']) {
        $tags['og:image:width'] = $data['width'];
        $tags['og:image:height'] = $data['height'];
    }
    foreach ($tags as $key => $value) if ($value !== '') echo '<meta property="'.esc_attr($key).'" content="'.esc_attr($value).'">'."\n";
}

add_action('add_meta_boxes', function() {
    foreach (['page', 'product'] as $type) add_meta_box('wtf-social-settings', 'Podgląd w mediach społecznościowych', function($post) {
        wp_nonce_field('wtf_social_save', 'wtf_social_nonce');
        $fallback = wtf_social_data($post->ID, true);
        $current = wtf_social_data($post->ID);
        echo '<div id="wtf-social-editor" data-fallback="'.esc_attr(wp_json_encode($fallback)).'">';
        echo '<p>Puste pola korzystają z SEO, obrazka wyróżniającego oraz domyślnej grafiki wydawnictwa. Podgląd jest orientacyjny — Facebook może inaczej kadrować grafikę.</p>';
        foreach (['title' => 'Tytuł', 'description' => 'Opis'] as $key => $label) {
            echo '<p><label for="wtf-social-'.$key.'">'.esc_html($label).'</label><textarea class="widefat" rows="'.($key === 'title' ? '2' : '3').'" id="wtf-social-'.$key.'" name="wtf_social_'.$key.'" placeholder="'.esc_attr($fallback[$key]).'">'.esc_textarea(get_post_meta($post->ID, '_wtf_social_'.$key, true)).'</textarea></p>';
        }
        echo '<input type="hidden" id="wtf-social-image" name="wtf_social_image" value="'.esc_attr(get_post_meta($post->ID, '_wtf_social_image', true)).'">';
        echo '<p><button type="button" class="button" id="wtf-social-select">Wybierz grafikę</button> <button type="button" class="button-link" id="wtf-social-remove">Użyj domyślnej</button></p><p>Zalecane 1200 × 630 px, proporcje 1,91:1. Podgląd przycina brzegi; ważne elementy umieść bliżej środka. Wybierz JPG, PNG lub WebP.</p>';
        echo '<div class="wtf-social-card"><img id="wtf-social-preview-image" src="'.esc_url($current['image']).'" alt=""'.($current['image'] ? '' : ' hidden').'><div class="wtf-social-card-copy"><small>'.esc_html(wp_parse_url(home_url(), PHP_URL_HOST)).'</small><strong id="wtf-social-preview-title">'.esc_html($current['title']).'</strong><span id="wtf-social-preview-description">'.esc_html($current['description']).'</span></div></div></div>';
    }, $type, 'normal', 'default');
});

add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'options-general.php' && (!in_array($hook, ['post.php', 'post-new.php'], true) || !in_array(get_current_screen()->post_type ?? '', ['page', 'product'], true))) return;
    wp_enqueue_media();
    wp_enqueue_script('wtf-social-preview', plugins_url('social-preview.js', __FILE__), ['jquery', 'media-views'], filemtime(__DIR__.'/social-preview.js'), true);
    wp_enqueue_style('wtf-social-preview', plugins_url('social-preview.css', __FILE__), [], filemtime(__DIR__.'/social-preview.css'));
});

add_action('save_post', function($id) {
    if (!in_array(get_post_type($id), ['page', 'product'], true) || wp_is_post_revision($id) || wp_is_post_autosave($id) || !current_user_can('edit_post', $id) || !isset($_POST['wtf_social_nonce']) || !is_string($_POST['wtf_social_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtf_social_nonce'])), 'wtf_social_save')) return;
    foreach (['title', 'description', 'image'] as $key) {
        if (!isset($_POST['wtf_social_'.$key]) || !is_string($_POST['wtf_social_'.$key])) continue;
        $raw = wp_unslash($_POST['wtf_social_'.$key]);
        $value = $key === 'image' ? absint($raw) : sanitize_text_field($raw);
        if ($key === 'image' && $value && !in_array(get_post_mime_type($value), ['image/jpeg', 'image/png', 'image/webp'], true)) continue;
        if ($value) update_post_meta($id, '_wtf_social_'.$key, $value); else delete_post_meta($id, '_wtf_social_'.$key);
    }
});
