<?php
/**
 * Plugin Name: wtf_quiz
 * Description: Quizy osobowościowe, edycja pytań i archetypów oraz losowanie przy remisie.
 * Version: 0.1.0
 * Requires PHP: 8.0
 * Text Domain: wtf_quiz
 */
if (!defined('ABSPATH')) exit;
require_once __DIR__.'/includes/admin.php';

function wtfq_register() {
    foreach (['wtfq_quiz'=>['Quizy','Quiz','quiz'],'wtfq_question'=>['Pytania','Pytanie','question']] as $type=>$labels) {
        register_post_type($type, [
            'labels'=>['name'=>$labels[0],'singular_name'=>$labels[1],'add_new'=>'Dodaj','add_new_item'=>'Dodaj '.($type==='wtfq_quiz'?'quiz':'pytanie'),'edit_item'=>'Edytuj '.($type==='wtfq_quiz'?'quiz':'pytanie')],
            'public'=>false,'show_ui'=>true,'show_in_menu'=>'wtf_quiz','supports'=>['title'],
            'capabilities'=>array_fill_keys(['edit_post','read_post','delete_post','edit_posts','edit_others_posts','publish_posts','read_private_posts','delete_posts','delete_private_posts','delete_published_posts','delete_others_posts','edit_private_posts','edit_published_posts','create_posts'],'manage_options'),
        ]);
    }
    wp_register_style('wtf_quiz',plugins_url('assets/quiz.css',__FILE__),[],filemtime(__DIR__.'/assets/quiz.css'));
    wp_register_script('wtf_quiz',plugins_url('assets/quiz.js',__FILE__),[],filemtime(__DIR__.'/assets/quiz.js'),true);
}
add_action('init','wtfq_register');
add_action('wp_enqueue_scripts',function(){
    if(is_page() && get_post_meta(get_queried_object_id(),'_wtfq_page',true))wp_enqueue_style('wtf_quiz');
});

function wtfq_questions($quiz, $status='publish') {
    return get_posts(['post_type'=>'wtfq_question','post_status'=>$status,'numberposts'=>-1,'meta_key'=>'_wtfq_quiz','meta_value'=>(int)$quiz,'orderby'=>['menu_order'=>'ASC','ID'=>'ASC'],'suppress_filters'=>true]);
}

// One answer awards one point to every selected outcome. Keep stable IDs independent of labels.
function wtfq_clean_answers($answers, $outcomes) {
    $ids=array_column($outcomes,'id');$clean=[];
    foreach ((array)$answers as $answer) {
        if (!is_array($answer)) continue;
        $text=sanitize_text_field($answer['text']??'');
        if ($text==='') continue;
        $clean[]=['text'=>$text,'outcomes'=>array_values(array_intersect($ids,array_unique(array_map('sanitize_key',(array)($answer['outcomes']??[])))))];
    }
    return $clean;
}

add_shortcode('wtf_quiz',function ($attributes) {
    $a=shortcode_atts(['id'=>0],$attributes,'wtf_quiz');$id=absint($a['id']);$quiz=get_post($id);
    if (!$quiz || $quiz->post_type!=='wtfq_quiz' || $quiz->post_status!=='publish') return '';
    $outcomes=get_post_meta($id,'_wtfq_outcomes',true)?:[];
    $questions=wtfq_questions($id);
    if (!$outcomes || !$questions) return current_user_can('manage_options')?'<p>Uzupełnij wyniki i pytania quizu w WTF Quiz.</p>':'';
    $data=[];
    foreach ($questions as $question) {
        $answers=wtfq_clean_answers(get_post_meta($question->ID,'_wtfq_answers',true),$outcomes);
        if (count($answers)<2 || in_array([],array_column($answers,'outcomes'),true)) return current_user_can('manage_options')?'<p>Każde pytanie wymaga co najmniej dwóch odpowiedzi z przypisanymi wynikami.</p>':'';
        $data[]=['title'=>$question->post_title,'answers'=>$answers,'image'=>wp_get_attachment_image((int)get_post_meta($question->ID,'_wtfq_image',true),'large',false,['loading'=>'lazy','class'=>'wtfq-question-image','sizes'=>'(max-width: 700px) 90vw, 700px'])];
    }
    wp_enqueue_style('wtf_quiz');wp_enqueue_script('wtf_quiz');
    $instance=wp_unique_id('wtfq-');
    ob_start(); ?>
    <div class="wtfq" data-wtfq>
        <header class="wtfq-heading">
        <p class="wtfq-eyebrow">Quiz osobowościowy</p>
        <h1><?php echo esc_html($quiz->post_title); ?></h1>
        <div class="wtfq-intro"><?php echo wp_kses_post(wpautop(get_post_meta($id,'_wtfq_intro',true))); ?></div>
        </header>
        <noscript><p>Włącz JavaScript, aby rozwiązać quiz.</p></noscript>
        <div class="wtfq-app" hidden>
            <p class="wtfq-progress" role="status" aria-live="polite"></p>
            <form novalidate>
            <?php foreach ($data as $index=>$question): ?>
            <fieldset class="wtfq-question" data-question="<?php echo (int)$index; ?>"<?php if($index)echo ' hidden'; ?>>
                <legend tabindex="-1"><?php echo esc_html($question['title']); ?></legend>
                <?php echo $question['image']; ?>
                <div class="wtfq-answers"><?php foreach ($question['answers'] as $n=>$answer): $input=$instance.'-'.$index.'-'.$n; ?>
                <label for="<?php echo esc_attr($input); ?>"><input required type="radio" id="<?php echo esc_attr($input); ?>" name="<?php echo esc_attr($instance.'-'.$index); ?>" value="<?php echo (int)$n; ?>"><span><?php echo esc_html($answer['text']); ?></span></label>
                <?php endforeach; ?></div>
            </fieldset>
            <?php endforeach; ?>
            <p class="wtfq-error" role="alert"></p>
            <div class="wtfq-controls"><button type="button" data-prev>Poprzednie</button><button type="submit" data-next>Dalej</button></div>
            </form>
        </div>
        <section class="wtfq-result" aria-label="Wynik quizu" hidden>
            <?php foreach ($outcomes as $outcome): ?>
            <?php $parts=wtfq_result_parts($outcome['content'],!empty($outcome['url'])); ?>
            <article data-outcome="<?php echo esc_attr($outcome['id']); ?>" class="wtfq-result-card<?php if(!$parts['image'])echo ' wtfq-result-card--text'; ?>" hidden>
                <?php if($parts['image']): ?><figure class="wtfq-result-image"><?php echo $parts['image']; ?></figure><?php endif; ?>
                <div class="wtfq-result-copy"><p class="wtfq-eyebrow">Twój archetyp</p><h2 tabindex="-1" role="heading" aria-level="1"><?php echo esc_html($outcome['title']); ?></h2>
                <div class="wtfq-result-content"><?php echo $parts['body']; ?></div>
                <?php if(!empty($outcome['url'])): ?><div class="wtfq-result-actions"><a class="wtfq-button" href="<?php echo esc_url($outcome['url']); ?>">Poznaj ten archetyp</a><a class="wtfq-share" href="<?php echo esc_url(add_query_arg('u',$outcome['url'],'https://www.facebook.com/sharer/sharer.php')); ?>" target="_blank" rel="noopener">Udostępnij wynik</a></div><?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
            <button type="button" data-restart>Rozwiąż ponownie</button>
        </section>
        <script type="application/json" class="wtfq-data"><?php echo wp_json_encode(['outcomes'=>array_column($outcomes,'id'),'urls'=>array_column($outcomes,'url','id'),'questions'=>array_map(fn($q)=>array_column($q['answers'],'outcomes'),$data)],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?></script>
    </div>
    <?php return ob_get_clean();
});

// Presentation only: preserve complete editable descriptions, show a short result with a link.
function wtfq_result_parts($content,$excerpt=false){
    $html=wp_kses_post(wpautop($content));$image='';
    if(preg_match('~<img\b[^>]*>~i',$html,$match)){$image=$match[0];$html=preg_replace('~<img\b[^>]*>~i','',$html,1);}
    $html=preg_replace('~<a\b[^>]*href=["\'][^"\']*facebook\.com/sharer[^"\']*["\'][^>]*>.*?</a>~is','',$html);
    $html=preg_replace('~<p>(?:\s|&nbsp;)*</p>~i','',$html);
    if($excerpt && preg_match_all('~<p\b[^>]*>.*?</p>~is',$html,$paragraphs))$html=implode('',array_slice($paragraphs[0],0,2));
    return ['image'=>$image,'body'=>$html];
}

// A single description, edited in WTF Quiz, used for direct visits and quiz completion.
add_shortcode('wtf_quiz_outcome',function($attributes){
    $a=shortcode_atts(['quiz'=>0,'outcome'=>'','quiz_url'=>''],$attributes,'wtf_quiz_outcome');
    $quiz=get_post(absint($a['quiz']));if(!$quiz || $quiz->post_type!=='wtfq_quiz' || $quiz->post_status!=='publish')return '';
    $outcome=null;foreach((array)get_post_meta($quiz->ID,'_wtfq_outcomes',true) as $item)if($item['id']===sanitize_key($a['outcome'])){$outcome=$item;break;}
    if(!$outcome)return '';
    wp_enqueue_style('wtf_quiz');wp_enqueue_script('wtf_quiz');
    $parts=wtfq_result_parts($outcome['content']);$canonical=get_permalink(get_queried_object_id());
    ob_start(); ?>
    <div class="wtfq wtfq--complete wtfq--outcome" data-wtfq-outcome>
        <article class="wtfq-result-card<?php if(!$parts['image'])echo ' wtfq-result-card--text'; ?>">
            <?php if($parts['image']): ?><figure class="wtfq-result-image"><?php echo $parts['image']; ?></figure><?php endif; ?>
            <div class="wtfq-result-copy"><p class="wtfq-eyebrow"><span data-outcome-direct>Archetyp</span><span data-outcome-earned hidden>Twój archetyp</span></p>
            <h1><?php echo esc_html($outcome['title']); ?></h1>
            <div class="wtfq-result-content"><?php echo $parts['body']; ?></div>
            <div class="wtfq-result-actions" data-outcome-controls hidden>
                <a class="wtfq-button" href="<?php echo esc_url(add_query_arg('u',$canonical,'https://www.facebook.com/sharer/sharer.php')); ?>" target="_blank" rel="noopener">Udostępnij swój wynik</a>
                <?php if($a['quiz_url']): ?><a class="wtfq-share" href="<?php echo esc_url($a['quiz_url']); ?>">Rozwiąż ponownie</a><?php endif; ?>
            </div></div>
        </article>
    </div>
    <?php return ob_get_clean();
});
