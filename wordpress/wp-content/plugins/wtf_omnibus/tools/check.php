<?php
// Integration checks: wp eval-file wp-content/plugins/wtf_omnibus/tools/check.php
if (!defined('WP_CLI') || !WP_CLI) exit;
$created=[];
$assert=function ($condition,$message) { if (!$condition) throw new RuntimeException($message); WP_CLI::log('OK: '.$message); };
$new=function ($price='100') use (&$created) {
    $product=new WC_Product_Simple();
    $product->set_name('WTF Omnibus — test lokalny');
    $product->set_status('private');
    $product->set_regular_price($price);
    $product->save();
    $created[]=$product->get_id();
    return $product;
};
try {
    global $wpdb;
    $table=$wpdb->prefix.'wtfo_prices';
    $p=$new(); $id=$p->get_id();
    $state=wtfo_state($id);
    $assert($state['seed']==='100.000000' && !$state['sale'],'Inicjalizacja bieżącą ceną');
    $p->save();
    $assert((int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE product_id=%d",$id))===1,'Zapis bez zmiany ceny nie duplikuje historii');
    $p->set_sale_price('80'); $p->save();
    $state=wtfo_state($id);
    $assert($state['reference']==='100.000000','Rozpoczęcie obniżki bierze poprzednią cenę');
    $reference=$state['reference']; $start=$state['start'];
    $p->set_description('Zmiana opisu'); $p->save();
    $state=wtfo_state($id);
    $assert($state['reference']===$reference && $state['start']===$start,'Cena odniesienia i początek obniżki nie zmieniają się przy edycji opisu');
    $assert(str_contains($p->get_price_html(),'wtfo-price-note'),'Informacja wyświetlana przez standardowe get_price_html');
    $p->set_sale_price('70'); $p->save();
    $assert(wtfo_state($id)['reference']==='80.000000','Kolejna obniżka uwzględnia poprzednią cenę promocyjną');
    $p->set_sale_price(''); $p->save();
    $assert(!wtfo_state($id)['sale'] && wtfo_note($p)==='','Koniec promocji usuwa komunikat');

    $old=$new('150'); $oid=$old->get_id(); $now=time();
    $wpdb->delete($table,['product_id'=>$oid]);
    $oldState=wtfo_state($oid); $oldState['since']=$now-70*DAY_IN_SECONDS; $oldState['last']='150.000000';
    update_post_meta($oid,'_wtfo_state',$oldState);
    wtfo_event($oid,'120',$now-60*DAY_IN_SECONDS);
    wtfo_event($oid,'150',$now-10*DAY_IN_SECONDS);
    $assert(wtfo_reference($old,$now,$oldState)==='120.000000','Cena obowiązująca na początku okna 30 dni jest uwzględniana');
    wtfo_event($oid,'90',$now-5*DAY_IN_SECONDS);
    $assert(wtfo_reference($old,$now,$oldState)==='90.000000','Minimum z rzeczywistych zmian w oknie');

    $seed=$new('200'); $sid=$seed->get_id();
    delete_post_meta($sid,'_wtfo_state'); $wpdb->delete($table,['product_id'=>$sid]);
    $seed->set_sale_price('180');
    // Initialize a preexisting promotion using the explicit starting reference.
    wtfo_initialize($seed,'190',$now);
    $seedState=wtfo_state($sid);
    $assert($seedState['reference']==='190.000000' && $seedState['start']===$now-14*DAY_IN_SECONDS,'Ręczna inicjalizacja i domyślny początek 14 dni temu');
    $assert(!wtfo_initialize($seed,'1'),'Ponowna inicjalizacja nie nadpisuje danych');

    $scheduled=$new('100');
    $scheduled->set_sale_price('75'); $scheduled->set_date_on_sale_from(time()+DAY_IN_SECONDS); $scheduled->save();
    $assert(!wtfo_state($scheduled->get_id())['sale'],'Zaplanowana przyszła promocja nie pokazuje komunikatu');
    $scheduled->set_date_on_sale_from(time()-60); $scheduled->save();
    $assert(wtfo_state($scheduled->get_id())['reference']==='100.000000','Uruchomienie zaplanowanej promocji zachowuje cenę odniesienia');

    $parent=new WC_Product_Variable(); $parent->set_name('WTF Omnibus — warianty test'); $parent->set_status('private');
    $attr=new WC_Product_Attribute(); $attr->set_name('Format'); $attr->set_options(['Mały','Duży']); $attr->set_variation(true); $attr->set_visible(true); $parent->set_attributes([$attr]); $parent->save(); $created[]=$parent->get_id();
    foreach (['Mały'=>'100','Duży'=>'200'] as $size=>$price) {
        $v=new WC_Product_Variation(); $v->set_parent_id($parent->get_id()); $v->set_attributes(['format'=>$size]); $v->set_regular_price($price); $v->save(); $created[]=$v->get_id();
        $v->set_sale_price((string)((int)$price-20)); $v->save();
        $assert(wtfo_state($v->get_id())['reference']===wc_format_decimal($price,6),'Oddzielna historia wariantu '.$size);
        $data=apply_filters('woocommerce_available_variation',['price_html'=>''],$parent,$v);
        $assert(str_contains($data['price_html'],'wtfo-price-note'),'Komunikat po wyborze wariantu '.$size);
    }
    WC_Product_Variable::sync($parent->get_id());
    $parent=wc_get_product($parent->get_id());
    $assert(substr_count(wtfo_note($parent),'wtfo-price-note')===2,'Kafelek produktu zmiennego podaje odniesienia poszczególnych wariantów');
    $assert(get_role('shop_manager')->has_cap('manage_woocommerce') && !get_role('customer')->has_cap('manage_woocommerce'),'Kierownik sklepu ma dostęp, klient go nie ma');
    $assert(wtfo_decimal('49,90')==='49.900000','Korekta obsługuje przecinek dziesiętny');
    WP_CLI::success('Wszystkie testy WTF Omnibus zakończone.');
} finally {
    foreach (array_reverse($created) as $id) wp_delete_post($id,true);
}
