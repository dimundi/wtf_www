<?php
if (!defined('ABSPATH')) { exit; }
function wtf_faq_install_votes() {
    global $wpdb;
    require_once ABSPATH.'wp-admin/includes/upgrade.php';
    $table=$wpdb->prefix.'wtf_faq_votes';
    dbDelta("CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        faq_id bigint(20) unsigned NOT NULL,
        visitor_hash char(64) NOT NULL,
        direction tinyint NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY faq_visitor (faq_id,visitor_hash)
    ) ".$wpdb->get_charset_collate().';');
}
function wtf_faq_vote_counts($ids) {
    global $wpdb;
    $ids=array_map('absint',$ids);$out=[];
    foreach ($ids as $id) { $out[$id]=['up'=>(int)get_post_meta($id,'_wtf_faq_import_up',true),'down'=>(int)get_post_meta($id,'_wtf_faq_import_down',true)]; }
    if (!$ids) { return $out; }
    $rows=$wpdb->get_results('SELECT faq_id, SUM(direction=1) AS up_votes, SUM(direction=-1) AS down_votes FROM '.$wpdb->prefix.'wtf_faq_votes WHERE faq_id IN ('.implode(',',$ids).') GROUP BY faq_id');
    foreach ($rows as $row) { $out[$row->faq_id]['up']+=(int)$row->up_votes;$out[$row->faq_id]['down']+=(int)$row->down_votes; }
    return $out;
}
add_action('rest_api_init', function () {
    register_rest_route('wtf-faq/v1','/vote',['methods'=>'POST','permission_callback'=>'__return_true','callback'=>function ($request) {
        global $wpdb;
        $id=absint($request->get_param('id'));$direction=$request->get_param('direction');$visitor=$request->get_param('visitor');
        if (!is_string($visitor) || !preg_match('/^[a-zA-Z0-9-]{32,64}$/D',$visitor) || !in_array($direction,['up','down'],true)) { return new WP_Error('invalid_vote','Niepoprawny głos.',['status'=>400]); }
        if (get_post_type($id)!=='wtf_faq' || get_post_status($id)!=='publish') { return new WP_Error('missing_question','Nie znaleziono pytania.',['status'=>404]); }
        $hash=hash_hmac('sha256',$visitor,wp_salt('auth'));
        $limitKey='wtf_faq_limit_'.substr($hash,0,32);$rate=(int)get_transient($limitKey);
        if ($rate>=30) { return new WP_Error('vote_limit','Spróbuj ponownie za chwilę.',['status'=>429]); }
        if (!$rate) { set_transient($limitKey,1,MINUTE_IN_SECONDS); }
        else { $expires=(int)get_option('_transient_timeout_'.$limitKey);set_transient($limitKey,$rate+1,max(1,$expires-time())); }
        $table=$wpdb->prefix.'wtf_faq_votes';
        $insert=$wpdb->query($wpdb->prepare("INSERT IGNORE INTO $table (faq_id,visitor_hash,direction,created_at) VALUES (%d,%s,%d,%s)",$id,$hash,$direction==='up'?1:-1,current_time('mysql',true)));
        if ($insert===false) { return new WP_Error('vote_failed','Nie udało się zapisać głosu.',['status'=>500]); }
        $saved=(int)$wpdb->get_var($wpdb->prepare("SELECT direction FROM $table WHERE faq_id=%d AND visitor_hash=%s",$id,$hash));
        return ['counts'=>wtf_faq_vote_counts([$id])[$id],'direction'=>$saved===1?'up':'down','alreadyVoted'=>$insert===0];
    }]);
});
