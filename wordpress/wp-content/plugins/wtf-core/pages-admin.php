<?php
if (!defined('ABSPATH')) { exit; }

function wtf_core_page_families() {
    return ['general'=>'Strony ogólne','elekt'=>'Elekt','kurzolaki'=>'Kurzołaki','tsukuyumi'=>'Tsukuyumi','klanarchia'=>'Klanarchia','publications'=>'Publikacje'];
}

function wtf_core_page_family($post) {
    $families=wtf_core_page_families();
    $chain=array_merge([$post->ID],get_post_ancestors($post));
    foreach($chain as $id) {
        $manual=get_post_meta($id,'_wtf_page_family',true);
        if(isset($families[$manual]))return $manual;
    }
    foreach($chain as $id) {
        foreach(['elekt'=>'_wtf_ele_kind','kurzolaki'=>'_wtf_kurz_kind','tsukuyumi'=>'_wtf_tsu_kind','publications'=>'_wtf_echo_kind'] as $family=>$meta)if(get_post_meta($id,$meta,true))return $family;
        $ancestor=get_post($id);if(!$ancestor)continue;
        foreach(['elekt','kurzolaki','tsukuyumi','klanarchia'] as $family)if(preg_match('/^'.preg_quote($family,'/').'(?:-|$)/',$ancestor->post_name))return $family;
        if(str_starts_with($ancestor->post_name,'echo-swiatow'))return 'publications';
    }
    return 'general';
}

add_action('add_meta_boxes_page',function(){add_meta_box('wtf-page-family','Grupa w Strony PL/EN','wtf_core_page_family_box','page','side','default');});
function wtf_core_page_family_box($post) {
    $manual=get_post_meta($post->ID,'_wtf_page_family',true);
    if($post->post_status==='auto-draft'&&isset($_GET['wtf_page_family']))$manual=sanitize_key(wp_unslash($_GET['wtf_page_family']));
    wp_nonce_field('wtf-page-family','wtf_page_family_nonce');
    echo '<label class="screen-reader-text" for="wtf-page-family-select">Grupa stron</label><select id="wtf-page-family-select" name="wtf_page_family" style="width:100%"><option value="">Automatycznie</option>';
    foreach(wtf_core_page_families() as $key=>$label)echo '<option value="'.esc_attr($key).'" '.selected($manual,$key,false).'>'.esc_html($label).'</option>';
    echo '</select><p>Grupa jest wspólna dla wersji PL/EN. Nowe podstrony mogą dziedziczyć grupę strony nadrzędnej.</p>';
}
add_action('save_post_page',function($id){
    if(wp_is_post_revision($id)||wp_is_post_autosave($id)||!current_user_can('edit_post',$id))return;
    if(!isset($_POST['wtf_page_family_nonce'],$_POST['wtf_page_family'])||!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtf_page_family_nonce'])),'wtf-page-family'))return;
    $family=sanitize_key(wp_unslash($_POST['wtf_page_family']));if($family!==''&&!isset(wtf_core_page_families()[$family]))return;
    $ids=[$id];if(function_exists('pll_get_post_translations'))$ids=array_merge($ids,array_values(pll_get_post_translations($id)));
    foreach(array_unique($ids) as $version){if(!current_user_can('edit_post',$version))continue;if($family==='')delete_post_meta($version,'_wtf_page_family');else update_post_meta($version,'_wtf_page_family',$family);}
});

add_action('admin_enqueue_scripts',function($hook){
    if($hook!=='toplevel_page_wtf-pages')return;
    wp_enqueue_style('wtf-pages-admin',plugins_url('pages-admin.css',__FILE__),[],filemtime(__DIR__.'/pages-admin.css'));
});

function wtf_core_pages_screen() {
    if(!current_user_can('edit_pages'))return;
    if(!function_exists('pll_get_post_translations')){echo '<div class="wrap"><h1>Strony PL/EN</h1><p>Włącz Polylang, aby wyświetlić powiązania tłumaczeń.</p></div>';return;}
    $families=wtf_core_page_families();
    $family=isset($_GET['family'])?sanitize_key(wp_unslash($_GET['family'])):'';if(!isset($families[$family]))$family='';
    $search=isset($_GET['s'])?sanitize_text_field(wp_unslash($_GET['s'])):'';
    $tree=array_fill_keys(array_keys($families),[]);$total=0;
    foreach(wtf_core_page_groups() as $group){
        $key=wtf_core_page_family($group['primary']);if($family!==''&&$family!==$key)continue;
        if($search!==''){
            $matched=false;foreach(array_merge([$group['primary']],array_values($group['versions'])) as $post)if(mb_stripos($post->post_title,$search)!==false){$matched=true;break;}
            if(!$matched)continue;
        }
        $tree[$key][]=$group;$total++;
    }
    foreach($tree as &$rows)usort($rows,static function($a,$b){$am=str_starts_with((string)get_post_meta($a['primary']->ID,'_wtf_ele_key',true),'main:')||str_starts_with((string)get_post_meta($a['primary']->ID,'_wtf_tsu_key',true),'main:')||str_starts_with((string)get_post_meta($a['primary']->ID,'_wtf_kurz_key',true),'main:');$bm=str_starts_with((string)get_post_meta($b['primary']->ID,'_wtf_ele_key',true),'main:')||str_starts_with((string)get_post_meta($b['primary']->ID,'_wtf_tsu_key',true),'main:')||str_starts_with((string)get_post_meta($b['primary']->ID,'_wtf_kurz_key',true),'main:');return ($am?0:1)<=>($bm?0:1)?:strnatcasecmp($a['title'],$b['title']);});unset($rows);
    ?>
    <div class="wrap"><h1 class="wp-heading-inline">Strony PL/EN</h1> <a class="page-title-action" href="<?php echo esc_url(admin_url('post-new.php?post_type=page')); ?>">Dodaj stronę</a><hr class="wp-header-end">
    <form method="get" class="wtf-pages-filters"><input type="hidden" name="page" value="wtf-pages">
    <label for="wtf-family-filter">Grupa</label><select id="wtf-family-filter" name="family"><option value="">Wszystkie grupy</option><?php foreach($families as $key=>$label): ?><option value="<?php echo esc_attr($key); ?>" <?php selected($family,$key); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select>
    <label class="screen-reader-text" for="wtf-search">Szukaj stron</label><input id="wtf-search" type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Szukaj stron PL/EN">
    <button class="button" type="submit">Filtruj</button><?php if($family||$search): ?><a class="button" href="<?php echo esc_url(admin_url('admin.php?page=wtf-pages')); ?>">Wyczyść filtry</a><?php endif; ?></form>
    <p><?php echo (int)$total; ?> par lub stron. Jeden wiersz łączy wersje PL/EN.</p>
    <?php if(!$total&&($family||$search)): ?><p>Nie znaleziono stron.</p><?php endif; ?>
    <div class="wtf-pages-tree">
    <?php foreach($families as $key=>$label):$rows=$tree[$key];if(($family&&$family!==$key)||($search&&!$rows))continue; ?>
    <details class="wtf-pages-family" data-family="<?php echo esc_attr($key); ?>" <?php if($family||$search)echo 'open'; ?>>
    <summary><span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span><span><?php echo esc_html($label); ?></span><span class="wtf-pages-count"><?php echo count($rows); ?></span></summary>
    <div class="wtf-pages-family-content"><p><a class="button" href="<?php echo esc_url(add_query_arg(['post_type'=>'page','wtf_page_family'=>$key],admin_url('post-new.php'))); ?>">Dodaj stronę</a></p>
    <?php if(!$rows): ?><p>Brak stron w tej grupie.</p><?php else: ?>
    <table class="wp-list-table widefat striped wtf-page-groups"><thead><tr><th scope="col">Strona</th><th scope="col">PL</th><th scope="col">EN</th></tr></thead><tbody>
    <?php foreach($rows as $group): ?><tr data-page-id="<?php echo (int)$group['primary']->ID; ?>"><th scope="row"><strong><?php echo esc_html($group['title']); ?></strong>
    <?php if(!$group['versions']): ?><p>Nie przypisano języka. <a href="<?php echo esc_url(get_edit_post_link($group['primary']->ID)); ?>">Edytuj i przypisz język</a></p><?php endif; ?></th>
    <?php foreach(['pl'=>'PL','en'=>'EN'] as $lang=>$language): ?><td>
    <?php if(isset($group['versions'][$lang])):$version=$group['versions'][$lang];$status=get_post_status_object($version->post_status); ?>
    <a href="<?php echo esc_url(get_edit_post_link($version->ID)); ?>" aria-label="<?php echo esc_attr('Edytuj '.$language.': '.$version->post_title); ?>"><strong><?php echo esc_html($version->post_title?:'(bez tytułu)'); ?></strong></a><p><?php echo esc_html($status?$status->label:$version->post_status); ?> · <a href="<?php echo esc_url(get_edit_post_link($version->ID)); ?>">Edytuj</a></p>
    <?php elseif($group['versions']): ?><a class="button button-small" href="<?php echo esc_url(wp_nonce_url(add_query_arg(['post_type'=>'page','from_post'=>$group['primary']->ID,'new_lang'=>$lang],admin_url('post-new.php')),'new-post-translation')); ?>">Dodaj tłumaczenie <?php echo esc_html($language); ?></a><?php else: ?>—<?php endif; ?>
    </td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table><?php endif; ?></div></details><?php endforeach; ?></div>
    <p><a href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>">Standardowa lista stron (akcje zbiorcze i kosz)</a></p></div>
    <?php
}
