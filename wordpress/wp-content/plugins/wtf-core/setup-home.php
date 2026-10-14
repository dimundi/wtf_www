<?php
// Uruchamiane ręcznie przez WP-CLI, wyłącznie do pierwszej inicjalizacji dev.
if (!defined('WP_CLI') || !WP_CLI) { exit; }
if (get_option('wtf_home_initialized')) { WP_CLI::success('Strona główna istnieje — treści zachowane.'); return; }

$schemas=wtf_core_schema();
$files=['klanarchia.jpg','kurzolaki.jpg','elekt.jpg','tsukuyumi.png','echo-swiatow.jpg','platnosci.png','wtf-logo-white-green.svg','wtf-logo-navy-green.svg','wtf-logo.svg'];
$media=[];
require_once ABSPATH.'wp-admin/includes/image.php';
foreach($files as $file) {
    $attached='media/'.$file;
    $existing=get_posts(['post_type'=>'attachment','post_status'=>'inherit','numberposts'=>1,'meta_key'=>'_wp_attached_file','meta_value'=>$attached]);
    if($existing) { $media[$file]=$existing[0]->ID; continue; }
    $path=wp_upload_dir()['basedir'].'/'.$attached;
    if(!is_file($path)){ WP_CLI::error('Brak assetu: '.$file); }
    $mime=str_ends_with($file,'.svg')?'image/svg+xml':wp_check_filetype($file)['type'];
    $id=wp_insert_attachment(['post_title'=>pathinfo($file,PATHINFO_FILENAME),'post_mime_type'=>$mime,'post_status'=>'inherit','guid'=>wtf_core_media($file)],$path,0,true);
    if(is_wp_error($id))WP_CLI::error($id->get_error_message());
    update_attached_file($id,$path);
    if($mime!=='image/svg+xml')wp_update_attachment_metadata($id,wp_generate_attachment_metadata($id,$path));
    $media[$file]=$id;
}
function wtf_dev_block($name,$attributes){return serialize_block(['blockName'=>'wtf/'.$name,'attrs'=>$attributes,'innerBlocks'=>[],'innerHTML'=>'','innerContent'=>[]]);}
function wtf_dev_media_ids($attrs,$media){
    if(isset($attrs['image']))$attrs['imageId']=$media[basename($attrs['image'])]??0;
    if(isset($attrs['items']))foreach($attrs['items'] as &$item)if(isset($item['image']))$item['imageId']=$media[basename($item['image'])]??0;
    return $attrs;
}
$content='';
foreach(['hero','cards','promotion'] as $name)$content.=wtf_dev_block($name,wtf_dev_media_ids($schemas[$name]['defaults'],$media))."\n";
$two=$schemas['cards']['defaults'];
$two['eyebrow']='';$two['title']='Świetne gry dla dwóch graczy';$two['description']='Dwie osoby przy stole wystarczą. Wybierz słodką rywalizację albo bezwzględną walkę o władzę.';$two['columns']='2';$two['anchor']='two-player-games';
$two['items']=[$two['items'][2],$two['items'][0]];
$two['items'][0]['title']='Kurzołaki';$two['items'][0]['description']='Jedna landrynka, dwa sprytne plany. Zajrzyjcie pod łóżko i sprawdźcie, kto kogo przechytrzy.';$two['items'][0]['linkLabel']='Poznaj Kurzołaki';
$two['items'][1]['title']='Elekt';$two['items'][1]['description']='Tron jest jeden, a Was dwoje. Karciane szachy pełne intensywnej strategii. Idealne dla dwóch graczy.';$two['items'][1]['linkLabel']='Poznaj Elekta';
$content.=wtf_dev_block('cards',wtf_dev_media_ids($two,$media))."\n";
foreach(['about','shop-invitation'] as $name)$content.=wtf_dev_block($name,$schemas[$name]['defaults'])."\n";
$page=wp_insert_post(wp_slash(['post_type'=>'page','post_status'=>'publish','post_title'=>'Strona główna','post_name'=>'start','post_content'=>$content]),true);
if(is_wp_error($page))WP_CLI::error($page->get_error_message());
update_option('show_on_front','page');update_option('page_on_front',$page);
// Treści nagłówka i stopki są zapisane jako edytowalne części szablonu, nie tylko defaults PHP.
foreach(['header'=>'Nagłówek','footer'=>'Stopka'] as $slug=>$title){
    $part=wp_insert_post(wp_slash(['post_type'=>'wp_template_part','post_status'=>'publish','post_name'=>$slug,'post_title'=>$title,'post_content'=>wtf_dev_block($slug,wtf_dev_media_ids($schemas[$slug]['defaults'],$media))]),true);
    if(is_wp_error($part))WP_CLI::error($part->get_error_message());
    wp_set_object_terms($part,'wtf','wp_theme');wp_set_object_terms($part,$slug,'wp_template_part_area');
}
update_option('permalink_structure','/%postname%/');flush_rewrite_rules();
update_option('wtf_home_initialized',1);
WP_CLI::success('Zarejestrowano media, sześć sekcji strony głównej i części szablonu.');
