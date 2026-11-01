# WTF Banery

Samodzielna wtyczka `wtf_banner`, menu **WTF Banery**, edycja dla administratora. Baner ma nazwę roboczą, opcjonalny nagłówek/tekst/przycisk, link, dwie grafiki z biblioteki Mediów, opis alternatywny, miejsca emisji, włącznik i opcjonalny termin od–do w strefie witryny. Wymaga statusu Opublikowany oraz zaznaczonego włącznika. Kolejność: pole Kolejność, rosnąco; przy remisie ID rosnąco. Wszystkie pasujące banery wyświetlają się jeden pod drugim, bez rotacji.

Shortcode określa miejsce i formę:

```text
[wtf_banner miejsce="glowna" forma="pozioma"]
[wtf_banner miejsce="sklep" forma="pionowa"]
[wtf_banner miejsce="gra" gra="kurzolaki" forma="pozioma"]
```

Miejsca: główna, sklep i strony gier według taksonomii `wtf_product_game`. Programowe rozszerzenie listy przez filtr `wtfb_places`. Checkbox miejsca nie wstawia sam shortcode'u do treści. Na stronie można użyć standardowego bloku Krótki kod. Brak pasujących banerów nie tworzy pustego kontenera. Niepoprawne miejsce lub forma niczego nie renderują.

Pozioma grafika: zalecane 1600 × 500 px, pionowa 600 × 800 px. Bez wymuszonego kadrowania; obraz zachowuje proporcje. Możliwy baner wyłącznie graficzny (cały obraz linkuje) lub obraz z tekstem/przyciskiem; sama treść również działa. Brak grafiki dla danej formy nie powoduje użycia niepasującego drugiego kadru. Opis grafiki i rozmiary edytowalne w panelu. Kolory zgodne z motywem i akcentem wydawnictwa.

Lokalny sklep: shortcode pionowy pod menu w lewej kolumnie, na telefonie po menu przed produktami. Lokalna główna (13): shortcode poziomy za istniejącą grupą Gamefound. Inne strony wymagają osadzenia shortcode'u. Gamefound pozostaje niezależny i nie zmienia działania. Bez aktywnych banerów sklep nie ma pustego pola banerowego.

Termin końcowy jest wyłączny (od wskazanej minuty baner znika). Harmonogram oceniany podczas generowania HTML; przy użyciu pełnego cache stron jego czas ważności musi uwzględniać terminy banerów albo cache wymaga czyszczenia. Bez zewnętrznego API, śledzenia i automatycznego publikowania.

31.10.2026: aktywowane wyłącznie lokalnie. Testy: kilka banerów, kolejność, wyłącznik, przyszły start, koniec, rozdzielenie miejsc, niepoprawne parametry, responsywny sklep 390/1440 px. Tymczasowe banery testowe usunięto. Narzędzia i zrzuty: `local-scripts/one-off/banner/`. Bez wdrożenia na PROD.
Dodane lokalnie: baner 4229 ALEgramy na głównej (zatwierdzona grafika z muchą, A64, link festiwalu, automatyczny koniec 8.11.2026 o 17:00); baner 4230 w sklepie, pudełko Kurzołaków i kod KURZOLAKI na darmową dostawę w Polsce przy podstawce lub zestawie, bez terminu. Podglądy home-banner/shop-banner w local-scripts/one-off/banner/, sprawdzone 390 i 1440 px. Skrypt add-live-banners-local.php nie nadpisuje późniejszych edycji.
Korekta: na głównej AleGramy przed Gamefound, wspólna szerokość kontenera i odstęp 32 px (sprawdzone 2560/1440/390 px). Kupon uproszczony: Aktualny kupon, KURZOLAKI w ramce, opis warunków; bez obrazu, przycisku i linku. Nowe opcjonalne pole Tekst w ramce (_wtfb_highlight) dostępne dla każdego banera.
Kolejna korekta: wyróżnienie kodu na kontrastującym tle; treść obsługuje bezpieczne linki, strong/em/br/p przez wp_kses (bez skryptów i niebezpiecznych protokołów). Podstawka i zestaw mają osobne linki. Pod kuponem dodano graficzny baner AleGramy 4233, kolejność 30, link festiwalu i koniec 8.11 o 17:00; bez tekstu/przycisku. Test 390/1440 px: dwa linki, grafika bez copy, kolejność, brak overflow. Zrzuty shop-links-*.png.
Mobilny sklep do 650 px: banery w dwóch równych kolumnach, odstęp 12 px, kompaktowy tekst kuponu. Desktop nadal jeden pod drugim. Sprawdzone 320/390/650/1440 px, bez poziomego przewijania ani przepełnienia kodu; zrzuty shop-pair-*.png.
Wspólny baner kuponu przy cenie: miejsce produkt, lista produktów w panelu i parametr produkt w shortcode. Kupon 4230 przypisany do 806 oraz 4205; ręczne _wtf_product_purchase_notice tych produktów usunięto z kopią w /tmp/wtfb-before-unify-product-notice.json. Powiązany kod WooCommerce (_wtfb_coupon) ukrywa baner, gdy kupon nie istnieje, nie jest opublikowany, wygasł lub osiągnął ogólny limit użyć; nie ocenia indywidualnego koszyka. Jeden włącznik i harmonogram dla wszystkich miejsc. Ciemny baner i aktywne menu: #273247, kod na #354735; jasny wariant zachowany. Produkty wybiera się w ustawieniach banera (_wtfb_products), bez kodowania identyfikatorów w szablonie.
1.11.2026 lokalnie: wybór produktów jako natywne wc-product-search z SelectWoo i wyszukiwaniem AJAX WooCommerce, wybrane produkty jako zawijane etykiety; lista wstępnie zawiera tylko wybrane ID. Baner przy cenie rozciągnięty do szerokości formularza zakupu. Test CMS potwierdził wyszukiwanie i zachowanie dwóch przypisań, test szerokości przy 390/1440 px przeszedł. Tymczasowa sesja testowa unieważniona.
1.11.2026: wdrożono na PROD na polecenie użytkownika. Mapowanie banerów 4229→4145, 4230→4146, 4233→4147; produkt zestawu 4205→4130. Testy i kopie: wtfDoc/srodowiska/banery-release-20261101.md.

1.11.2026, kolejna poprawka lokalna: ikona przy kodzie kuponu kopiuje kod do schowka, pokazuje potwierdzenie i ogłasza wynik czytnikom ekranu. Sprawdzone kopiowanie oraz układ przy 390/1440 px, składnia PHP poprawna. Etykieta „Cena” przy produkcie przesunięta o 8 px niżej. Bez wdrożenia na PROD.
1.11.2026 kolejna zmiana tylko lokalna: Język emisji (_wtfb_language): pl/en/all, brak pola oznacza pl. Renderowanie według bieżącego języka Polylang. Osobne wersje treści/grafik jako osobne rekordy w tym samym miejscu emisji. Test PL/EN/all przeszedł; tymczasowe rekordy testowe usunięte. Główna EN ma miejsce glowna, bez aktywnego banera EN.
