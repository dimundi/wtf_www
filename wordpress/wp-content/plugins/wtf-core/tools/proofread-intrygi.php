<?php
if (!defined('WP_CLI') || !WP_CLI) { exit; }
$page=get_post(925);
if(!$page || get_post_meta(925,'_wtf_ele_key',true)!=='intrygi:pl')WP_CLI::error('Brak Intryg.');
if(get_post_meta(925,'_wtf_intrygi_proofread',true)){WP_CLI::success('Korekta już zapisana.');return;}
$changes=[
'Dodatek wprowadza nowy typ kart'=>'Dodatek wprowadza nowy typ kart — Intrygi. Każda z nich opisuje scenariusz, który modyfikuje przygotowanie gry lub zasady obowiązujące podczas rozgrywki.',
'Przed rozpoczęciem gry wylosujcie'=>'Przed rozpoczęciem gry wylosujcie lub wybierzcie 1 Intrygę i wykonajcie zawarte na niej polecenia. Podczas rozgrywki trzymajcie obowiązującą Intrygę w widocznym miejscu, aby każdy gracz miał do niej łatwy dostęp.',
'Intrygi posiadają różny stopień'=>'Stopień złożoności Intrygi oznaczono spiralami obok jej nazwy. Intrygi z 1 spiralą wprowadzają drobne modyfikacje, a te z 2 spiralami potrafią wywrócić rozgrywkę do góry nogami. Na początek zalecamy Intrygi z 1 spiralą. Możecie użyć kilku Intryg jednocześnie, biorąc pod uwagę łączną liczbę spiral — zalecamy, aby nie przekraczała 3.',
'W dodatku znajdują się karty ze zdolnościami'=>'W dodatku znajdują się karty, których zdolności pozwalają poświęcać zasoby występujące w grze, np. Żar lub Jednostki.',
'Każdy zasób, który zostanie poświęcony'=>'Każdy poświęcony zasób zapewnia graczowi 1 Tytuł.',
'Poświęcenie zasobu polega'=>'Poświęcenie zasobu polega na dołączeniu go do zdobytej karty — od tej chwili pozostaje z nią nierozerwalnie związany. Jeśli karta zostanie przekazana innemu graczowi lub odrzucona, to samo dzieje się z dołączonymi zasobami. Żetony umieszczajcie na karcie, a karty, np. Jednostki, pod nią, tak aby lekko wystawały. Dzięki temu zawsze będzie widać, ile Tytułów zapewnia dana karta.',
'Karty umożliwiające poświęcenie zasobów'=>'Karty pozwalające poświęcać zasoby w zamian za Tytuły same w sobie nie mają Tytułów. Jeśli gracz niczego nie poświęci, ponieważ nie może lub nie chce tego zrobić, karta nie zapewni mu żadnego Tytułu. Po zdobyciu należy jednak wyłożyć ją przed sobą — jej posiadanie może mieć znaczenie dla innych efektów w grze.',
'Poświęcone żetony Żaru'=>'Poświęcone żetony Żaru uszczuplają wspólną pulę. Jeśli w fazie zakończenia rundy nie wystarczy żetonów dla wszystkich dobierających graczy, dobierajcie Żar w kolejności Dworu. Gracze, dla których go zabraknie, nie otrzymują Żaru.',
'Karty zawarte w tym dodatku'=>'Karty z tego dodatku, z wyjątkiem Intryg, możecie wtasować do odpowiednich talii i używać w każdej partii Elekta. Obowiązują dwa wyjątki: <strong>Krwawiącego Serca</strong> używajcie wyłącznie podczas gry z Intrygą <strong>Agonia Cesarza</strong>, a <strong>Czarnego Rynku</strong> — tylko w połączeniu z dodatkiem <strong>Artefakty</strong>.',
'Po zdobyciu, karta ta'=>'Po zdobyciu karta zapewnia właścicielowi 2 Tytuły. Używajcie jej podczas gry z Intrygą „Agonia Cesarza”.',
'Ten Wpływ umożliwia'=>'Ten Wpływ pozwala poświęcać Artefakty. Wtasujcie go do talii Wpływów tylko wtedy, gdy gracie z dodatkiem Artefakty.',
];
$seen=[];
$walk=function($blocks)use(&$walk,$changes,&$seen){foreach($blocks as &$b){
 if($b['blockName']==='wtf/ele-text')foreach($changes as $prefix=>$new)if(str_starts_with($b['attrs']['content']??'',$prefix)){
  if(isset($seen[$prefix]))WP_CLI::error('Powtórzony akapit.');
  $seen[$prefix]=true;$b['attrs']['content']=$new;break;
 }
 if($b['innerBlocks'])$b['innerBlocks']=$walk($b['innerBlocks']);
}return $blocks;};
$updated=serialize_blocks($walk(parse_blocks($page->post_content)));
if(count($seen)!==count($changes))WP_CLI::error('Nie znaleziono wszystkich akapitów.');
update_post_meta(925,'_wtf_before_intrygi_proofread',$page->post_content);
$saved=wp_update_post(['ID'=>925,'post_content'=>wp_slash($updated)],true);
if(is_wp_error($saved))WP_CLI::error($saved->get_error_message());
update_post_meta(925,'_wtf_intrygi_proofread',true);
WP_CLI::success('Korekta 11 akapitów Intryg zapisana.');
