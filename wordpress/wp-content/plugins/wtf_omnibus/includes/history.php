<?php
if (!defined('ABSPATH')) exit;

function wtfo_install() {
    global $wpdb;
    require_once ABSPATH.'wp-admin/includes/upgrade.php';
    $table=$wpdb->prefix.'wtfo_prices';
    dbDelta("CREATE TABLE $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        product_id bigint(20) unsigned NOT NULL,
        price decimal(20,6) NOT NULL,
        recorded_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY product_time (product_id,recorded_at,id)
    ) ".$wpdb->get_charset_collate().';');
    update_option('wtfo_schema', '1', false);
}

function wtfo_supported($product) {
    return $product instanceof WC_Product && $product->is_type(['simple','variation']) && get_woocommerce_currency()==='PLN';
}

function wtfo_state($id) {
    $state=get_post_meta($id, '_wtfo_state', true);
    return is_array($state) ? $state : [];
}

function wtfo_event($id, $price, $time) {
    global $wpdb;
    return $wpdb->insert($wpdb->prefix.'wtfo_prices', ['product_id'=>$id,'price'=>wc_format_decimal($price,6),'recorded_at'=>gmdate('Y-m-d H:i:s',$time)], ['%d','%s','%s']);
}

/** Seed is an explicit initial reference, never fabricated historical observations. */
function wtfo_initialize($product, $reference=null, $now=null) {
    if (!wtfo_supported($product) || wtfo_state($product->get_id()) || $product->get_price()==='') return false;
    $now=$now ?? time();
    $price=wc_format_decimal($product->get_price(),6);
    $seed=$reference===null ? $price : wc_format_decimal($reference,6);
    $sale=$product->is_on_sale();
    $state=['since'=>$now,'seed'=>$seed,'seed_manual'=>$reference!==null,'last'=>$price,'sale'=>$sale,'start'=>$sale?$now-14*DAY_IN_SECONDS:null,'reference'=>$sale?$seed:null,'manual_reference'=>$sale,'short'=>false];
    if (false===wtfo_event($product->get_id(),$price,$now)) return false;
    update_post_meta($product->get_id(),'_wtfo_state',$state);
    return true;
}

/** Include the price carried into the window, not only changes inside it. */
function wtfo_reference($product, $start, $state, $include_previous=false) {
    global $wpdb;
    $table=$wpdb->prefix.'wtfo_prices';
    $window=$start-30*DAY_IN_SECONDS;
    $values=$wpdb->get_col($wpdb->prepare("SELECT price FROM $table WHERE product_id=%d AND recorded_at >= %s AND recorded_at < %s",$product->get_id(),gmdate('Y-m-d H:i:s',$window),gmdate('Y-m-d H:i:s',$start)));
    $previous=$wpdb->get_var($wpdb->prepare("SELECT price FROM $table WHERE product_id=%d AND recorded_at < %s ORDER BY recorded_at DESC,id DESC LIMIT 1",$product->get_id(),gmdate('Y-m-d H:i:s',$window)));
    if ($previous!==null) $values[]=$previous;
    if ($state['since']>$window && isset($state['seed'])) $values[]=$state['seed'];
    // Two edits in the same second still have a genuine preceding price in state.
    if ($include_previous && isset($state['last'])) $values[]=$state['last'];
    return $values ? wc_format_decimal(min(array_map('floatval',$values)),6) : null;
}

function wtfo_track($product) {
    if (!wtfo_supported($product) || $product->get_price()==='') return;
    $id=$product->get_id();
    $state=wtfo_state($id);
    if (!$state) { wtfo_initialize($product); return; }
    $now=time();
    $price=wc_format_decimal($product->get_price(),6);
    $sale=$product->is_on_sale();
    $changed=$price!==$state['last'];
    if ($sale && (empty($state['sale']) || $changed)) {
        $reference=wtfo_reference($product,$now,$state,true);
        $state['start']=$now;
        $state['reference']=$reference;
        $state['manual_reference']=false;
        $created=$product->get_date_created();
        $state['short']=$created && $created->getTimestamp()>$now-30*DAY_IN_SECONDS;
    } elseif (!$sale) {
        $state['start']=null;
        $state['reference']=null;
        $state['manual_reference']=false;
    }
    if ($changed && false===wtfo_event($id,$price,$now)) return;
    $state['last']=$price;
    $state['sale']=$sale;
    update_post_meta($id,'_wtfo_state',$state);
}

function wtfo_initialise_missing() {
    $count=0;
    $page=1;
    do {
        $query=new WP_Query(['post_type'=>['product','product_variation'],'post_status'=>['publish','private'],'posts_per_page'=>100,'paged'=>$page++,'fields'=>'ids','orderby'=>'ID','order'=>'ASC','meta_query'=>[['key'=>'_wtfo_state','compare'=>'NOT EXISTS']]]);
        // Paging over a changing result set would skip rows: collect before writing.
        $ids=array_merge($ids??[],$query->posts);
    } while ($page<=$query->max_num_pages);
    foreach ($ids as $id) if (wtfo_initialize(wc_get_product($id))) $count++;
    return $count;
}
