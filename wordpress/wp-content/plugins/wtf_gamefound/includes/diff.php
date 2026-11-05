<?php
if (!defined('ABSPATH')) { exit; }

function wtfg_money_preview($value) {
    if ($value===null || $value==='') { return '—'; }
    if (!preg_match('/^(-?)([0-9]+)(?:\.([0-9]+))?$/D',(string)$value,$m)) { return esc_html((string)$value); }
    $fraction=str_pad(rtrim($m[3] ?? '','0'),2,'0');
    return esc_html($m[1].preg_replace('/\B(?=(\d{3})+(?!\d))/u',"\u{00A0}",$m[2]).','.$fraction);
}

function wtfg_description_diff($before,$after) {
    // Wyłącznie prezentacja. Źródłowy ProductsOrdered i podgląd importu pozostają 1:1.
    $display=fn($text)=>preg_replace('/\s+\+\s+/u',"\n",(string)$text);
    $diff=wp_text_diff($display($before),$display($after),['show_split_view'=>false]);
    echo '<p><strong>Zmiany opisu:</strong> <span class="wtfg-added">Dodano</span> / <span class="wtfg-removed">Usunięto</span></p>';
    if ($diff) { echo $diff; }
    else { echo '<p>Zmieniły się wyłącznie odstępy lub podział tekstu. Sprawdź pełny zapis poniżej.</p>'; }
    echo '<details><summary>Pełny oryginalny zapis przed i&nbsp;po</summary><h4>Przed zmianą</h4><div class="wtfg-source">'.esc_html((string)$before).'</div><h4>Po zmianie</h4><div class="wtfg-source">'.esc_html((string)$after).'</div></details>';
}

function wtfg_item_diff($before,$after) {
    $changed=[];
    foreach (array_unique(array_merge(array_keys($before),array_keys($after))) as $key) {
        if (!isset($before[$key]) || !isset($after[$key]) || !wtfg_same($before[$key],$after[$key])) { $changed[]=$key; }
    }
    echo '<p><strong>Zmienione pozycje: '.count($changed).'.</strong> Pokazujemy tylko różnice.</p>';
    $labels=['source'=>'Nazwa źródłowa','external_product_id'=>'ID produktu','variant'=>'Wariant','quantity'=>'Ilość','quantity_raw'=>'Ilość źródłowa','external_user_id'=>'UserID','order_id'=>'Powiązanie zamówienia','variant_id'=>'Powiązanie wariantu','status'=>'Stan powiązania'];
    $statuses=wtfg_item_labels();
    foreach ($changed as $key) {
        $old=$before[$key] ?? null; $new=$after[$key] ?? null;
        $label=$old===null?'Dodano pozycję':($new===null?'Usunięto pozycję':'Zmieniono pozycję');
        echo '<h4>'.esc_html($label).': '.esc_html(($new ?? $old)['source']).'</h4><table class="widefat wtfg-changes"><thead><tr><th>Pole</th><th>Przed zmianą</th><th>Po zmianie</th></tr></thead><tbody>';
        foreach (wtfg_fields_changed($old,$new ?? []) as $field) {
            if ($field==='quantity_raw' && ($old['quantity'] ?? null)!==null && ($new['quantity'] ?? null)!==null && ($old['quantity'] ?? null)!==($new['quantity'] ?? null)) { continue; }
            $left=$old[$field] ?? null; $right=$new[$field] ?? null;
            if ($field==='status') { $left=$statuses[$left] ?? $left; $right=$statuses[$right] ?? $right; }
            echo '<tr><th>'.esc_html($labels[$field] ?? $field).'</th><td class="wtfg-removed">'.wtfg_value($left).'</td><td class="wtfg-added">'.wtfg_value($right).'</td></tr>';
        }
        echo '</tbody></table>';
    }
    echo '<details><summary>Pełna lista elementów przed i&nbsp;po</summary><h4>Przed zmianą</h4>'; wtfg_items_table($before);
    echo '<h4>Po zmianie</h4>'; wtfg_items_table($after); echo '</details>';
}

function wtfg_record_diff($entry,$labels) {
    $fields=array_values(array_diff($entry['fields'],['description']));
    if ($fields) {
        echo '<table class="widefat wtfg-changes"><thead><tr><th>Pole</th><th>Przed zmianą</th><th>Po zmianie</th></tr></thead><tbody>';
        foreach ($fields as $field) {
            $old=$entry['before'][$field] ?? null; $new=$entry['after'][$field] ?? null;
            $money=in_array($field,['total_amount','total_paid'],true);
            echo '<tr><th>'.esc_html($labels[$field] ?? $field).'</th><td class="wtfg-removed">'.($money?wtfg_money_preview($old):wtfg_value($old)).'</td><td class="wtfg-added">'.($money?wtfg_money_preview($new):wtfg_value($new)).'</td></tr>';
        }
        echo '</tbody></table>';
    }
    if (in_array('description',$entry['fields'],true)) { wtfg_description_diff($entry['before']['description'] ?? '',$entry['after']['description'] ?? ''); }
}
