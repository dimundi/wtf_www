<?php
if (!defined('ABSPATH')) exit;

// The public sitemap contains offer/content pages, not checkout/account utilities.
add_filter('wp_sitemaps_posts_query_args',function($args,$post_type){
    if($post_type!=='page')return $args;
    $excluded=[];
    foreach(['cart','checkout','myaccount'] as $name){$id=(int)get_option('woocommerce_'.$name.'_page_id');if($id>0)$excluded[]=$id;}
    foreach(['logowanie','rejestracja','klanarchia/edytor'] as $slug){$page=get_page_by_path($slug);if($page)$excluded[]=$page->ID;}
    $args['post__not_in']=array_values(array_unique(array_merge($args['post__not_in']??[],$excluded)));
    return $args;
},10,2);
// Current catalogue lives on custom game/shop pages; legacy taxonomy archives add no content.
add_filter('wp_sitemaps_taxonomies','__return_empty_array');
add_filter('wp_sitemaps_post_types',function($types){return array_intersect_key($types,array_flip(['page','product']));});
add_filter('wp_sitemaps_add_provider',function($provider,$name){return $name==='users'?false:$provider;},10,2);

add_action('admin_init',function(){
    register_setting('general','wtf_google_site_verification',[
        'type'=>'string','default'=>'','sanitize_callback'=>function($value){
            $value=trim((string)$value);
            if($value!=='' && !preg_match('/^[a-zA-Z0-9_-]{10,200}$/D',$value)){
                add_settings_error('wtf_google_site_verification','invalid','Wklej samą wartość content z tagu Google, bez kodu HTML.');
                return get_option('wtf_google_site_verification','');
            }
            return $value;
        },
    ]);
    add_settings_field('wtf_google_site_verification','Google Search Console',function(){
        echo '<input class="regular-text" name="wtf_google_site_verification" value="'.esc_attr(get_option('wtf_google_site_verification','')).'"><p class="description">Wartość content z tagu google-site-verification. Puste pole wyłącza tag weryfikacyjny.</p>';
    },'general');
});
add_action('wp_head',function(){
    $token=get_option('wtf_google_site_verification','');
    if(preg_match('/^[a-zA-Z0-9_-]{10,200}$/D',(string)$token))echo '<meta name="google-site-verification" content="'.esc_attr($token).'">'."\n";
},1);

// Editorial SEO stays with the page/product, independently of visible headings.
add_action('add_meta_boxes',function(){
    foreach(['page','product'] as $type)add_meta_box('wtf-seo','SEO — tytuł i opis w Google',function($post){
        wp_nonce_field('wtf_seo_save','wtf_seo_nonce');
        echo '<p>Tytuł i opis dotyczą wyników wyszukiwania. Nie zmieniają nagłówka na stronie. Google może wybrać inny tekst.</p>';
        echo '<p><label for="wtf-seo-title">Pełny tytuł SEO</label><input class="widefat" id="wtf-seo-title" name="wtf_seo_title" value="'.esc_attr(get_post_meta($post->ID,'_wtf_seo_title',true)).'"></p>';
        echo '<p><label for="wtf-seo-description">Opis SEO</label><textarea class="widefat" rows="3" id="wtf-seo-description" name="wtf_seo_description">'.esc_textarea(get_post_meta($post->ID,'_wtf_seo_description',true)).'</textarea></p>';
        echo '<p>Puste pola zachowują standardowy tytuł WordPressa i nie dodają opisu. Każda strona powinna mieć własny opis.</p>';
    },$type,'normal','default');
});
add_action('save_post',function($id){
    if(!in_array(get_post_type($id),['page','product'],true) || wp_is_post_revision($id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post',$id) || !isset($_POST['wtf_seo_nonce']) || !is_string($_POST['wtf_seo_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtf_seo_nonce'])),'wtf_seo_save'))return;
    foreach(['title','description'] as $field){
        if(!isset($_POST['wtf_seo_'.$field]) || !is_string($_POST['wtf_seo_'.$field]))continue;
        $value=sanitize_text_field(wp_unslash($_POST['wtf_seo_'.$field]));
        if($value==='')delete_post_meta($id,'_wtf_seo_'.$field);else update_post_meta($id,'_wtf_seo_'.$field,$value);
    }
});
function wtf_seo_public_id(){
    $shop=function_exists('is_shop') && is_shop();
    if((!is_singular(['page','product']) && !$shop) || is_preview() || is_feed())return 0;
    $id=$shop?(int)get_option('woocommerce_shop_page_id'):get_queried_object_id();
    if(get_post_status($id)!=='publish' || post_password_required($id))return 0;
    if(function_exists('pll_get_post_language') && pll_get_post_language($id) && pll_get_post_language($id)!=='pl')return 0;
    if((function_exists('is_account_page') && is_account_page()) || (function_exists('is_cart') && is_cart()) || (function_exists('is_checkout') && is_checkout()))return 0;
    return $id;
}
add_filter('pre_get_document_title',function($title){
    $id=wtf_seo_public_id();$custom=$id?get_post_meta($id,'_wtf_seo_title',true):'';
    return $custom!==''?$custom:$title;
});
add_action('wp_head',function(){
    $id=wtf_seo_public_id();if(!$id)return;
    if(function_exists('is_shop') && is_shop() && !is_singular())echo '<link rel="canonical" href="'.esc_url(get_permalink($id)).'">'."\n";
    $title=get_post_meta($id,'_wtf_seo_title',true);$description=get_post_meta($id,'_wtf_seo_description',true);
    if($description!=='')echo '<meta name="description" content="'.esc_attr($description).'">'."\n";
    if($title!=='' || $description!==''){
        echo '<meta property="og:site_name" content="What The Frog">'."\n";
        if($title!=='')echo '<meta property="og:title" content="'.esc_attr($title).'">'."\n";
        if($description!=='')echo '<meta property="og:description" content="'.esc_attr($description).'">'."\n";
        echo '<meta property="og:url" content="'.esc_url(get_permalink($id)).'">'."\n";
    }
    if(!is_front_page())return;
    $home=home_url('/');
    $graph=['@context'=>'https://schema.org','@graph'=>[
        ['@type'=>'Organization','@id'=>$home.'#organization','name'=>'What The Frog','legalName'=>'Dimundi sp. z o.o.','url'=>$home,'logo'=>home_url('/wp-content/uploads/media/wtf-logo-navy-green.svg')],
        ['@type'=>'WebSite','@id'=>$home.'#website','url'=>$home,'name'=>'What The Frog','alternateName'=>'WTF','inLanguage'=>'pl-PL','publisher'=>['@id'=>$home.'#organization']],
    ]];
    echo '<script type="application/ld+json">'.wp_json_encode($graph,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).'</script>'."\n";
},5);
