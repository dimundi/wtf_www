<?php
if (!defined('ABSPATH')) exit;
add_action('admin_menu',function(){
    add_menu_page('WTF Quiz','WTF Quiz','manage_options','wtf_quiz',function(){wp_safe_redirect(admin_url('edit.php?post_type=wtfq_quiz'));exit;},'dashicons-editor-help',27);
});
add_action('load-toplevel_page_wtf_quiz',function(){wp_safe_redirect(admin_url('edit.php?post_type=wtfq_quiz'));exit;});

function wtfq_admin_quiz_id($post) {
    $id=(int)get_post_meta($post->ID,'_wtfq_quiz',true);
    if (!$id && $post->post_status==='auto-draft') $id=absint($_GET['quiz']??0);
    return get_post_type($id)==='wtfq_quiz'?$id:0;
}
add_action('admin_enqueue_scripts',function(){
    $screen=get_current_screen();if(!$screen || !in_array($screen->post_type,['wtfq_quiz','wtfq_question'],true))return;
    wp_enqueue_media();wp_enqueue_editor();
    wp_enqueue_style('wtfq-admin',plugins_url('../assets/admin.css',__FILE__),[],filemtime(__DIR__.'/../assets/admin.css'));
    wp_enqueue_script('wtfq-admin',plugins_url('../assets/admin.js',__FILE__),['wp-editor'],filemtime(__DIR__.'/../assets/admin.js'),true);
    $quizzes=[];foreach(get_posts(['post_type'=>'wtfq_quiz','post_status'=>['publish','draft','private','pending'],'numberposts'=>-1,'suppress_filters'=>true]) as $q)$quizzes[$q->ID]=get_post_meta($q->ID,'_wtfq_outcomes',true)?:[];
    wp_localize_script('wtfq-admin','wtfqAdmin',['quizzes'=>$quizzes]);
});
add_action('add_meta_boxes',function(){
    add_meta_box('wtfq-questions','Pytania','wtfq_questions_box','wtfq_quiz','normal','high');
    add_meta_box('wtfq-settings','Ustawienia quizu','wtfq_quiz_box','wtfq_quiz','normal','high');
    add_meta_box('wtfq-outcomes','Wyniki / archetypy','wtfq_outcomes_box','wtfq_quiz','normal','default');
    add_meta_box('wtfq-question','Pytanie i odpowiedzi','wtfq_question_box','wtfq_question','normal','high');
});
add_action('edit_form_after_title',function($post){
    if($post->post_type!=='wtfq_quiz')return;
    echo '<nav class="nav-tab-wrapper wtfq-tabs" aria-label="Edycja quizu"><button type="button" class="nav-tab nav-tab-active" data-wtfq-tab="wtfq-questions">Pytania</button><button type="button" class="nav-tab" data-wtfq-tab="wtfq-outcomes">Wyniki / archetypy</button><button type="button" class="nav-tab" data-wtfq-tab="wtfq-settings">Ustawienia</button></nav>';
});
function wtfq_questions_box($post){
    echo '<p><a class="button button-primary" href="'.esc_url(admin_url('post-new.php?post_type=wtfq_question&quiz='.$post->ID)).'">Dodaj pytanie</a> <a class="button" href="'.esc_url(admin_url('edit.php?post_type=wtfq_question&quiz='.$post->ID)).'">Lista i filtrowanie pytań</a></p><ol class="wtfq-question-list">';
    foreach(wtfq_questions($post->ID,['publish','draft','private','pending']) as $question)echo '<li><a href="'.esc_url(get_edit_post_link($question->ID)).'">'.esc_html($question->post_title).'</a>'.($question->post_status!=='publish'?' — szkic':'').'</li>';
    echo '</ol><p class="description">Kliknij pytanie, aby zmienić treść, ilustrację, odpowiedzi, przypisania i kolejność.</p>';
}
function wtfq_quiz_box($post) {
    wp_nonce_field('wtfq_save','wtfq_nonce');
    echo '<p>Shortcode: <code>[wtf_quiz id="'.(int)$post->ID.'"]</code></p>';
    echo '<p><a class="button" href="'.esc_url(admin_url('edit.php?post_type=wtfq_question&quiz='.$post->ID)).'">Pytania tego quizu</a> <a class="button button-primary" href="'.esc_url(admin_url('post-new.php?post_type=wtfq_question&quiz='.$post->ID)).'">Dodaj pytanie</a></p>';
    echo '<p>Każda odpowiedź dodaje po 1 punkcie wskazanym wynikom. Przy remisie losujemy jeden z najlepszych wyników. Nie zapisujemy odpowiedzi odwiedzających.</p><h3>Zaproszenie do quizu</h3>';
    wp_editor(get_post_meta($post->ID,'_wtfq_intro',true),'wtfq_intro',['textarea_name'=>'wtfq_intro','textarea_rows'=>4,'media_buttons'=>false]);
}
function wtfq_outcome_row($key,$row=[]) {
    $id=$row['id']??('outcome_'.wp_generate_uuid4());
    echo '<div class="wtfq-outcome wtfq-card"><h3>Wynik / archetyp</h3><input type="hidden" name="wtfq_outcomes['.esc_attr($key).'][id]" value="'.esc_attr($id).'">';
    echo '<p><label>Nazwa wyniku <input class="widefat" required name="wtfq_outcomes['.esc_attr($key).'][title]" value="'.esc_attr($row['title']??'').'"></label></p>';
    echo '<p><label>Adres podstrony archetypu <input type="url" class="widefat" name="wtfq_outcomes['.esc_attr($key).'][url]" value="'.esc_attr($row['url']??'').'"></label></p>';
    echo '<p>Opis wyniku i ilustracja</p>';
    wp_editor($row['content']??'','wtfq_result_'.sanitize_key($key),['textarea_name'=>'wtfq_outcomes['.$key.'][content]','textarea_rows'=>7,'media_buttons'=>true]);
    echo '<p><button type="button" class="button-link-delete" data-remove>Usuń wynik</button></p></div>';
}
function wtfq_outcomes_box($post) {
    echo '<p>Nazwy możesz zmieniać bez utraty przypisań odpowiedzi. Usunięcie wyniku wymaga sprawdzenia pytań, które na niego wskazywały.</p><div data-outcomes>';
    foreach((array)get_post_meta($post->ID,'_wtfq_outcomes',true) as $key=>$row)if(is_array($row))wtfq_outcome_row($key,$row);
    echo '</div><button type="button" class="button" data-add-outcome>Dodaj wynik</button>';
}
function wtfq_question_box($post) {
    wp_nonce_field('wtfq_save','wtfq_nonce');$quiz=wtfq_admin_quiz_id($post);
    echo '<p><label>Quiz <select name="wtfq_quiz" id="wtfq-quiz" required><option value="">Wybierz quiz</option>';
    foreach(get_posts(['post_type'=>'wtfq_quiz','post_status'=>['publish','draft','pending','private'],'numberposts'=>-1,'suppress_filters'=>true]) as $q)echo '<option value="'.(int)$q->ID.'" '.selected($quiz,$q->ID,false).'>'.esc_html($q->post_title).'</option>';
    echo '</select></label> <label>Kolejność <input type="number" name="wtfq_order" value="'.(int)$post->menu_order.'" min="0" class="small-text"></label></p>';
    $image=(int)get_post_meta($post->ID,'_wtfq_image',true);
    echo '<div class="wtfq-image-field"><input type="hidden" name="wtfq_image" value="'.$image.'"><div data-image-preview>'.($image?wp_get_attachment_image($image,'medium'):'').'</div><p><button type="button" class="button" data-choose-image>Wybierz ilustrację</button> <button type="button" class="button-link-delete" data-clear-image>Usuń ilustrację</button></p><p class="description">Ilustracja opcjonalna. Dowolne proporcje, bez kadrowania; zalecana szerokość 1200 px.</p></div>';
    echo '<h3>Odpowiedzi</h3><p>Zaznacz archetypy otrzymujące po +1 punkcie za tę odpowiedź. Możesz wskazać kilka. Wszystkie pytania są wymagane.</p>';
    $answers=get_post_meta($post->ID,'_wtfq_answers',true)?:array_fill(0,4,['text'=>'','outcomes'=>[]]);
    echo '<div data-answers data-initial="'.esc_attr(wp_json_encode($answers)).'"></div><button type="button" class="button" data-add-answer>Dodaj odpowiedź</button>';
}

add_action('save_post',function($id,$post){
    if(!in_array($post->post_type,['wtfq_quiz','wtfq_question'],true) || wp_is_post_revision($id) || (defined('DOING_AUTOSAVE')&&DOING_AUTOSAVE) || !current_user_can('manage_options') || !isset($_POST['wtfq_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtfq_nonce'])),'wtfq_save'))return;
    if($post->post_type==='wtfq_quiz'){
        $outcomes=[];$seen=[];
        foreach((array)wp_unslash($_POST['wtfq_outcomes']??[]) as $row){
            if(!is_array($row))continue;$title=sanitize_text_field($row['title']??'');$key=sanitize_key($row['id']??'');
            if(!$title || !$key || isset($seen[$key]))continue;$seen[$key]=true;
            $outcomes[]=['id'=>$key,'title'=>$title,'content'=>wp_kses_post($row['content']??''),'url'=>esc_url_raw($row['url']??'')];
        }
        update_post_meta($id,'_wtfq_outcomes',$outcomes);update_post_meta($id,'_wtfq_intro',wp_kses_post(wp_unslash($_POST['wtfq_intro']??'')));
    }else{
        $quiz=absint($_POST['wtfq_quiz']??0);if(get_post_type($quiz)!=='wtfq_quiz')return;
        update_post_meta($id,'_wtfq_quiz',$quiz);
        $answers=wtfq_clean_answers(wp_unslash($_POST['wtfq_answers']??[]),get_post_meta($quiz,'_wtfq_outcomes',true)?:[]);
        update_post_meta($id,'_wtfq_answers',$answers);
        $image=absint($_POST['wtfq_image']??0);update_post_meta($id,'_wtfq_image',wp_attachment_is_image($image)?$image:0);
    }
},10,2);
add_filter('wp_insert_post_data',function($data){
    if(($data['post_type']??'')==='wtfq_question' && isset($_POST['wtfq_nonce'],$_POST['wtfq_order']) && current_user_can('manage_options') && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wtfq_nonce'])),'wtfq_save'))$data['menu_order']=absint($_POST['wtfq_order']);return $data;
});
add_filter('manage_wtfq_quiz_posts_columns',function($columns){return ['cb'=>$columns['cb'],'title'=>'Quiz','wtfq_count'=>'Pytania','wtfq_shortcode'=>'Shortcode','date'=>$columns['date']];});
add_action('manage_wtfq_quiz_posts_custom_column',function($column,$id){
    if($column==='wtfq_count')echo '<a href="'.esc_url(admin_url('edit.php?post_type=wtfq_question&quiz='.$id)).'">'.count(wtfq_questions($id,['publish','draft','pending','private'])).' — zobacz pytania</a>';
    if($column==='wtfq_shortcode')echo '<code>[wtf_quiz id="'.(int)$id.'"]</code>';
},10,2);
add_filter('manage_wtfq_question_posts_columns',function($columns){return ['cb'=>$columns['cb'],'title'=>'Pytanie','wtfq_quiz'=>'Quiz','wtfq_order'=>'Kolejność','wtfq_answers'=>'Odpowiedzi','date'=>$columns['date']];});
add_action('manage_wtfq_question_posts_custom_column',function($column,$id){
    if($column==='wtfq_quiz')echo esc_html(get_the_title((int)get_post_meta($id,'_wtfq_quiz',true)));
    if($column==='wtfq_order')echo (int)get_post_field('menu_order',$id);
    if($column==='wtfq_answers'){
        $answers=get_post_meta($id,'_wtfq_answers',true)?:[];$outcomes=get_post_meta((int)get_post_meta($id,'_wtfq_quiz',true),'_wtfq_outcomes',true)?:[];
        $valid=wtfq_clean_answers($answers,$outcomes);$complete=count($valid)>=2 && !in_array([],array_column($valid,'outcomes'),true);
        echo (int)count($answers).($complete?'':' — uzupełnij przypisania');
    }
},10,2);
add_action('restrict_manage_posts',function($type){if($type!=='wtfq_question')return;echo '<select name="quiz"><option value="">Wszystkie quizy</option>';foreach(get_posts(['post_type'=>'wtfq_quiz','post_status'=>['publish','draft','private','pending'],'numberposts'=>-1,'suppress_filters'=>true]) as $q)echo '<option value="'.(int)$q->ID.'" '.selected(absint($_GET['quiz']??0),$q->ID,false).'>'.esc_html($q->post_title).'</option>';echo '</select>';});
add_action('pre_get_posts',function($query){if(!is_admin()||!$query->is_main_query()||$query->get('post_type')!=='wtfq_question')return;$query->set('orderby',['menu_order'=>'ASC','ID'=>'ASC']);if(!empty($_GET['quiz'])){$query->set('meta_key','_wtfq_quiz');$query->set('meta_value',absint($_GET['quiz']));}});
add_filter('post_row_actions',function($actions,$post){if($post->post_type==='wtfq_quiz')$actions['wtfq_add']='<a href="'.esc_url(admin_url('post-new.php?post_type=wtfq_question&quiz='.$post->ID)).'">Dodaj pytanie</a>';return $actions;},10,2);
