<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu',function () {
    add_submenu_page('edit.php?post_type=product','WTF Omnibus — Historia cen','Historia cen','manage_woocommerce','wtf_omnibus','wtfo_admin');
});

function wtfo_admin_url($args=[]) {
    return add_query_arg(array_merge(['post_type'=>'product','page'=>'wtf_omnibus'],$args),admin_url('edit.php'));
}

function wtfo_decimal($value, $optional=false) {
    if (!is_string($value)) wp_die('Nieprawidłowa cena.');
    $value=trim($value);
    if ($optional && $value==='') return null;
    $value=str_replace(',','.',$value);
    if (!preg_match('/^\d+(?:\.\d{1,6})?$/D',$value)) wp_die('Podaj nieujemną cenę, np. 49,90.');
    return wc_format_decimal($value,6);
}

function wtfo_parse_date($value) {
    if (!is_string($value)) wp_die('Nieprawidłowa data.');
    $format=strlen($value)===19?'Y-m-d\TH:i:s':'Y-m-d\TH:i';
    $date=DateTimeImmutable::createFromFormat('!'.$format,$value,wp_timezone());
    if (!$date || $date->format($format)!==$value || $date->getTimestamp()>time()) wp_die('Podaj poprawną datę, nie późniejszą niż teraz.');
    return $date->getTimestamp();
}

add_action('admin_post_wtfo_save',function () {
    if (!current_user_can('manage_woocommerce')) wp_die('Brak uprawnień.', '', ['response'=>403]);
    check_admin_referer('wtfo_save');
    if (!function_exists('wc_get_product') || get_woocommerce_currency()!=='PLN') wp_die('Wymagane WooCommerce i waluta PLN.');
    $action=sanitize_key($_POST['operation']??'');
    $id=absint($_POST['product_id']??0);
    if ($action==='initialize_all') {
        wtfo_initialise_missing();
    } else {
        $product=wc_get_product($id);
        if (!wtfo_supported($product) || !current_user_can('edit_post',$id)) wp_die('Nieprawidłowy produkt lub brak uprawnień.', '', ['response'=>403]);
        $state=wtfo_state($id);
        if ($action==='save') {
            $seed=wtfo_decimal(wp_unslash($_POST['seed']??''),true);
            if (!$state) {
                wtfo_initialize($product,$seed);
            } else {
                $state['seed']=$seed ?? wc_format_decimal($product->get_price(),6);
                $state['seed_manual']=$seed!==null;
                if (!empty($state['sale'])) {
                    $state['reference']=wtfo_decimal(wp_unslash($_POST['reference']??''),true) ?? $state['seed'];
                    $state['start']=wtfo_parse_date(wp_unslash($_POST['started']??''));
                    $state['manual_reference']=true;
                }
                update_post_meta($id,'_wtfo_state',$state);
            }
        } elseif ($action==='event' && $state) {
            global $wpdb;
            $event=absint($_POST['event_id']??0);
            $price=wtfo_decimal(wp_unslash($_POST['price']??''));
            $at=wtfo_parse_date(wp_unslash($_POST['recorded_at']??''));
            if ($at<$state['since']) wp_die('Obserwacja nie może poprzedzać rozpoczęcia rejestrowania. Do wcześniejszych danych służy wartość początkowa.');
            $wpdb->update($wpdb->prefix.'wtfo_prices',['price'=>$price,'recorded_at'=>gmdate('Y-m-d H:i:s',$at)],['id'=>$event,'product_id'=>$id],['%s','%s'],['%d','%d']);
            if (!empty($state['sale']) && empty($state['manual_reference'])) {
                $state['reference']=wtfo_reference($product,$state['start'],$state);
                update_post_meta($id,'_wtfo_state',$state);
            }
        } else wp_die('Nieznana operacja.');
    }
    wp_safe_redirect(wtfo_admin_url(['updated'=>1]+(!empty($_POST['detail'])?['product_id'=>$id]:[])));
    exit;
});

function wtfo_form_open($id,$operation,$detail=false) {
    echo '<form class="wtfo-form" action="'.esc_url(admin_url('admin-post.php')).'" method="post">';
    wp_nonce_field('wtfo_save');
    echo '<input type="hidden" name="action" value="wtfo_save"><input type="hidden" name="operation" value="'.esc_attr($operation).'"><input type="hidden" name="product_id" value="'.absint($id).'">';
    if ($detail) echo '<input type="hidden" name="detail" value="1">';
}

function wtfo_date($timestamp) {
    return wp_date('Y-m-d\TH:i:s',$timestamp,wp_timezone());
}

function wtfo_input_price($price) {
    return $price===null ? '' : wc_format_localized_price(wc_format_decimal($price,wc_get_price_decimals()));
}

function wtfo_admin() {
    if (!current_user_can('manage_woocommerce')) return;
    echo '<div class="wrap wtfo-admin"><h1>WTF Omnibus — Historia cen</h1>';
    if (!function_exists('wc_get_product')) { echo '<p>Aktywuj WooCommerce.</p></div>'; return; }
    if (get_woocommerce_currency()!=='PLN') { echo '<p>Ta wersja obsługuje wyłącznie PLN.</p></div>'; return; }
    wp_enqueue_style('wtfo-admin',plugins_url('../assets/admin.css',__FILE__),[],'0.1.0');
    if (isset($_GET['updated'])) echo '<div class="notice notice-success is-dismissible"><p>Zapisano.</p></div>';
    $detail=absint($_GET['product_id']??0);
    if ($detail) { wtfo_admin_detail($detail); echo '</div>'; return; }
    echo '<p>Historia cen produktów i wariantów. Wartość początkowa uzupełnia okres sprzed rozpoczęcia rejestrowania; nie jest odtworzoną historią. Korekty nie zmieniają cen sprzedaży w WooCommerce.</p>';
    wtfo_form_open(0,'initialize_all');
    echo '<button class="button" type="submit">Zainicjalizuj brakujące bieżącą ceną</button> <span>Istniejące wpisy pozostaną bez zmian. Początek obecnych obniżek: 14 dni temu.</span></form>';
    $search=isset($_GET['s'])&&is_string($_GET['s'])?sanitize_text_field(wp_unslash($_GET['s'])):'';
    echo '<form method="get" class="wtfo-search"><input type="hidden" name="post_type" value="product"><input type="hidden" name="page" value="wtf_omnibus"><label for="wtfo-search">Szukaj produktu lub wariantu </label><input id="wtfo-search" type="search" name="s" value="'.esc_attr($search).'"><button class="button">Szukaj</button></form>';
    $page=max(1,absint($_GET['paged']??1));
    $query=new WP_Query(['post_type'=>['product','product_variation'],'post_status'=>['publish','private'],'posts_per_page'=>20,'paged'=>$page,'s'=>$search,'orderby'=>'title','order'=>'ASC']);
    echo '<div class="wtfo-table"><table class="widefat striped"><thead><tr><th>Produkt / wariant</th><th>Cena sprzedaży</th><th>Status</th><th>Inicjalizacja / korekta (PLN)</th></tr></thead><tbody>';
    foreach ($query->posts as $post) {
        $product=wc_get_product($post->ID);
        if (!$product) continue;
        echo '<tr><td><a href="'.esc_url(wtfo_admin_url(['product_id'=>$post->ID])).'"><strong>'.esc_html($product->get_name()).'</strong></a><br><small>#'.absint($post->ID).' · '.esc_html($product->get_sku()).'</small></td><td>'.wp_kses_post(wc_price((float)$product->get_price())).'</td>';
        if ($product->is_type('variable')) { echo '<td>Osobna historia każdego wariantu</td><td><a href="'.esc_url(get_edit_post_link($post->ID)).'">Edytuj warianty produktu</a></td></tr>'; continue; }
        if (!wtfo_supported($product)) { echo '<td colspan="2">Nieobsługiwany typ produktu</td></tr>'; continue; }
        $state=wtfo_state($post->ID);
        echo '<td>';
        if (!$state) echo 'Do inicjalizacji';
        else {
            echo !empty($state['sale'])?'Obniżka':'Cena zwykła';
            echo '<br><small>Rejestracja od '.esc_html(wp_date('d.m.Y H:i',$state['since'])).'<br>Wartość początkowa: '.(!empty($state['seed_manual'])?'ręczna':'bieżąca przy inicjalizacji').'</small>';
        }
        echo '</td><td>';
        wtfo_form_open($post->ID,'save');
        echo '<label>Wartość początkowa <input type="text" inputmode="decimal" name="seed" value="'.esc_attr(wtfo_input_price($state['seed']??null)).'" placeholder="Bieżąca cena"></label>';
        if (!empty($state['sale'])) {
            echo '<label>Najniższa przed obniżką <input type="text" inputmode="decimal" name="reference" value="'.esc_attr(wtfo_input_price($state['reference'])).'"></label><label>Początek obniżki <input type="datetime-local" step="1" name="started" value="'.esc_attr(wtfo_date($state['start'])).'" required></label>';
        }
        echo '<button class="button button-primary">'.($state?'Zapisz korektę':'Zainicjalizuj').'</button></form></td></tr>';
    }
    if (!$query->posts) echo '<tr><td colspan="4">Brak produktów.</td></tr>';
    echo '</tbody></table></div>';
    echo '<div class="tablenav"><div class="tablenav-pages">'.wp_kses_post(paginate_links(['base'=>add_query_arg('paged','%#%',wtfo_admin_url(['s'=>$search])),'format'=>'','current'=>$page,'total'=>$query->max_num_pages])).'</div></div></div>';
}

function wtfo_admin_detail($id) {
    $product=wc_get_product($id);
    if (!$product) { echo '<p>Nie znaleziono produktu.</p>'; return; }
    echo '<p><a href="'.esc_url(wtfo_admin_url()).'">Lista produktów</a></p><h2>'.esc_html($product->get_name()).'</h2>';
    if ($product->is_type('variable')) {
        echo '<ul>';
        foreach ($product->get_children() as $child) {
            $variant=wc_get_product($child);
            if ($variant) echo '<li><a href="'.esc_url(wtfo_admin_url(['product_id'=>$child])).'">'.esc_html($variant->get_name()).'</a></li>';
        }
        echo '</ul>'; return;
    }
    $state=wtfo_state($id);
    if (!$state) { echo '<p>Najpierw zainicjalizuj produkt na liście.</p>'; return; }
    echo '<p>Zarejestrowane ceny sprzedaży w PLN. Ręczne korekty nadpisują wpis bez rejestru zmian. Zmiana historii nie zmienia aktualnej ceny produktu.</p>';
    global $wpdb;
    $page=max(1,absint($_GET['paged']??1));
    $table=$wpdb->prefix.'wtfo_prices';
    $events=$wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE product_id=%d ORDER BY recorded_at DESC,id DESC LIMIT 50 OFFSET %d",$id,($page-1)*50));
    echo '<table class="widefat striped"><thead><tr><th>Data i cena sprzedaży (czas sklepu)</th></tr></thead><tbody>';
    foreach ($events as $event) {
        echo '<tr><td>'; wtfo_form_open($id,'event',true);
        echo '<input type="hidden" name="event_id" value="'.absint($event->id).'"><label>Data <input type="datetime-local" step="1" name="recorded_at" value="'.esc_attr(wtfo_date(strtotime($event->recorded_at.' UTC'))).'" required></label><label>Cena (PLN) <input name="price" inputmode="decimal" value="'.esc_attr(wtfo_input_price($event->price)).'" required></label><button class="button">Zapisz korektę</button></form></td></tr>';
    }
    echo '</tbody></table>';
    $count=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE product_id=%d",$id));
    echo wp_kses_post(paginate_links(['base'=>add_query_arg('paged','%#%',wtfo_admin_url(['product_id'=>$id])),'format'=>'','current'=>$page,'total'=>(int)ceil($count/50)]));
}
