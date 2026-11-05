<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
// Uruchomienie: php wordpress/wp-content/plugins/wtf_gamefound/tests/products.php
// Bez WordPressa, bazy, plików użytkownika i zewnętrznych połączeń.
define('ABSPATH', __DIR__);
function add_action(...$args) {}
function wp_json_encode($value) { return json_encode($value, JSON_UNESCAPED_UNICODE); }
require dirname(__DIR__).'/includes/csv.php';
require dirname(__DIR__).'/includes/parsers.php';
require dirname(__DIR__).'/includes/data.php';
require dirname(__DIR__).'/includes/import.php';

function check($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
function parse_csv($csv) {
    $file=tempnam(sys_get_temp_dir(),'wtfg-test-');
    try { file_put_contents($file,$csv); return wtfg_parse_products_csv($file); }
    finally { unlink($file); }
}
function rejects($csv) {
    try { parse_csv($csv); } catch (RuntimeException $error) { return; }
    throw new RuntimeException('Nie odrzucono nieprawidłowego CSV.');
}

[$catalog,$rows]=parse_csv("\xEF\xBB\xBFProductName,Ignored\r\n\"Kości, sakiewka [126163] (Czarne)\",x\r\n\"Kości, sakiewka [126163] (Czarne)\",y\r\n\"Kości, sakiewka [126163] (Czarno - niebieskie)\",z\r\nFree Gift: Free Gift: Metalowa Kość [117894],x\r\nMetalowa Kość [117894] (Czarny),x\r\n");
check($rows===5 && count($catalog)===2,'Nie scalono powtórzeń.');
check($catalog[126163]['name']==='Kości, sakiewka','Błąd cytowania przecinka w nazwie.');
check(count($catalog[126163]['variants'])===2,'Nie wykryto wariantów.');
check($catalog[117894]['name']==='Metalowa Kość','Nie usunięto Free Gift.');
check(in_array('',$catalog[117894]['variants'],true),'Utracono wpis bez wariantu.');
$state=['customers'=>[],'products'=>$catalog,'orders'=>[],'items'=>[]];
$packet=['customers'=>[],'products'=>$catalog,'orders'=>[],'items'=>[]];
check(wtfg_candidates($state,$packet)===[],'Identyczny import pokazuje zmiany.');
$packet['products'][126163]['name']='Nowa nazwa';
$packet['products'][126163]['variants']=['Zielone'];
unset($packet['products'][117894]);
$packet['products'][76458]=['name'=>'Podręcznik','variants'=>['']];
$plan=wtfg_candidates($state,$packet);
check(count($plan)===2,'Nieprawidłowa liczba propozycji.');
check($plan[0]['before']!==null && $plan[1]['before']===null,'Nie rozróżniono zmiany istniejącego i nowego produktu.');
check(count($plan[0]['after']['variants'])===3,'Usunięto stare warianty z niepełnego eksportu.');
rejects("ProductName;Quantity\nProdukt [1];1\n");
rejects("ProductName,ProductName\nProdukt [1],Produkt [1]\n");
rejects("ProductName\nBrak ID\n");
rejects("ProductName\nProdukt [1]\nInna nazwa [1]\n");
rejects("ProductName\n");
rejects("ProductName\nNiepoprawny UTF-8 \xFF [1]\n");
[$extra]=parse_csv("Quantity,ProductName,Ignored\n1,Produkt [1],x,additional\n2,Produkt [1]\n");
check(count($extra)===1,'Pozostałe kolumny blokują import.');
[$utf16]=parse_csv("\xFF\xFE".iconv('UTF-8','UTF-16LE',"ProductName,Quantity\r\nProdukt [1],1\r\n"));
check($utf16[1]['name']==='Produkt','Błąd CSV UTF-16LE.');
[$utf16be]=parse_csv("\xFE\xFF".iconv('UTF-8','UTF-16BE',"Quantity,ProductName\n1,Produkt [1]\n"));
check($utf16be[1]['name']==='Produkt','Błąd CSV UTF-16BE.');
[$quoted]=parse_csv("\xEF\xBB\xBF\"ProductName\",Quantity\nProdukt [1],1\n");
check($quoted[1]['name']==='Produkt','Błąd BOM przed cytowanym nagłówkiem.');
[$valid,$total,$skipped]=parse_csv("ProductName\nProdukt [1]\nNieprawidłowy wpis\nInny produkt [2]\n");
check(count($valid)===2 && $total===3,'Błędny rekord zablokował poprawne produkty.');
check(count($skipped)===1 && $skipped[0]['record']===3,'Brak numeru pominiętego rekordu.');
echo "OK: parser CSV, warianty, deduplikacja, różnice i walidacja.\n";
