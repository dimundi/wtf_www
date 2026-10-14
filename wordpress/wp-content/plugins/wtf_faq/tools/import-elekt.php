<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
wp_set_current_user(1);
require_once ABSPATH.'wp-admin/includes/image.php';
$doc=new DOMDocument();libxml_use_internal_errors(true);
$sourceFile=$args[0]??'/tmp/elekt-faq-source.html';
if (!is_file($sourceFile)) { WP_CLI::error('Podaj ścieżkę do pobranego HTML FAQ.'); }
$doc->loadHTML('<?xml encoding="utf-8">'.file_get_contents($sourceFile));libxml_clear_errors();
$xpath=new DOMXPath($doc);
function ele_faq_class($name) { return 'contains(concat(" ", normalize-space(@class), " "), " '.$name.' ")'; }
function ele_faq_inner($node) { $html='';foreach ($node->childNodes as $child) {$html.=$node->ownerDocument->saveHTML($child);}return $html; }
$game=term_exists('elekt','wtf_faq_game')?:wp_insert_term('Elekt','wtf_faq_game',['slug'=>'elekt']);
if (is_wp_error($game)) { WP_CLI::error($game->get_error_message()); }
$total=0;$order=0;$upTotal=0;$downTotal=0;$groupSlugs=[];$sourceImageIds=[];
foreach ($xpath->query('//div['.ele_faq_class('ewd-ufaq-faq-category').']') as $category) {
    $heading=$xpath->query('.//div['.ele_faq_class('ewd-ufaq-faq-category-title').']//h2',$category)->item(0);
    if (!$heading) { continue; }
    $name=trim($heading->textContent);$slug=sanitize_title($name);$groupSlugs[]=$slug;
    $group=term_exists($slug,'wtf_faq_group')?:wp_insert_term($name,'wtf_faq_group',['slug'=>$slug]);
    if (is_wp_error($group)) { WP_CLI::error($group->get_error_message()); }
    foreach ($xpath->query('.//div['.ele_faq_class('ewd-ufaq-faq-div').']',$category) as $item) {
        $sourceId=$item->getAttribute('data-post_id');
        $position=$order++;
        $existing=get_posts(['post_type'=>'wtf_faq','post_status'=>['publish','draft'],'numberposts'=>1,'meta_key'=>'_wtf_faq_source_id','meta_value'=>'elekt:'.$sourceId,'suppress_filters'=>true]);
        if ($existing) { WP_CLI::log('Zachowano pytanie '.$sourceId);continue; }
        $title=$xpath->query('.//div['.ele_faq_class('ewd-ufaq-faq-title-text').']//h4',$item)->item(0);
        $body=$xpath->query('.//div['.ele_faq_class('ewd-ufaq-faq-post').']',$item)->item(0);
        $up=$xpath->query('.//div['.ele_faq_class('ewd-ufaq-up-vote').']/span',$item)->item(0);
        $down=$xpath->query('.//div['.ele_faq_class('ewd-ufaq-down-vote').']/span',$item)->item(0);
        if (!$title||!$body||!$up||!$down||!ctype_digit(trim($up->textContent))||!ctype_digit(trim($down->textContent))) { WP_CLI::error('Niepełne pytanie: '.$sourceId); }
        foreach ($xpath->query('.//img',$body) as $img) {
            $url=$img->getAttribute('src');
            if (!isset($sourceImageIds[$url])) {
                $filename='faq-elekt-'.sanitize_file_name(basename(parse_url($url,PHP_URL_PATH)));
                $path=wp_upload_dir()['basedir'].'/media/'.$filename;
                $existingMedia=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wtf_faq_source_image','meta_value'=>$url]);
                if ($existingMedia) { $mediaId=$existingMedia[0]->ID; }
                else {
                    $response=wp_remote_get($url,['timeout'=>30]);
                    if (is_wp_error($response)||wp_remote_retrieve_response_code($response)!==200) { WP_CLI::error('Nie pobrano obrazu: '.$url); }
                    file_put_contents($path,wp_remote_retrieve_body($response));
                    $mime=wp_check_filetype($filename)['type'];
                    $mediaId=wp_insert_attachment(['post_title'=>pathinfo($filename,PATHINFO_FILENAME),'post_mime_type'=>$mime,'post_status'=>'inherit'],$path,0,true);
                    if (is_wp_error($mediaId)) { WP_CLI::error($mediaId->get_error_message()); }
                    update_attached_file($mediaId,$path);wp_update_attachment_metadata($mediaId,wp_generate_attachment_metadata($mediaId,$path));
                    update_post_meta($mediaId,'_wtf_faq_source_image',$url);
                }
                $sourceImageIds[$url]=$mediaId;
            }
            $img->setAttribute('src',wp_get_attachment_url($sourceImageIds[$url]));
            $img->setAttribute('class','wp-image-'.$sourceImageIds[$url]);
            $img->setAttribute('loading','lazy');$img->removeAttribute('srcset');$img->removeAttribute('sizes');
        }
        $id=wp_insert_post(wp_slash(['post_type'=>'wtf_faq','post_status'=>'publish','post_title'=>trim($title->textContent),'post_content'=>wp_kses_post(ele_faq_inner($body)),'menu_order'=>$position]),true);
        if (is_wp_error($id)) { WP_CLI::error($id->get_error_message()); }
        wp_set_object_terms($id,[(int)$group['term_id']],'wtf_faq_group');wp_set_object_terms($id,[(int)$game['term_id']],'wtf_faq_game');
        update_post_meta($id,'_wtf_faq_source_id','elekt:'.$sourceId);
        update_post_meta($id,'_wtf_faq_import_up',(int)$up->textContent);update_post_meta($id,'_wtf_faq_import_down',(int)$down->textContent);
        $upTotal+=(int)$up->textContent;$downTotal+=(int)$down->textContent;$total++;
    }
}
if (!$groupSlugs) { WP_CLI::error('Nie znaleziono grup.'); }
$shortcode='[wtf_faq game="elekt" groups="'.implode(',',$groupSlugs).'"]';
update_option('wtf_faq_elekt_shortcode',$shortcode);
WP_CLI::success(wp_json_encode(['questions'=>$total,'groups'=>count($groupSlugs),'up'=>$upTotal,'down'=>$downTotal,'images'=>count($sourceImageIds),'shortcode'=>$shortcode],JSON_UNESCAPED_UNICODE));
