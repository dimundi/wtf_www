<?php
if (!defined('ABSPATH')) { exit; }

function wtfg_page_start($title) {
    wtfg_require_admin();
    echo '<div class="wrap"><h1>'.esc_html($title).'</h1>';
    $campaign=wtfg_selected_campaign();
    if ($campaign) { echo '<p><strong>'.esc_html(get_the_title($campaign)).'</strong></p>'; }
    else { echo '<p>Wybierz zbiórkę na ekranie <a href="'.esc_url(admin_url('admin.php?page=wtfg')).'">Zbiórki</a>.</p>'; }
    return $campaign;
}

function wtfg_data_links($mode='all') {
    $base=admin_url('admin.php?page=wtfg-orders');
    echo '<p><a href="'.esc_url(add_query_arg('view','log',$base)).'">Dziennik importów</a> · <a href="'.esc_url(add_query_arg(['view'=>'import','mode'=>$mode],$base)).'">Importuj dane</a></p>';
}

function wtfg_import_status($status) {
    return ['preview'=>'Zmiany do zatwierdzenia','applied'=>'Zakończony','failed'=>'Błąd','expired'=>'Podgląd wygasł','manual'=>'Dodano ręcznie'][$status] ?? $status;
}

function wtfg_pagination($total,$size,$page,$param='paged') {
    echo wp_kses_post(paginate_links(['base'=>add_query_arg($param,'%#%'),'current'=>$page,'total'=>max(1,ceil($total/$size))]));
}

function wtfg_field_labels() {
    return wtfg_supporter_fields()+['name'=>'Nazwa','variants'=>'Warianty','public_id'=>'Numer publiczny','user_external_id'=>'UserID','stage'=>'Status zamówienia','payment_status'=>'Status płatności','currency'=>'Waluta','total_amount'=>'Kwota z wysyłką','total_paid'=>'Zapłacono','description'=>'Oryginalny opis','order_id'=>'Powiązanie zamówienia','variant_id'=>'Powiązanie wariantu','status'=>'Stan powiązania'];
}

function wtfg_value($value) {
    if (is_array($value)) { return esc_html(implode(' | ',array_map(fn($v)=>$v===''?'(podstawowy)':(string)$v,$value))); }
    return $value===null || $value===''?'—':esc_html((string)$value);
}

function wtfg_items_table($lines) {
    $labels=wtfg_item_labels();
    echo '<table class="widefat striped"><thead><tr><th>Element źródłowy / ID</th><th>Wariant</th><th>Ilość</th><th>UserID</th><th>Powiązanie</th></tr></thead><tbody>';
    foreach ($lines as $line) {
        echo '<tr><td>'.esc_html($line['source']).'<br>ID: '.esc_html($line['external_product_id'] ?: '—').'</td><td>'.wtfg_value($line['variant']).'</td><td>'.wtfg_value($line['quantity'] ?? $line['quantity_raw']).'</td><td>'.wtfg_value($line['external_user_id']).'</td><td>'.esc_html($labels[$line['status']] ?? $line['status']).'<br><small>Zamówienie: '.wtfg_value($line['order_id']).', wariant: '.wtfg_value($line['variant_id']).'</small></td></tr>';
    }
    if (!$lines) { echo '<tr><td colspan="5">Brak elementów.</td></tr>'; }
    echo '</tbody></table>';
}

function wtfg_import_page($campaign) {
    global $wpdb;
    $mode=in_array($_GET['mode'] ?? '',['customers','products'],true)?$_GET['mode']:'all';
    echo '<p><a href="'.esc_url(admin_url('admin.php?page=wtfg-orders')).'">Wróć do zamówień</a></p><h2>Import danych zbiórki</h2>';
    echo '<p>Nowe poprawne rekordy zapisują się automatycznie. Każda zmiana istniejących danych wymaga osobnego zaznaczenia i&nbsp;zatwierdzenia. Brak rekordu w&nbsp;pliku nie usuwa go z&nbsp;bazy.</p>';
    echo '<form method="post" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'">';
    wp_nonce_field('wtfg_import_'.$campaign);
    echo '<input type="hidden" name="action" value="wtfg_import"><input type="hidden" name="campaign_id" value="'.(int)$campaign.'"><input type="hidden" name="mode" value="'.esc_attr($mode).'">';
    if ($mode==='all') {
        echo '<p><label><strong>Orders — zamówienia i&nbsp;klienci</strong><br><input type="file" name="orders_csv" accept=".csv,text/csv"></label><br>Wymagane: ProjectID, OrderID, UserID, ProductsOrdered. Dane klientów odczytujemy, jeśli są kolumny CustomerFullName i&nbsp;CustomerEmail. Opis ProductsOrdered zachowujemy 1:1, bez rozbijania na produkty.</p>';
    }
    $description=['all'=>'Order items — produkty, warianty i elementy do realizacji','customers'=>'Order items — tylko wspierający','products'=>'Order items — tylko katalog produktów i wariantów'][$mode];
    echo '<p><label><strong>'.esc_html($description).'</strong><br><input type="file" name="items_csv" accept=".csv,text/csv"></label></p>';
    if ($mode==='all') {
        echo '<p>Order items wymaga ProjectID, OrderID, UserID, ProductName, Quantity. Każdy wiersz jest osobną pozycją. Możesz przesłać oba pliki razem albo tylko jeden.</p><p><label><input type="checkbox" name="complete_orders" value="1"> Order items zawiera komplet elementów zamówień wymienionych w&nbsp;pliku.</label></p>';
    } elseif ($mode==='customers') { echo '<p>Wymagane: ProjectID, UserID, CustomerFullName, CustomerEmail. Import nie tworzy kont WordPressa ani zamówień.</p>'; }
    else { echo '<p>Wymagana kolumna ProductName; pozostałe mogą występować. ProjectID, jeśli występuje, ogranicza dane do tej zbiórki. Import nie tworzy zamówień.</p>'; }
    echo '<p><strong>CSV rozdzielany przecinkiem</strong>, UTF-8 lub UTF-16, do 8 MB na plik.</p><button class="button button-primary">Importuj dane</button></form>';
    $run=absint($_GET['import_id'] ?? 0);
    if (!$run) { return; }
    $row=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.wtfg_table('imports').' WHERE id=%d AND campaign_id=%d AND user_id=%d',$run,$campaign,get_current_user_id()),ARRAY_A);
    if (!$row) { echo '<p>Nie znaleziono Twojego importu.</p>'; return; }
    $report=json_decode($row['report'],true) ?: [];
    echo '<h2>Import #'.(int)$run.' — '.esc_html(wtfg_import_status($row['status'])).'</h2>';
    if (isset($report['error'])) { echo '<p>'.esc_html($report['error']).'</p>'; }
    foreach ($report['apply_errors'] ?? [] as $error) { echo '<p>Błąd zatwierdzania: '.esc_html($error['message']).'</p>'; }
    if (isset($report['automatic'])) {
        $c=$report['automatic'];
        echo '<p>Dodano automatycznie: klienci '.(int)$c['customers'].', produkty '.(int)$c['products'].', zamówienia '.(int)$c['orders'].', elementy '.(int)$c['items'].'. Zatwierdzone aktualizacje: '.(int)$report['confirmed'].'. Oczekujące zmiany rekordów: '.(int)$report['pending'].'.</p>';
    }
    foreach ($report['warnings'] ?? [] as $source=>$warnings) {
        echo '<p>'.esc_html($source).': pominięte wiersze '.count($warnings['skipped'] ?? []).', konflikty '.count($warnings['conflicts'] ?? []).'.</p>';
        if (!empty($warnings['skipped']) || !empty($warnings['conflicts'])) {
            echo '<details><summary>Szczegóły ostrzeżeń</summary><ul>';
            foreach ($warnings['skipped'] ?? [] as $warning) { echo '<li>Wiersz '.(int)$warning['record'].': '.esc_html($warning['reason']).'</li>'; }
            foreach ($warnings['conflicts'] ?? [] as $id=>$fields) { echo '<li>Konflikt ID: '.esc_html(is_array($fields)?(string)$id:(string)$fields).'</li>'; }
            echo '</ul></details>';
        }
    }
    if ($row['status']!=='preview') { return; }
    try { $payload=wtfg_supporter_decrypt($row['payload']); }
    catch (Throwable $error) { echo '<p>Podgląd jest niedostępny. Wczytaj plik ponownie.</p>'; return; }
    if (!hash_equals($row['snapshot_hash'],wtfg_state_hash($campaign,wtfg_state($campaign)))) {
        echo '<p><strong>Dane zmieniły się od podglądu. Wczytaj plik ponownie. Nie można zastosować starego potwierdzenia.</strong></p>'; return;
    }
    $page=max(1,absint($_GET['diff_page'] ?? 1)); $pending=$payload['pending']; $labels=wtfg_field_labels();
    echo '<h3>Zmiany istniejących rekordów — nic nie jest zaznaczone automatycznie</h3><form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    wp_nonce_field('wtfg_confirm_'.$run);
    echo '<input type="hidden" name="action" value="wtfg_confirm"><input type="hidden" name="campaign_id" value="'.(int)$campaign.'"><input type="hidden" name="import_id" value="'.(int)$run.'">';
    $entities=['customers'=>'Klient','products'=>'Produkt','orders'=>'Zamówienie','items'=>'Elementy zamówienia'];
    echo '<p><label><input type="checkbox" data-wtfg-select-all> <strong>Zaznacz wszystkie zmiany na tej stronie</strong></label></p>';
    foreach (array_slice($pending,($page-1)*10,10,true) as $index=>$entry) {
        $number=$entry['entity']==='orders'?($entry['after']['public_id'] ?? $entry['before']['public_id'] ?? ''):'';
        echo '<section class="wtfg-change-card"><h3>'.esc_html($entities[$entry['entity']].($number!==''?' '.$number:'').' — ID Gamefound: '.$entry['key']).'</h3>';
        if ($entry['entity']==='items') {
            wtfg_item_diff($entry['before'],$entry['after']);
        } else {
            wtfg_record_diff($entry,$labels);
        }
        echo '<p><label><input type="checkbox" name="approve[]" value="'.(int)$index.'"> Zatwierdzam wszystkie pokazane powyżej zmiany tego rekordu.</label></p></section>';
    }
    echo '<p>Niezaznaczone rekordy pozostają bez zmian. Jeśli zatwierdzony produkt lub zamówienie zmieni propozycję powiązań, pokażemy ją do kolejnego potwierdzenia.</p><button class="button button-primary">Zastosuj wyłącznie zaznaczone zmiany</button></form>';
    wtfg_pagination(count($pending),10,$page,'diff_page');
}

function wtfg_log_page($campaign) {
    global $wpdb; $page=max(1,absint($_GET['paged'] ?? 1));
    echo '<p><a href="'.esc_url(admin_url('admin.php?page=wtfg-orders')).'">Wróć do zamówień</a></p><h2>Dziennik importów i&nbsp;zmian</h2>';
    $count=(int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.wtfg_table('imports').' WHERE campaign_id=%d',$campaign));
    $rows=$wpdb->get_results($wpdb->prepare('SELECT id,user_id,created_at,status,report FROM '.wtfg_table('imports').' WHERE campaign_id=%d ORDER BY id DESC LIMIT 50 OFFSET %d',$campaign,($page-1)*50),ARRAY_A);
    echo '<table class="widefat striped"><thead><tr><th>Import</th><th>Administrator / czas UTC</th><th>Stan</th><th>Wynik</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $report=json_decode($row['report'],true) ?: [];
        $summary=$report['error'] ?? ('Nowe rekordy/elementy: '.array_sum($report['automatic'] ?? []).', zatwierdzone aktualizacje: '.($report['confirmed'] ?? 0).', oczekujące: '.($report['pending'] ?? 0));
        if (!empty($report['apply_errors'])) { $last=end($report['apply_errors']); $summary.='. Błąd zatwierdzania: '.$last['message']; }
        echo '<tr><td><a href="'.esc_url(add_query_arg(['view'=>'import','import_id'=>$row['id']],admin_url('admin.php?page=wtfg-orders'))).'">#'.(int)$row['id'].'</a></td><td>'.(int)$row['user_id'].' / '.esc_html($row['created_at']).'</td><td>'.esc_html(wtfg_import_status($row['status'])).'</td><td>'.esc_html($summary).'</td></tr>';
    }
    echo '</tbody></table>'; wtfg_pagination($count,50,$page);
    $changes=$wpdb->get_results($wpdb->prepare('SELECT import_id,user_id,entity,external_id,operation,fields,created_at FROM '.wtfg_table('changes').' WHERE campaign_id=%d ORDER BY id DESC LIMIT 100',$campaign),ARRAY_A);
    echo '<h3>Ostatnie 100 zastosowanych zmian</h3><table class="widefat striped"><thead><tr><th>Import / czas UTC</th><th>Administrator</th><th>Rekord</th><th>Operacja / pola</th></tr></thead><tbody>';
    foreach ($changes as $change) { echo '<tr><td>#'.(int)$change['import_id'].' / '.esc_html($change['created_at']).'</td><td>'.(int)$change['user_id'].'</td><td>'.esc_html($change['entity'].' / '.$change['external_id']).'</td><td>'.esc_html($change['operation'].' / '.implode(', ',json_decode($change['fields'],true) ?: [])).'</td></tr>'; }
    echo '</tbody></table>';
}

function wtfg_orders_page() {
    $campaign=wtfg_page_start('Zamówienia');
    if (!$campaign) { echo '</div>'; return; }
    if (($_GET['view'] ?? '')==='import') { wtfg_import_page($campaign); echo '</div>'; return; }
    if (($_GET['view'] ?? '')==='log') { wtfg_log_page($campaign); echo '</div>'; return; }
    wtfg_data_links(); global $wpdb;
    $search=isset($_GET['order_number']) && is_string($_GET['order_number'])?trim(sanitize_text_field(wp_unslash($_GET['order_number']))):'';
    $stage=isset($_GET['order_stage']) && is_string($_GET['order_stage'])?sanitize_text_field(wp_unslash($_GET['order_stage'])):'';
    $customer=in_array($_GET['customer_type'] ?? '',['company','private','unknown'],true)?$_GET['customer_type']:'';
    $stages=$wpdb->get_col($wpdb->prepare('SELECT DISTINCT stage FROM '.wtfg_table('orders').' WHERE campaign_id=%d ORDER BY stage',$campaign));
    echo '<form method="get"><input type="hidden" name="page" value="wtfg-orders"><p><label>Numer zamówienia (OrderPublicID) <input type="search" name="order_number" value="'.esc_attr($search).'"></label> <label>Status <select name="order_stage"><option value="">Wszystkie statusy</option>';
    foreach ($stages as $value) { $filter=$value===''?'__missing__':$value; echo '<option value="'.esc_attr($filter).'" '.selected($stage,$filter,false).'>'.esc_html(wtfg_stage_label($value)).'</option>'; }
    echo '</select></label> <label>Klient <select name="customer_type">';
    foreach ([''=>'Wszyscy klienci','company'=>'Firmy','private'=>'Osoby prywatne','unknown'=>'Brak danych'] as $value=>$label) { echo '<option value="'.esc_attr($value).'" '.selected($customer,$value,false).'>'.esc_html($label).'</option>'; }
    echo '</select></label> <button class="button">Szukaj / filtruj</button> <a href="'.esc_url(admin_url('admin.php?page=wtfg-orders')).'">Wyczyść filtry</a></p></form>';
    $where='o.campaign_id=%d'; $args=[$campaign];
    if ($search!=='') { $where.=' AND LOWER(o.public_id) LIKE %s'; $args[]='%'.$wpdb->esc_like(strtolower($search)).'%'; }
    if ($stage!=='') { $where.=' AND o.stage=%s'; $args[]=$stage==='__missing__'?'':$stage; }
    if ($customer!=='') { $where.=['company'=>' AND c.is_business=1','private'=>' AND c.is_business=0','unknown'=>' AND c.is_business IS NULL'][$customer]; }
    $from=wtfg_table('orders').' o LEFT JOIN '.wtfg_table('customers').' c ON c.campaign_id=o.campaign_id AND c.external_id=o.user_external_id';
    $count=(int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.$from.' WHERE '.$where,$args));
    $page=max(1,absint($_GET['paged'] ?? 1));
    $rows=$wpdb->get_results($wpdb->prepare('SELECT o.*,c.full_name,c.is_business FROM '.$from.' WHERE '.$where.' ORDER BY o.id DESC LIMIT 25 OFFSET %d',array_merge($args,[($page-1)*25])),ARRAY_A);
    $grouped=[];
    if ($rows) {
        $ids=array_column($rows,'external_id'); $marks=implode(',',array_fill(0,count($ids),'%s'));
        $items=$wpdb->get_results($wpdb->prepare('SELECT * FROM '.wtfg_table('order_items')." WHERE campaign_id=%d AND external_order_id IN ($marks) ORDER BY id",array_merge([$campaign],$ids)),ARRAY_A);
        foreach ($items as $item) { $grouped[$item['external_order_id']][]=$item; }
    }
    echo '<h2>Zamówienia: '.(int)$count.'</h2><table class="widefat striped"><thead><tr><th>Numer / status</th><th>Wspierający</th><th>Oryginalny opis i&nbsp;elementy do realizacji</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr data-wtfg-order-id="'.esc_attr($row['external_id']).'" data-wtfg-order-number="'.esc_attr($row['public_id']).'"><td><strong>'.wtfg_value($row['public_id']).'</strong><br><small>OrderID: '.esc_html($row['external_id']).'</small><br>Status: '.esc_html(wtfg_stage_label($row['stage'])).'<br>Płatność: '.wtfg_value($row['payment_status']).'<br>Kwota: '.wtfg_value($row['total_amount']).' '.esc_html($row['currency']).'<br>Zapłacono: '.wtfg_value($row['total_paid']).'</td><td><strong>'.esc_html($row['full_name'] ?: 'Brak danych klienta').'</strong><br>UserID: '.esc_html($row['user_external_id']);
        if ((string)$row['is_business']==='1') { echo '<br><strong>Firma</strong>'; }
        echo '</td><td><strong>Oryginalny opis zamówienia:</strong><div style="white-space:pre-wrap;overflow-wrap:anywhere">'.esc_html($row['description']).'</div><details><summary>Elementy do realizacji (Order items): '.count($grouped[$row['external_id']] ?? []).'</summary>';
        wtfg_items_table($grouped[$row['external_id']] ?? []); echo '</details></td></tr>';
    }
    if (!$rows) { echo '<tr><td colspan="3">Nie znaleziono zamówień.</td></tr>'; }
    echo '</tbody></table>'; wtfg_pagination($count,25,$page);
    $errors=$wpdb->get_results($wpdb->prepare("SELECT * FROM ".wtfg_table('order_items')." WHERE campaign_id=%d AND status<>'matched' ORDER BY id LIMIT 100",$campaign),ARRAY_A);
    if ($errors) { echo '<h3>Elementy wymagające uzupełnienia — pierwsze 100</h3><p>Obejmuje też elementy bez znalezionego zamówienia. Popraw dane i&nbsp;ponów import; zmiany powiązań będą wymagały potwierdzenia.</p>'; wtfg_items_table($errors); }
    echo '</div>';
}

function wtfg_customers_page() {
    $campaign=wtfg_page_start('Wspierający');
    if (!$campaign) { echo '</div>'; return; }
    wtfg_data_links('customers'); global $wpdb;
    $page=max(1,absint($_GET['paged'] ?? 1));
    $count=(int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.wtfg_table('customers').' WHERE campaign_id=%d',$campaign));
    $rows=$wpdb->get_results($wpdb->prepare('SELECT external_id,data,is_business FROM '.wtfg_table('customers').' WHERE campaign_id=%d ORDER BY id DESC LIMIT 25 OFFSET %d',$campaign,($page-1)*25),ARRAY_A);
    echo '<h2>Wspierający: '.(int)$count.'</h2><table class="widefat striped"><thead><tr><th>UserID</th><th>Klient</th><th>Dane klienta</th></tr></thead><tbody>';
    foreach ($rows as $row) {
        $data=json_decode($row['data'],true) ?: [];
        echo '<tr><td>'.esc_html($row['external_id']).'</td><td><strong>'.esc_html($data['CustomerFullName'] ?? '').'</strong>';
        if ((string)$row['is_business']==='1') { echo '<br><strong>Firma</strong>'; }
        echo '<br>'.esc_html($data['CustomerEmail'] ?? '').'</td><td><details><summary>Szczegóły</summary>';
        foreach (wtfg_supporter_fields() as $field=>$label) { if (($data[$field] ?? '')!=='') { echo '<p>'.esc_html($label).': '.esc_html($data[$field]).'</p>'; } }
        echo '</details></td></tr>';
    }
    if (!$rows) { echo '<tr><td colspan="3">Brak wspierających.</td></tr>'; }
    echo '</tbody></table>'; wtfg_pagination($count,25,$page); echo '</div>';
}

function wtfg_product_sales($campaign) {
    global $wpdb;
    $from=wtfg_table('order_items').' i LEFT JOIN '.wtfg_table('orders').' o ON o.id=i.order_id AND o.campaign_id=i.campaign_id LEFT JOIN '.wtfg_table('variants').' v ON v.id=i.variant_id AND v.campaign_id=i.campaign_id LEFT JOIN '.wtfg_table('products').' p ON p.id=v.product_id AND p.campaign_id=i.campaign_id';
    $canceled="LOWER(TRIM(COALESCE(o.stage,''))) IN ('canceled','cancelled')";
    $valid="i.status='matched' AND o.id IS NOT NULL AND v.id IS NOT NULL AND p.id IS NOT NULL AND i.quantity>0 AND (o.user_external_id='' OR o.user_external_id=i.external_user_id) AND p.external_id=i.external_product_id AND BINARY v.name=BINARY i.variant";
    $rows=$wpdb->get_results($wpdb->prepare('SELECT i.variant_id,SUM(i.quantity) AS quantity FROM '.$from.' WHERE i.campaign_id=%d AND NOT ('.$canceled.') AND ('.$valid.') GROUP BY i.variant_id',$campaign),ARRAY_A); wtfg_query_ok($rows);
    $summary=$wpdb->get_row($wpdb->prepare('SELECT COUNT(*) AS imported_rows,COALESCE(SUM(CASE WHEN NOT ('.$canceled.') AND NOT ('.$valid.') THEN 1 ELSE 0 END),0) AS invalid_rows FROM '.$from.' WHERE i.campaign_id=%d',$campaign),ARRAY_A); wtfg_query_ok($summary);
    return ['variants'=>array_map('intval',array_column($rows,'quantity','variant_id')),'total'=>array_sum(array_column($rows,'quantity')),'imported_rows'=>(int)$summary['imported_rows'],'invalid_rows'=>(int)$summary['invalid_rows']];
}

function wtfg_products_page() {
    $campaign=wtfg_page_start('Produkty');
    if (!$campaign) { echo '</div>'; return; }
    wtfg_data_links('products'); global $wpdb;
    echo '<p><a href="#wtfg-product-add" data-wtfg-import-toggle aria-controls="wtfg-product-add" aria-expanded="false">Dodaj produkt ręcznie</a></p>';
    if (isset($_GET['product_created'])) { echo '<p>Produkt dodany. Ponów import, aby zobaczyć propozycje uzupełnienia brakujących powiązań.</p>'; }
    echo '<div id="wtfg-product-add" hidden><h2>Dodaj produkt ręcznie</h2><form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    wp_nonce_field('wtfg_manual_product_'.$campaign);
    echo '<input type="hidden" name="action" value="wtfg_manual_product"><input type="hidden" name="campaign_id" value="'.(int)$campaign.'"><p><label>ID Gamefound <input required name="external_id" inputmode="numeric" pattern="[0-9]{1,64}" maxlength="64"></label></p><p><label>Nazwa <input required name="product_name" class="large-text" maxlength="2000"></label></p><p><label>Warianty — jeden w&nbsp;wierszu; puste pole oznacza wariant podstawowy.<textarea name="variants" class="large-text" rows="4" maxlength="50000"></textarea></label></p><button class="button">Dodaj produkt</button></form></div>';
    $stats=wtfg_product_sales($campaign);
    echo '<p>Łącznie: <strong>'.(int)$stats['total'].' szt.</strong> Wyłącznie Elementy do realizacji (Order items), bez zamówień anulowanych. Opis Orders nie jest liczony.</p>';
    if (!$stats['imported_rows']) { echo '<p>Brak elementów do realizacji — zaimportuj Order items.</p>'; }
    if ($stats['invalid_rows']) { echo '<p><strong>Pominięte błędne lub niepowiązane elementy: '.(int)$stats['invalid_rows'].'.</strong> Szczegóły na ekranie Zamówienia.</p>'; }
    $page=max(1,absint($_GET['paged'] ?? 1));
    $count=(int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.wtfg_table('products').' WHERE campaign_id=%d',$campaign));
    $products=$wpdb->get_results($wpdb->prepare('SELECT * FROM '.wtfg_table('products').' WHERE campaign_id=%d ORDER BY id LIMIT 50 OFFSET %d',$campaign,($page-1)*50),ARRAY_A);
    $variants=[];
    if ($products) {
        $ids=array_map('intval',array_column($products,'id')); $marks=implode(',',array_fill(0,count($ids),'%d'));
        $rows=$wpdb->get_results($wpdb->prepare('SELECT * FROM '.wtfg_table('variants')." WHERE campaign_id=%d AND product_id IN ($marks) ORDER BY id",array_merge([$campaign],$ids)),ARRAY_A);
        foreach ($rows as $row) { $variants[$row['product_id']][]=$row; }
    }
    echo '<h2>Produkty: '.(int)$count.'</h2><table class="widefat striped"><thead><tr><th>ID Gamefound</th><th>Produkt</th><th>Wariant — liczba sztuk</th><th>Łącznie sztuk</th></tr></thead><tbody>';
    foreach ($products as $product) {
        $total=0; echo '<tr data-wtfg-product-id="'.esc_attr($product['external_id']).'"><td>'.esc_html($product['external_id']).'</td><td>'.esc_html($product['name']).'</td><td>';
        foreach ($variants[$product['id']] ?? [] as $variant) {
            $qty=$stats['variants'][$variant['id']] ?? 0; $total+=$qty;
            echo '<div data-wtfg-variant="'.esc_attr($variant['name']).'" data-wtfg-quantity="'.(int)$qty.'">'.esc_html($variant['name'] ?: '(podstawowy)').': <strong>'.(int)$qty.'</strong></div>';
        }
        echo '</td><td data-wtfg-product-total>'.(int)$total.'</td></tr>';
    }
    if (!$products) { echo '<tr><td colspan="4">Brak produktów.</td></tr>'; }
    echo '</tbody></table>'; wtfg_pagination($count,50,$page); echo '</div>';
}

add_action('admin_post_wtfg_manual_product',function () {
    wtfg_require_admin(); $campaign=absint($_POST['campaign_id'] ?? 0); check_admin_referer('wtfg_manual_product_'.$campaign);
    if (!wtfg_valid_campaign($campaign)) { wp_die('Nieprawidłowa zbiórka.'); }
    global $wpdb; $lock=''; $message='';
    try {
        $scalar=fn($key)=>isset($_POST[$key]) && is_string($_POST[$key])?trim(wp_unslash($_POST[$key])):'';
        $external=$scalar('external_id'); $name=sanitize_text_field($scalar('product_name')); $raw=$scalar('variants');
        if (!preg_match('/^[0-9]{1,64}$/D',$external) || $name==='' || strlen($name)>2000 || strlen($raw)>50000) { throw new RuntimeException('Podaj prawidłowe ID, nazwę i warianty produktu.'); }
        $variants=[];
        foreach (preg_split('/\R/u',$raw) ?: [] as $variant) {
            $variant=preg_replace('/\s+/u',' ',trim(sanitize_text_field($variant)));
            if (strlen($variant)>2000) { throw new RuntimeException('Nazwa wariantu jest zbyt długa.'); }
            if ($variant!=='' && !in_array($variant,$variants,true)) { $variants[]=$variant; }
        }
        if (count($variants)>100) { throw new RuntimeException('Limit: 100 wariantów produktu.'); }
        if (!$variants) { $variants=['']; } sort($variants,SORT_STRING);
        $lock=wtfg_lock(); wtfg_query_ok($wpdb->query('START TRANSACTION'));
        if ($wpdb->get_var($wpdb->prepare('SELECT id FROM '.wtfg_table('products').' WHERE campaign_id=%d AND external_id=%s',$campaign,$external))) { throw new RuntimeException('Produkt o tym ID już istnieje. Nie zmieniono danych.'); }
        wtfg_query_ok(true); wtfg_write($campaign,'products',$external,['name'=>$name,'variants'=>$variants]);
        wtfg_query_ok($wpdb->insert(wtfg_table('imports'),['campaign_id'=>$campaign,'user_id'=>get_current_user_id(),'created_at'=>gmdate('Y-m-d H:i:s'),'finished_at'=>gmdate('Y-m-d H:i:s'),'status'=>'manual','file_hash'=>'','snapshot_hash'=>'','payload'=>'','report'=>wp_json_encode(['automatic'=>['products'=>1],'confirmed'=>0,'pending'=>0])]));
        wtfg_audit($campaign,$wpdb->insert_id,'products',$external,'manual_new',['name','variants']);
        wtfg_query_ok($wpdb->query('COMMIT'));
    } catch (Throwable $error) { $wpdb->query('ROLLBACK'); $message=$error instanceof RuntimeException?$error->getMessage():'Nie można dodać produktu.'; }
    finally { if ($lock) { $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$lock)); } }
    if ($message) { wp_die(esc_html($message),'',['back_link'=>true]); }
    wp_safe_redirect(add_query_arg('product_created',1,admin_url('admin.php?page=wtfg-products'))); exit;
});
