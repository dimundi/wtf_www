<?php
if (!defined('ABSPATH')) { exit; }
function wtf_faq_questions_screen() {
    if (!current_user_can('edit_posts')) { return; }
    $game=isset($_GET['faq_game'])?absint($_GET['faq_game']):0;
    $group=isset($_GET['faq_group'])?absint($_GET['faq_group']):0;
    $tax=[];
    if ($game) { $tax[]=['taxonomy'=>'wtf_faq_game','field'=>'term_id','terms'=>$game]; }
    if ($group) { $tax[]=['taxonomy'=>'wtf_faq_group','field'=>'term_id','terms'=>$group]; }
    $args=['post_type'=>'wtf_faq','post_status'=>['publish','draft','pending','private','future'],'numberposts'=>-1,'orderby'=>['menu_order'=>'ASC','ID'=>'ASC'],'suppress_filters'=>true];
    if ($tax) { $args['tax_query']=$tax; }
    $questions=array_values(array_filter(get_posts($args),static fn($post)=>current_user_can('edit_post',$post->ID)));
    $tree=[];
    foreach ($questions as $question) {
        $games=wp_get_object_terms($question->ID,'wtf_faq_game');$groups=wp_get_object_terms($question->ID,'wtf_faq_group');
        $gameTerm=!is_wp_error($games)&&$games?$games[0]:null;
        $groupTerm=!is_wp_error($groups)&&$groups?$groups[0]:null;
        $gameId=$gameTerm?$gameTerm->term_id:0;$groupId=$groupTerm?$groupTerm->term_id:0;
        $tree[$gameId]['name']=$gameTerm?$gameTerm->name:'Bez przypisanej gry';
        $tree[$gameId]['groups'][$groupId]['name']=$groupTerm?$groupTerm->name:'Bez grupy';
        $tree[$gameId]['groups'][$groupId]['questions'][]=$question;
    }
    uasort($tree,static fn($a,$b)=>strnatcasecmp($a['name'],$b['name']));
    $counts=wtf_faq_vote_counts(wp_list_pluck($questions,'ID'));
    $chevron='<svg aria-hidden="true" viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m4 6 4 4 4-4"/></svg>';
    ?><div class="wrap"><h1 class="wp-heading-inline">Pytania FAQ</h1><a class="page-title-action" href="<?php echo esc_url(admin_url('post-new.php?post_type=wtf_faq')); ?>">Dodaj pytanie</a><hr class="wp-header-end">
    <form method="get" class="wtf-faq-admin-filters"><input type="hidden" name="page" value="wtf_faq_questions">
    <label>Gra <?php wp_dropdown_categories(['taxonomy'=>'wtf_faq_game','hide_empty'=>false,'name'=>'faq_game','selected'=>$game,'show_option_all'=>'Wszystkie gry','orderby'=>'name']); ?></label>
    <label>Grupa <?php wp_dropdown_categories(['taxonomy'=>'wtf_faq_group','hide_empty'=>false,'name'=>'faq_group','selected'=>$group,'show_option_all'=>'Wszystkie grupy','orderby'=>'name']); ?></label>
    <button type="submit" class="button">Filtruj</button><?php if ($game||$group): ?><a class="button" href="<?php echo esc_url(admin_url('admin.php?page=wtf_faq_questions')); ?>">Wyczyść filtry</a><?php endif; ?></form>
    <div class="wtf-faq-admin-tree">
    <?php if (!$questions): ?><p>Brak pytań.</p><?php endif; ?>
    <?php foreach ($tree as $gameId=>$gameEntry):
        $gameCount=array_sum(array_map(static fn($entry)=>count($entry['questions']),$gameEntry['groups']));
        uasort($gameEntry['groups'],static fn($a,$b)=>strnatcasecmp($a['name'],$b['name'])); ?>
    <details class="wtf-faq-admin-game" data-game-id="<?php echo (int)$gameId; ?>" <?php if ($game||$group) { echo 'open'; } ?>>
    <summary><?php echo $chevron; ?><span><?php echo esc_html($gameEntry['name']); ?></span><span class="wtf-faq-admin-count"><?php echo (int)$gameCount; ?></span></summary>
    <div class="wtf-faq-admin-groups">
    <?php foreach ($gameEntry['groups'] as $groupId=>$groupEntry): ?>
    <details class="wtf-faq-admin-group" data-group-id="<?php echo (int)$groupId; ?>" <?php if ($group) { echo 'open'; } ?>>
    <summary><?php echo $chevron; ?><span><?php echo esc_html($groupEntry['name']); ?></span><span class="wtf-faq-admin-count"><?php echo count($groupEntry['questions']); ?></span></summary>
    <p class="wtf-faq-admin-add-question"><a class="button" href="<?php echo esc_url(add_query_arg(['post_type'=>'wtf_faq','faq_game'=>$gameId,'faq_group'=>$groupId],admin_url('post-new.php'))); ?>">Dodaj pytanie</a></p>
    <ul class="wtf-faq-admin-questions">
    <?php foreach ($groupEntry['questions'] as $question): $status=get_post_status_object($question->post_status); ?>
    <li data-question-id="<?php echo (int)$question->ID; ?>"><div><a href="<?php echo esc_url(get_edit_post_link($question->ID,'raw')); ?>"><?php echo esc_html($question->post_title?:'(Bez tytułu)'); ?></a><?php if ($question->post_status!=='publish'): ?> <span class="wtf-faq-admin-status"><?php echo esc_html($status?$status->label:$question->post_status); ?></span><?php endif; ?></div><span class="wtf-faq-admin-votes" aria-label="Głosy pomocne i niepomocne">👍 <?php echo (int)$counts[$question->ID]['up']; ?> &nbsp; 👎 <?php echo (int)$counts[$question->ID]['down']; ?></span></li>
    <?php endforeach; ?></ul></details><?php endforeach; ?></div></details><?php endforeach; ?></div></div><?php
}

// Standardowe odnośniki WP do listy FAQ również otwierają widok hierarchiczny.
add_action('load-edit.php',function () {
    if (($_GET['post_type']??'')!=='wtf_faq' || !current_user_can('edit_posts') || isset($_GET['post_status'])) { return; }
    foreach (['action','action2'] as $key) { if (!empty($_REQUEST[$key]) && $_REQUEST[$key]!=='-1') { return; } }
    $args=['page'=>'wtf_faq_questions'];
    foreach (['wtf_faq_group'=>'faq_group','wtf_faq_game'=>'faq_game'] as $taxonomy=>$filter) {
        if (!empty($_GET[$taxonomy]) && is_string($_GET[$taxonomy])) {
            $term=get_term_by('slug',sanitize_title(wp_unslash($_GET[$taxonomy])),$taxonomy);
            if ($term) { $args[$filter]=$term->term_id; }
        }
    }
    wp_safe_redirect(add_query_arg($args,admin_url('admin.php')));exit;
});
