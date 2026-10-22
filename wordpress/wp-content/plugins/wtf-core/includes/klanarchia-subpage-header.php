<?php
if (!defined('ABSPATH')) exit;

function wtf_core_render_klan_subpage_header(){
    if(!is_page() || get_page_template_slug()!=='klanarchia')return '';
    $slug=get_post_field('post_name',get_queried_object_id());if($slug==='klanarchia')return '';
    $label='Poznaj Klanarchię';$title=get_the_title(get_queried_object_id());
    if(str_starts_with($slug,'archetyp-')){$label='Poznaj archetyp';$title='';}
    elseif($slug==='quiz-klanarchia'){$label='Quiz Klanarchii';$title='Odkryj swój archetyp';}
    ob_start(); ?>
    <div class="klan-back-wrap">
        <nav class="klan-context-bar" aria-label="Nawigacja Klanarchii">
            <a class="klan-back-button" href="<?php echo esc_url(home_url('/klanarchia/')); ?>" aria-label="Wróć do Klanarchii">
                <img src="<?php echo esc_url(wtf_core_media('klanarchia-logo.webp')); ?>" alt="" width="96" height="60">
                <span><small>Świat Klanarchii</small><strong>Wróć do Klanarchii</strong></span>
            </a>
            <a class="klan-materials-button" href="http://localhost:8082/download/Klanarchia/">Materiały do gry</a>
            <span class="klan-mobile-context"><?php echo esc_html($label); ?></span>
            <div class="klan-context-location" aria-label="Bieżąca strona"><span><?php echo esc_html($label); ?></span><?php if($title!==''): ?><strong><?php echo esc_html($title); ?></strong><?php endif; ?></div>
        </nav>
    </div>
    <?php return ob_get_clean();
}

add_action('init',function(){
    foreach(['wtf/klan-subpage-header','wtf/klan-back'] as $name)register_block_type($name,['api_version'=>3,'render_callback'=>'wtf_core_render_klan_subpage_header']);
});
