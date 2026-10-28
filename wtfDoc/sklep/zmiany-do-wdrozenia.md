# Zmiany produktów — stan wdrożenia

## Wdrożone 28.10.2026

- Wszystkie 33 adresy /produkt/<gra>/<produkt>/, bez przekierowań ze starych adresów; zaktualizowane linki wewnętrzne.
- Sekcje opisu zamiast zakładek i edytowalny nagłówek; Opis kart dla produktu 807.
- Maty: nowe nazwy, zdjęcia główne i zdjęcia pod opisami na całą szerokość, wzajemne linki do drugiego wzoru.
- Paczka: backups/product-release/batch-20261028/. Kopie kodu i zmienianych danych: .wtf-staging/batch-products-20261028/. Zachowane ceny, stany i powiązania; galerie bez zmian.
- Sprawdzone na PROD: 33 nowe adresy i kanoniczne linki, stare adresy 404 bez przekierowań, linki sklepu/gier, zdjęcia mat przy 390/1440 px, HTTPS i zabezpieczenia.

## Wdrożone wcześniej

Poniższe zmiany były wdrożone na `whathefrog.pl` na polecenie użytkownika; późniejsze dopiski o zmianach lokalnych należą do kolejnej paczki.

- Pozostała bieżąca oferta Elekta: skorygowane opisy dwóch mat (833, 830), pocztówek (828), czterech kart specjalnych (807), Echa Światów z Minstrelem (824) i albumu MP3 (825). Ujednolicone akapity, specyfikacje, nagłówki, listy i twarde spacje; poprawione linki albumu. Dłuższe opowieści i informacje znajdują się w pełnych opisach. Zachowano wymiary, zawartość, 17 nazw pocztówek, listę 14 utworów z próbkami, obrazy i film. Dane czasów pozostają zgodne z dotychczasowymi wersjami: zestaw z CD 72 minuty, osobny album MP3 1 godzina 18 minut. Niewidocznych w ofercie plakatów i sakiewki nie zmieniano.
- Wdrożenie powyższych sześciu opisów: `local-scripts/one-off/release-pl/correct-remaining-elekt-products.php`, paczka `backups/product-release/remaining-elekt-20261028/`, kopie na serwerze `.wtf-staging/patch-remaining-elekt-copy-20261028/`. Import zmienił wyłącznie opisy; ceny, stany, podatki, powiązania i dane pobrań zachowane. Kontrola HTTP sześciu stron lokalnie i na PROD poprawna, fotografie i galeria bez zmian.

- Standardowy produkt: proporcje galerii i opisu, panel zakupu nad opisem, kwadratowe zdjęcia i miniatury, tło zgodne z motywem sklepu. Breadcrumb gry prowadzi do tego samego filtra `game` co wybór gry. Kategoria pod opisem i sekcja up-sell ukryte; automatyczne podobne produkty pozostają.
- Podstawka Elekta: rozbudowane opisy i parametry gry, zawartość pudełka, zmiany drugiego wydania, kolorystyka sklepu w obu trybach. Galeria z automatycznym slideshow co 3 s i znacznikami 6 px; wybór zdjęcia zatrzymuje automat. Bez etykiety i licznika zdjęć.
- Wiedźmy 2: poprawiony opis, link `/elekt/wiedzmy2/`, 22 karty, autor Krzysztof Głośnicki, ilustracje Justyna Szumczyk.
- Plamy i FanPack: przeniesione aktualne grafiki użytkownika, wszystkie warianty plików i przypisania. ID mediów mapowane na docelowe.

Kod: `wtf-core/shop-product.php`, motyw `assets/product.css` i `assets/product-gallery.js`. Import zmienił wyłącznie opisy i zajawkę podstawki, krótki opis Wiedźm 2 oraz przypisania grafik Plam i FanPacka. Ceny, stany, podatki i powiązania produktów porównane przed i po — bez zmian. Klientów ani zamówień nie importowano.

Paczka i potwierdzenie: `backups/product-release/20261027/`. Eksport/import poza Git w `local-scripts/one-off/release-pl/`. Kopie starych plików i wybranych danych produktów na serwerze: `.wtf-staging/patch-product-release-20261027/`.

Test na OVH przy 390 i 1440 px: cztery produkty, opisy, nowe obrazy, filtrowanie Elekta, brak up-sell, karuzela 3 s, znaczniki 6 px i brak wznowienia po ręcznym wyborze. Kontrola HTTP/HTTPS, ochrony dostępu i zgodność SHA-256 wdrożonych plików poprawne.Produkt Wiedźmy (808, /produkt/dodatek-wiedzmy-2/) sformatowany według wzorca Wiedźm 2 i wdrożony na polecenie użytkownika: poprawiona polszczyzna i typografia, aktualny link /elekt/wiedzmy/, wymóg podstawki, 16 kart, autor Krzysztof Głośnicki i ilustracje Katarzyna Malinowska, rozpisana zawartość oraz zachowane informacje 18+ i dodruku 2023. Zmieniony wyłącznie krótki opis, ceny/stany/powiązania zachowane i porównane. Kopia .wtf-staging/patch-wiedzmy-product/; skrypt lokalnej korekty local-scripts/one-off/release-pl/format-wiedzmy-product.php. Kontrola HTTP na PROD poprawna.
Memo Cube (827, /produkt/elekt-memo-cube/): zdjęcie główne podmienione na wskazany Elekt-memo-ad.png, zaimportowane przez Media WordPressa z wariantami i ALT. Wdrożono tylko zdjęcie główne, bez zmiany galerii, opisu, ceny i stanu. Local i whathefrog.pl zgodne; kontrola strony produktu, listy sklepu i HTTP obrazu poprawna. Źródło i skrypt poza Git: local-scripts/one-off/product-media/memo-cube/. Paczka: backups/product-release/memo-20261027/. Kopia przypisania na serwerze: .wtf-staging/patch-memo-image/.
Memo Cube sformatowany i wdrożony: korekta krótkiego opisu, samodzielna gra pamięciowa, 36 kafelków / 18 par, kafelki 50 × 50 mm, bok pudełka 50 mm. Zachowano zdjęcie główne i 2 dodatkowe zdjęcia. Karuzela product-gallery.js jest teraz wspólnym komponentem dla obu szablonów wszystkich produktów: przy kilku zdjęciach automat co 3 s, znaczniki 6 px i zatrzymanie po ręcznym wyborze; przy jednym zdjęciu bez znaczników. Kopie kodu i starego opisu: .wtf-staging/patch-memo-format/. Testy local i PROD przy 390/1440 px oraz podstawka (7 zdjęć) i Wiedźmy 2 (1 zdjęcie) poprawne.
Galeria wszystkich produktów: znaczniki zastąpione miniaturami 64 × 64 px w jednym rzędzie bez zawijania. Strzałki pojawiają się tylko przy braku miejsca, przewijają sam pasek; aktywna miniatura pozostaje widoczna także podczas automatu. Zachowany pokaz co 3 s i zatrzymanie po ręcznym wyborze, wsparcie ograniczenia animacji. Zarezerwowana wysokość paska przed inicjalizacją JS. Wdrożono product.css i product-gallery.js; kopie .wtf-staging/patch-gallery-thumbnails/. Testy local i PROD 320/390/768/1440 px: jedna linia, strzałki i ich stany, wybór zdjęcia, 3 miniatury Memo Cube bez zbędnych strzałek, automat, zatrzymanie i pojedyncze zdjęcie bez panelu poprawne.
28.10.2026: Rubież (809) i Powołanie 2 (810) sformatowane zgodnie z wzorcem Wiedźm 2 i wdrożone na whathefrog.pl. Korekta polszczyzny, twarde spacje, prawidłowe linki do stron dodatków, wymagania podstawki, liczby kart, autorzy i zawartość z lokalnych stron dodatków. Rubież: 22 karty (10 Misji, 10 Wpływów, 2 Przywileje), autor Krzysztof Głośnicki, ilustracje Justyna Szumczyk i Katarzyna Malinowska. Powołanie 2: 20 kart Jednostek Podstawowych, autor Krzysztof Głośnicki, ilustracje Justyna Szumczyk; zachowane wymaganie bez Powołania 1. Zmieniono wyłącznie krótkie opisy; ceny/stany/powiązania porównane i zachowane. Kontrola HTTP treści i linków na PROD poprawna. Kopie: .wtf-staging/patch-elekt-expansions-20261028/; paczka backups/product-release/elekt-expansions-20261028/.
28.10.2026: galerie Powołania 2 (810), Rubieży (809) i Wiedźm (808) zastąpione kartami z odpowiednich stron dodatków. Zgodnie z odpowiedzią użytkownika pudełka zachowane jako zdjęcia główne; dodatkowe zdjęcia wyłącznie dwie karty na produkt. Dotychczasowe pliki pozostały w bibliotece, zmieniono tylko przypisania galerii. Sześć obrazów i 50 wariantów zweryfikowano sumami; istniejące zgodne pliki i załączniki na serwerze użyte ponownie. Main image, ceny i stany zachowane i porównane. Wspólna karuzela pokazuje 3 miniatury. Testy local i PROD 390/1440 px: właściwe pudełko i karty, wszystkie miniatury dostępne, wybór zdjęcia poprawny. Kopie przypisań: .wtf-staging/patch-expansion-galleries-20261028/; paczka backups/product-release/expansion-gallery-20261028/; skrypt local-scripts/one-off/release-pl/replace-elekt-expansion-gallery.php.
28.10.2026: Szklany Żar (834, /produkt/szklany-zar/) — zdjęcie główne zastąpione wskazanym Zar.png. Import przez Media WP, ALT i 8 plików wariantów zachowane. Zmieniono tylko zdjęcie główne; galeria, opis, ceny i stany bez zmian. Local i PROD zgodne, HTTP obrazu na PROD 200. Kopia przypisania .wtf-staging/patch-zar-image-20261028/; paczka backups/product-release/zar-20261028/.
28.10.2026: ponowna aktualizacja grafiki Żaru po zmianie źródłowego Zar.png przez użytkownika. Nowy załącznik Zar-updated i warianty, nowy URL zapobiega użyciu starego obrazu z cache. Local i PROD zaktualizowane, sprawdzono HTML produktu. Backup .wtf-staging/patch-zar-image-20261028-v2/; ceny i pozostałe pola bez zmian.
28.10.2026: korekta opisu Szklanego Żaru wdrożona local i PROD. Trzy czytelne akapity, poprawiona polszczyzna i twarde spacje; zachowane fakty: szkło, opalizujące bursztynowe znaczniki o srebrzystym połysku, zestaw 13 sztuk. Zmieniono tylko krótki opis, aktualne zdjęcie i pozostałe dane bez zmian. Kontrola HTTP poprawna, kopia .wtf-staging/patch-zar-description-20261028/.
28.10.2026: wspólny załącznik 727 (11972-Elekt-promki-2023.jpg / .webp) zastąpiony plikiem Elekt-promki-2023.png wskazanym przez użytkownika. Zachowano ID, przypisania i dotychczasowe adresy JPG/WebP oraz miniaturek; nowe metadane odzwierciedlają obraz 543 × 543 px. Zaktualizowano 6 plików. Miniatury i WebP tworzy WordPress z Modern Image Formats; dla oryginalnego JPEG na czas zapisu pominięto automatyczną zmianę formatu przez wtyczkę. Faktyczne formaty JPEG/WebP, HTTP 200 i przypisanie produktu 807 potwierdzone na PROD. Kopie .wtf-staging/patch-promki-image-20261028/ i .wtf-staging/patch-promki-jpeg-fix-20261028/; paczki backups/product-release/promki-image-20261028/; skrypty local-scripts/one-off/product-media/promki/.
28.10.2026: maty 833 i 830 otrzymały zdjęcia główne elekt_mata_1.png i elekt_mata_2.png. Galerie i pozostałe dane zachowane. Na wyraźne polecenie użytkownika nowe adresy /produkt/elekt/mata-1/ i /produkt/elekt/mata-2/; dokładne stare adresy przekierowują 301. shop-product-urls.php definiuje linki kanoniczne, routing i aliasy; ID i post_name nie zmieniano. Sprawdzone obrazy wizualnie lokalnie, HTTP 200 nowych stron, 301 starych i linki z listy sklepu na PROD. Kod i paczka wdrożone; kopie .wtf-staging/patch-maty-images-urls-20261028/; paczka backups/product-release/maty-20261028/.
Na polecenie użytkownika wyłączono przekierowania dawnych adresów produktów. Maty działają pod nowymi adresami /produkt/elekt/mata-1/ i /produkt/elekt/mata-2/ (200); stare /produkt/mata-do-gry/ i /produkt/mata-do-gry-2/ zwracają 404, bez Location i bez automatycznego przekierowania kanonicznego WP. Sprawdzone local i PROD. Zasada na przyszłość zapisana w AGENTS.md.

## Wdrożone — panorama mat (28.10.2026)

- Panorama Rubieży i Nie jesteś tu sam: nagłówki, fabularne opisy kursywą, brak numeru przy jednej sekcji.
- Zdjęcia pod opisami z powiększaniem PhotoSwipe; Upiory z poprawionym obrazem 3317 i nowym URL.
- Ciemne powierzchnie produktów w granacie #273247; nazwa produktu 807: Karty specjalne - edycja 2023.
- Paczka backups/product-release/panorama-20261028/, kopie serwera .wtf-staging/panorama-20261028/. Import trzech produktów; ceny, stany, zdjęcia główne i galerie zachowane.

## Wdrożone — Memo Cube (28.10.2026)

- Produkt 827: zdjęcie główne memo_1.png (3318), galeria zastąpiona memo_2.png (3319) i memo_3.png (3320), import przez Media z wariantami. Stare zdjęcia odłączone od produktu, pliki zachowane w bibliotece. Opisy, ceny i stany porównane bez zmian. Skrypt local-scripts/one-off/product-media/memo-cube/replace-images-local.php. Wdrożone na whathefrog.pl; trzy zdjęcia i kolejność sprawdzone HTTP. Paczka backups/product-release/memo-three-20261028/, kopie przypisań .wtf-staging/memo-three-20261028/. Ceny, stany i powiązania zachowane.

## Wdrożone — etykieta promocji (28.10.2026)

- Strony produktów i ich podobne produkty: zielona etykieta #90C11D z łapką wyodrębnioną z konturu litery G logo WTF, granatowy tekst. Kod product.css i assets/wtf-frog-foot.svg. PROD bez zmian.

- Etykieta lokalna: procent liczony automatycznie z ceny regularnej i promocyjnej, zaokrąglony do liczby całkowitej. Łapka stanowi wysunięty lewy obrys zielonej etykiety zamiast ikony wewnątrz. Kod shop-product.php i product.css. PROD bez zmian.

- Wdrożona końcowa wersja: automatyczny rabat procentowy, wysunięta łapka jako lewy obrys, obniżona góra prostokąta i zaokrąglony lewy dolny róg. Pliki shop-product.php, product.css, wtf-frog-foot.svg. Kopie .wtf-staging/sale-badge-20261028/backup/. Bez zmian bazy.

## Favicon — wdrożone 28.10.2026

- Local i whathefrog.pl: favicon SVG, zielona łapka logo WTF na granatowym zaokrąglonym tle. Motyw functions.php i assets/favicon.svg; wersjonowany URL. Sprawdzone HTML i HTTP obrazu w obu środowiskach. Kopia .wtf-staging/favicon-20261028/functions-before.php.

- Favicon local i PROD: na polecenie użytkownika usunięte granatowe tło; sama zielona łapka na przezroczystości. HTTP SVG sprawdzone.

- Favicon local/PROD: ciasny kwadratowy viewBox 245 144 44 44 zamiast 57 × 57, łapka większa o około 30%, bez tła.

## Wdrożone — odstęp nad produktem (28.10.2026)

- Mobile do 700 px: shop-single padding-top 10 px zamiast 35 px, bliższa ścieżka produktu pod głównym menu. Desktop bez zmian. Kod product.css. Wdrożono na whathefrog.pl i sprawdzono HTTP CSS. Kopia .wtf-staging/product-mobile-gap-20261028/product-before.css.

## Wdrożone 28.10.2026 — jednostki sklepu i Tsukuyumi

- Poprawione opcje WooCommerce woocommerce_weight_unit=kg, woocommerce_dimension_unit=m, zgodnie ze źródłową kopią 8082. Importowane wartości nie zostały przeliczone ani zmienione. Sunrise Kingdom ma wagę 3 kg i wymiary 0,32 × 0,32 × 0,07 m. Na PROD przenieść tylko te dwie opcje; nie zmieniać wartości produktów.

- Lokalnie Sunrise Kingdom (813): poprawione i uporządkowane oba opisy; parametry, waga i wymiary 32 × 32 × 7 cm w pierwszej sekcji. Usunięte hashtagi, puste akapity i dawna zapowiedź premiery. Nagłówek O rozszerzeniu. Meta _wtf_specs_in_intro=yes wyłącza dublującą tabelę dodatkowych informacji, jeśli nie ma innych atrybutów; kod shop-product.php. Ceny, stany i grafiki bez zmian. Skrypt format-sunrise-local.php; PROD bez zmian.

- Sunrise Kingdom (813): w krótkim opisie link Więcej o rozszerzeniu Sunrise Kingdom do /tsukuyumi/#tsu-sunrise (sekcja sprawdzona). Usunięty ogólny link z długiego opisu; lokalnie, PROD bez zmian.

- Lokalnie produkt 813: nazwa Sunrise Kingdom zamiast Tsukuyumi: Sunrise Kingdom. Adres bez zmian; do wdrożenia wyłącznie tytuł wraz z oczekującymi opisami.

- Lokalnie usunięto Tsukuyumi z nazw 10 produktów poza podstawką: 812, 814, 815, 817, 819–823, 826. Sunrise Kingdom 813 zmieniony wcześniej. Nazwa podstawki 816 i adresy zachowane. Skrypt rename-tsukuyumi-expansions-local.php; przy wdrożeniu przenieść wyłącznie nazwy, bez nadpisywania opisów pozostałych produktów. PROD bez zmian.

- Lokalnie Kampfgruppe 03 (823): poprawione oba opisy, wymaganie podstawki i 10 podstawek przy zakupie, aktualne linki do frakcji /tsukuyumi/kg3/ i oferty podstawek 25 szt. Historia kursywą, pełna lista zawartości i zachowana ilustracja pod opisem. Usunięte stare shortcode animacji, tło zdublowanego obrazu, puste odstępy i literówka Kompfgruppe. Nagłówek Frakcja i zawartość. Ceny, stany, atrybuty, zdjęcie główne i galeria zachowane i porównane. Skrypt format-kampfgruppe-local.php; PROD bez zmian.

- Kampfgruppe 03 lokalnie: ilustracja jest kompozycją dwóch plików. Przywrócone tło c57234de…KG3_Z11.webp pod przezroczystą warstwą napisów 6d76ffbd…KG3_Z11.webp, biała podstawa figury. Podczas wdrożenia zachować styl tła w opisie.

## Wdrożone 28.10.2026 — pozostałe dodatki Tsukuyumi

- Uporządkowane oba opisy produktów 812, 814, 815, 817–822: parametry i wymagania w pierwszej sekcji, listy zawartości w pełnym opisie, aktualne linki, twarde spacje. Chronomasters: zachowane ograniczenia kafelków, 33 × 24 × 3 cm i 0,4 kg w pierwszej sekcji, meta _wtf_specs_in_intro=yes.
- Zachowane wszystkie osiem ilustracji, bez zmiany zdjęć głównych i galerii. Ceny, stany, atrybuty i nazwy porównane bez zmian. Usunięto stare hashtagi, puste odstępy, nieaktualną zapowiedź premiery i sztywny tekst historycznej ceny (aktualne komunikaty cenowe pozostają automatyczne). Bonus planszetek nr 4 oznaczony jako oferta przedsprzedażowa, bez obietnicy bieżącego bonusu.
- Skrypt format-remaining-tsukuyumi-local.php; sprawdzono HTTP dziewięciu produktów oraz 59 linków/grafik. Komiks i podstawka poza zakresem. PROD bez zmian.

- Planszetki nr 4 (812), lokalnie: usunięta cała informacja o przedsprzedaży, nagłówek Planszetki frakcji w zestawie. Usunięta duplikująca galeria (739), zachowane zdjęcie główne 738; pliki w bibliotece bez zmian. Przy wdrożeniu przenieść opis i pustą galerię. PROD bez zmian.

- Lokalnie produkt 819: nazwa Zestaw planszetek nr 1. Adres i pozostałe dane bez zmian; do wdrożenia tytuł.

- Lokalnie komiks Tsukuyumi (826): zajawka i dane wydania przy zakupie (autor, 70 stron komiksu + 24 strony artbooka, miękka oprawa, wydawca, 2020, ISBN). Nagłówek Smok powraca; poprawiona fabuła kursywą i czytelna zawartość. Nazwa, ceny, stany, zdjęcie i galeria zachowane i porównane. Skrypt format-tsukuyumi-comic-local.php; PROD bez zmian.

- Lokalnie listy sklepu: wtf_shop_card pokazuje rabat przez wspólny filtr woocommerce_sale_flash, ta sama zielona łapka z procentem. Style w shop.css, grafika wtf-frog-foot.svg. Dotyczy kart gier i widoku Wszystkie. PROD bez zmian.

## Potwierdzenie wdrożenia Tsukuyumi i etykiet — 28.10.2026

Wszystkie powyższe zmiany Tsukuyumi i etykiet list przeniesione na whathefrog.pl, w tym nazwy 12 produktów, korekty planszetek nr 4 (jedno zdjęcie, bez przedsprzedaży), nazwa planszetek nr 1, komiks, kompletna dwuwarstwowa ilustracja Kampfgruppe, parametry w pierwszej sekcji i kg/m. Wcześniejsze dopiski PROD bez zmian przy tych pozycjach opisują stan przed tym wdrożeniem.

Paczka backups/product-release/tsukuyumi-20261028/, kopie plików, produktów i jednostek .wtf-staging/tsukuyumi-20261028/. Ceny, stany, atrybuty, zdjęcia główne zachowane i porównane. Galerie bez zmian poza świadomym wyczyszczeniem galerii 812. Sprawdzono HTTP 12 produktów, 75 linków/grafik oraz zieloną zwartą etykietę z procentem na liście Elekta w Chrome. Bez podmiany bazy.

## Wdrożone 28.10.2026 — opakowania i układy frakcji

- Fireborn (822): nagłówek Zawartość, usunięty podnagłówek Zawartość pudełka. Sentinels (820), Władcy Utraconych Mórz (821), Kampfgruppe 03 (823): Zawartość blistra zamiast Zawartość pudełka. Chronomasters i Sunrise Kingdom zachowują pudełka. Reguła opakowań zapisana w AGENTS.md. Skrypt correct-faction-packaging-local.php; PROD bez zmian.

- Korekta lokalna powyższego: nagłówki ZAWARTOŚĆ, bez określenia blistra. Fireborn: główny nagłówek ZAWARTOŚĆ; pozostałe trzy frakcje: podnagłówek ZAWARTOŚĆ. PROD bez zmian.

- Sentinels (820) lokalnie: nagłówek Zawartość, lista i powtórzenie informacji o braku 12 podstawek po lewej, ilustracja po prawej; na telefonie ilustracja pod listą. Meta _wtf_contents_image_layout=yes, renderer shop-product.php i product.css. Źródło dostępne wyłącznie Sentinels_low (1000 × 728); bez sztucznego powiększania. PROD bez zmian.

- Lokalnie link dokup podstawki osobno pod listą Sentinels wyraźnie podkreślony i lekko pogrubiony, style product.css. PROD bez zmian.

- Chronomasters (814) lokalnie: usunięta wzmianka o osobno zakupionym woreczku lnianym. Zdanie kończy się na przechowywaniu w pudełku gry. PROD bez zmian.

- Lokalnie Nowe Lądy (817) i Kampfgruppe 03 (823): układ jak Sentinels, nagłówek Zawartość, tekst/lista po lewej, ilustracja po prawej; mobilnie obraz pod tekstem. Pod listami kopia informacji o braku 10 podstawek z podkreślonym linkiem. Meta _wtf_contents_image_layout=yes. Kampfgruppe zachowuje obie warstwy grafiki. PROD bez zmian.

- Fireborn (822) lokalnie: Zawartość, lista i kopia informacji o braku 7 podstawek po lewej; oba dotychczasowe obrazy po prawej w grupie wtf-product-illustrations. Wspólny renderer grupuje wiele obrazów, bez nakładania ich na siebie. Mobilnie obrazy pod tekstem.
- Podstawka Tsukuyumi (816) lokalnie: poprawione oba opisy, parametry 2–4 / do 6 graczy z rozszerzeniami, 12+, 180 min, 32 × 32 × 10 cm, 3 kg, EAN w pierwszej sekcji. Fabuła kursywą, sekcje mechanik, zachowana pełna lista zawartości i ilustracja. Aktualne linki komiksu i planszetek, bez starego linku do nieoferowanych koszulek, hashtagów i pustych odstępów. Meta _wtf_specs_in_intro=yes i nagłówek O grze. Ceny, stany, nazwa i galeria zachowane i porównane. Skrypt fireborn-base-tsukuyumi-local.php; PROD bez zmian.

- Gracz+ (x2), 815 lokalnie: nagłówek Zawartość, lista łącznej zawartości dwóch rozszerzeń po lewej i ilustracja po prawej; na mobile obraz pod listą. Pod zawartością powtórzenie informacji o braku 12 podstawek z podkreślonym linkiem. Meta _wtf_contents_image_layout=yes. PROD bez zmian.

## Potwierdzenie wdrożenia układów Tsukuyumi — 28.10.2026

Wszystkie powyższe korekty opakowań, nagłówków, Sentinels, Chronomasters, Nowych Lądów, Kampfgruppe 03, Fireborn, podstawki i Gracz+ wdrożone na whathefrog.pl. Wcześniejsze zapisy PROD bez zmian dla tych pozycji dotyczą stanu przed wdrożeniem. Przeniesiono wyłącznie wybrane opisy i metadane układu ośmiu produktów oraz shop-product.php i product.css. Galerie, nazwy, ceny, stany i atrybuty zachowane i porównane. Paczka backups/product-release/tsukuyumi-layout-20261028/, kopie .wtf-staging/tsukuyumi-layout-20261028/.

## Kurzołaki — próby kolorów i stan wdrożenia

- Tylko paleta strony gry: wanilia #FFF6DF, brzoskwinia #FFD5B5, róż #FFCEE5, turkus #75DDD3, malina #DB3978, śliwkowy tekst #452650. Jaśniejsze pola ilustracji i karty frakcji, delikatniejsza faktura. Układ i nieregularne kształty zachowane. Kod assets/kurzolaki.css; poprzednia wersja backups/kurzolaki-colors/20261028/kurzolaki-before.css. PROD bez zmian.

- Na polecenie użytkownika cofnięto lokalną paletę cukierkową Kurzołaków. Przywrócony dokładnie poprzedni kurzolaki.css z kopii, zgodność SHA-256 sprawdzona. Zmiana kolorów nie oczekuje na wdrożenie; PROD nie był zmieniany. Kierunek dalszej pracy do ponownej oceny.

- Nowa próba lokalna Kurzołaków: tylko tło ciepłobiałe #FFFEFA bez papierowej faktury; dopasowane tło menu sekcji i dropdownu. Dotychczasowe kolory akcentów, tekst i kształty bez zmian. assets/kurzolaki.css; PROD bez zmian.

- Kurzołaki lokalnie: białe tło odrzucone; aktualna próba to przygaszona mięta #E5EEE8 bez faktury, wraz z tłem menu. Reszta palety i kształty zachowane. PROD bez zmian.

- Kurzołaki lokalnie: na aktualnym miętowym tle dodany bardzo delikatny splot tkaniny z dwóch półprzezroczystych kierunków CSS. Bez nowych plików i zmian pozostałych kolorów. PROD bez zmian.

- Kurzołaki lokalnie: aktualne tło stonowany beż #E9E2D5 zamiast mięty, z zachowaniem subtelnego splotu tkaniny. Menu dopasowane do beżu; pozostałe kolory i kształty bez zmian. PROD bez zmian.

- Kurzołaki lokalnie: beż rozjaśniony do #F2EDE3; faktura bez zmian. PROD bez zmian.

- Kurzołaki PL (566) lokalnie: blok dust-logo podmieniony na ostrzejszy Kurzolaki_logo_1.png z folderu nowych assetów użytkownika. Import przez Media WP z WebP i wariantami, ALT i grupa Kurzołaki. Bez zmian treści, wymiaru CSS i innych obrazów. Skrypt local-scripts/one-off/kurzolaki-assets/replace-logo-local.php; PROD bez zmian.

- Naprawiony lokalny zapis bloku po podmianie logo: przywrócona oryginalna treść z kopii i ponownie przypisane medium 3321, z wp_slash przy wp_update_post. Naprawione znaczniki HTML i pozostałe escapowane atrybuty. Sprawdzono lokalny HTML i wygląd 390/1440 px; PROD bez zmian.

- Kurzołaki lokalnie: obraz pudełka w hero bez mix-blend-mode multiply (oryginalne kolory); nagłówek hero mocny bezszeryfowy Trebuchet/Verdana, malinowe hasło bez kursywy z lekkim przechyleniem. Tło, układ i nieregularne kształty zachowane. PROD bez zmian.

- Kurzołaki PL (566), lokalnie: nowe pudełko Pudlo_Kurzołaki.png z folderu użytkownika, medium 3324 i warianty WebP. W hero zachowana naturalna orientacja (bok po prawej), przechylenie obrazu +3 stopnie zamiast -8. Bez odbijania napisów. Zmieniony tylko właściwy blok obrazu; poprawny zapis wp_slash. PROD bez zmian.

- Kurzołaki lokalnie: logo w hero powiększone z 290 do 360 px (około 24%), maksymalnie 90% kolumny na telefonach. PROD bez zmian.

- Kurzołaki lokalnie: zwiastun Kurzolaki_trailer.mp4 (162 MB, 90 s, 1920 × 1080) w sekcji pod hero i menu lokalnym, przed opisem gry. Film Media 3326, miniatura kadru 3327. Native video controls/playsinline/preload=none, bez autoplay i pobierania MP4 przy wejściu. Blok wtf/kurz-video obsługuje fileId i poster oraz wybór MP4/miniatury w edytorze; YouTube zachowany. Kod kurzolaki.php, kurzolaki-editor.js, kurzolaki.css; skrypt add-trailer-local.php, źródło media/kurzolaki/. Oryginalny film nie jest jeszcze skompresowany; przed publikacją przygotować lżejszą wersję. PROD bez zmian.

- Zwiastun Kurzołaków lokalnie: wyróżniona żółta informacja Koniecznie oglądaj z dźwiękiem! w sekcji filmu. PROD bez zmian.

- Lokalnie informacja o dźwięku przeniesiona pod nagłówek zwiastuna, w osobnym wyśrodkowanym wierszu tuż nad filmem. PROD bez zmian.

- Plakietka dźwięku zwiastuna lokalnie: mniejsza (12 px, padding 6 × 12 px), nieregularna, przechylona o -4 stopnie z delikatnym cieniem. PROD bez zmian.

- Plakietka dźwięku lokalnie przesunięta 22 px w górę, nad nagłówkiem w warstwie, z możliwością lekkiego nachodzenia. PROD bez zmian.

- Kurzołaki PL (566) lokalnie: cztery ilustrowane frakcje w sekcji Po czyjej jesteś stronie. Gałgan (3331), Kichacz (3332), Puca (3333) i Landrynka (3334). Puca/Landrynka z obecnymi tłami na akceptację, do ewentualnej podmiany przez użytkownika; czwarta frakcja oznaczona jako dodatek Sweet Revenge. Cztery karty na desktopie, dwie na tabletach, jedna na telefonach, nieregularne kształty zachowane. Wszystkie bloki edytowalne, media z WebP i grupą Kurzołaki. Test 390/1440 px: obrazy, nazwy, brak poziomego przepełnienia poprawne. Skrypt illustrate-factions-local.php; PROD bez zmian.

- Kurzołaki lokalnie: sekcja frakcji używa trzech kompletnych plansz board-na stronę-frakcje_PL_2/3/4.png z www użytkownika, media 3336–3338 (warianty WebP). Pliki zawierają teksty; zachowane bez kadrowania, max szerokość 1050 px, kolejno Flejtuchy/Robale/Puca; dostępne tekstowe opisy dla czytników. Na telefonie plansze na pełną szerokość i link do pełnej grafiki umożliwiający powiększenie. Landrynka zachowana osobno jako czwarta frakcja z dodatku. Zastępuje poprzedni lokalny układ czterech kart. Testy 390/1440: obrazy, proporcje, linki, brak overflow. Skrypt faction-boards-local.php; PROD bez zmian.

- Plansze frakcji Kurzołaków lokalnie po dwie w rzędzie na desktopie/tablecie, na telefonie jedna. Obecna karta Landrynki w czwartym miejscu czeka na nową planszę użytkownika. PROD bez zmian.

- Lokalnie czwarta plansza frakcji: board-na stronę-frakcje_PL_5.png (Landryna/Sweet Revenge) zastępuje tymczasową kartę Landrynki. Wszystkie cztery są jednakowymi blokami plansz, po dwie w rzędzie desktop/tablet i pojedynczo mobile, z linkami do pełnych grafik. Import z WebP i grupą Kurzołaki; skrypt landrynka-board-local.php. PROD bez zmian.

- Kurzołaki lokalnie: usunięty nagłówek Po czyjej jesteś stronie. Cztery plansze włączone do sekcji dust-game (Małe stwory), bez osobnej sekcji; zachowany anchor dust-factions na wewnętrznym kontenerze dla menu. Sekcja rozszerzona do szerokości kontenera, tekst wstępu zachował węższą miarę, plansze 2 × 2 / jedna mobilnie. PROD bez zmian.

- Sweet Revenge na stronie Kurzołaków lokalnie: usunięty różowy podkład i padding wokół pudełka, grafika bez multiply, oryginalne kolory. Układ i tekst bez zmian. PROD bez zmian.

- Sweet Revenge lokalnie: tekstowy nagłówek zastąpiony oryginalnym logo z folderu Sweet Revenge/logo/logo sweet revenge.png. Import przez Media WP, grupa Kurzołaki, alt Sweet Revenge w semantycznym h2. Logo 380 px desktop / 300 px mobile, oryginalne proporcje i kolory. PROD bez zmian.

- Sweet Revenge lokalnie: nowy Box_SweetRevenge.png z www użytkownika, naturalna orientacja bez odbicia; przechylenie oprawy +2 stopnie zamiast -3. Podmieniony tylko blok grafiki w dust-expansion-art, Media z WebP i grupą Kurzołaki. PROD bez zmian.

- Sweet Revenge lokalnie: różowa nieregularna chmurka za pudełkiem, jak w hero; pseudoelement powiązany z rozmiarem grafiki, bez rozciągania do wysokości kolumny tekstu. PROD bez zmian.

- Kurzołaki lokalnie: pod wstępem Małe stwory, przed infografikami, dodany edytowalny akapit o dwujęzycznym wydaniu z komponentami PL i EN. Twarde spacje zachowane. PROD bez zmian.

- Kurzołaki lokalnie: ilustracja w sekcji dust-play (Spróbuj to rozgryźć) podmieniona na Kurzolaki_plansza.png z www użytkownika. Pod ilustracją przycisk Pokaż pełny wymiar, link do oryginalnego rozmiaru w nowej karcie. Media WP z wariantami i grupą Kurzołaki; skrypt replace-play-board-local.php. PROD bez zmian.

- Plansza Kurzołaków lokalnie: przycisk Pokaż planszę zamiast Pokaż pełny wymiar, podpis Plansza i elementy gry Kurzołaki. — bez określenia prototyp. PROD bez zmian.

- Dwujęzyczność Kurzołaków lokalnie: osobny wyróżniony blok zamiast zwykłego akapitu, nagłówek Jedna gra. Dwa języki., żółta przekrzywiona plakietka PL / EN i informacja o komponentach w obu językach. Nieregularna miętowa oprawa, responsywny układ, edytowalne bloki. PROD bez zmian.

- Kurzołaki lokalnie: dwa samouczki YouTube pkbfRJCQ3O8 i Sa4Fuo40yAc dodane na początku dust-video-grid w podanej kolejności, stare dwa filmy zachowane. Osadzenia youtube-nocookie z lazy loading, edytowalne bloki. Skrypt prepend-tutorials-local.php; PROD bez zmian.

- Lokalnie tytuły nowych samouczków: Przygotowanie do gry oraz Przykładowa runda. Pod każdym podtytuł Film przygotowany na bazie prototypu gry. PROD bez zmian.

## Recenzje Kurzołaków — wdrożone 28.10.2026

- Z dostarczonego przez użytkownika HTML kampanii Gamefound, sekcja 84874, przeniesiono komplet sześciu źródeł: Tabletoppping (Instagram EN, polski przekład ze źródłowej grafiki), Planszówki we dwoje (Wiktor/Kasia i zdjęcie), Boardgamerka (cytat i zdjęcie), trzy filmy: Planszówki Online RqpcjUisRN4, Planszowe Newsy hSMnF-lVlhQ, OnTable Z7XQtbFY6PM.
- Układ: wyróżniona opinia, dwie karty recenzji pisanych, osobna siatka wideo. Wszystkie źródła z linkami, cztery cytaty z podpisami, iframe lazy youtube-nocookie. Dotychczasowe samouczki i pozostałe filmy zachowane. Media 3354/3355 przez WP z WebP; źródło i skrypt local-scripts/one-off/kurzolaki-reviews/. HTML zawiera dane sesji — nie publikować całego pliku źródłowego, do strony przeniesiono tylko treść recenzji. PROD bez zmian.

- Lokalnie przy zdjęciach recenzji Planszówek we dwoje i Boardgamerki podpis Zdjęcie prototypu gry. Dwa podpisy sprawdzone w HTML. PROD bez zmian.

- Lokalnie usunięty dopisek Polski przekład opinii zamieszczony na stronie kampanii spod opinii Tabletoppping. Cytat i link zachowane; PROD bez zmian.

- Kurzołaki lokalnie: Kurzolaczki.png z www użytkownika na samym końcu treści strony przed stopką, w pełnych proporcjach, max szerokość 1200 px. Media WP z WebP, opis alternatywny oddaje dialog mema, grupa Kurzołaki. Skrypt append-meme-local.php; PROD bez zmian.

- Kurzołaki lokalnie: podmenu Opinie → Recenzje. Do 700 px układ jak w innych grach: strzałka powrotu na górę po lewej i details z listą sekcji po prawej. Zamykanie po wyborze, Escape i kliknięciu poza; sticky, reduced-motion, ukrycie przy otwartym głównym menu. Desktop zachowuje poziome menu. Kod kurzolaki.js/css i functions.php; PROD bez zmian.

## Kurzołaki — komplet wdrożony 28.10.2026

Wszystkie zgromadzone poprawki Kurzołaków są na whathefrog.pl. Wcześniejsze dopiski lokalnie / PROD bez zmian przy tych pozycjach opisują stan sprzed tego wdrożenia. Aktualnie: jasny beż #F2EDE3 z fakturą tkaniny, nowa typografia hero, większe logo i nowe pudełko; zwiastun z informacją o dźwięku; blok dwujęzyczności PL/EN; cztery infografiki w sekcji Małe stwory (2 × 2 desktop, jedna mobile); logo i pudełko Sweet Revenge z różową chmurką; nowa plansza z przyciskiem Pokaż planszę; cztery filmy instruktażowe z opisami; komplet sześciu źródeł recenzji, podpisy zdjęć prototypu; grafika Kurzolaczki na końcu; menu Recenzje i mobilna lista ze strzałką do góry.

Film zoptymalizowany z 161711711 do 39616107 bajtów (około 75% mniej), MP4 H.264 Full HD, 90 sekund, dźwięk AAC stereo, faststart. Oryginał zachowany lokalnie. Preload none i odtwarzanie po kliknięciu; test PROD potwierdza HTTP Range 206 i brak pobierania filmu przy wejściu.

Zmieniona wyłącznie strona PL 566 (docelowe ID rozwiązane ze sluga) oraz 14 mediów / 126 plików i pliki motywu/wtyczki. Mapowanie imageId/mobileId/fileId i domen wykonane przy imporcie. Sklep, klienci, zamówienia i EN bez zmian. Kopie strony i plików prywatnie .wtf-staging/kurzolaki-page-20261028/. Paczka końcowa backups/kurzolaki-release/20261028/page.tar.gz i manifest.json. Skrypty eksportu/importu local-scripts/one-off/kurzolaki-assets/.

Kontrola PROD: 390/1440 px, menu i powrót do góry, cztery plansze, dwujęzyczność, sześć źródeł recenzji, podpisy, samouczki, wszystkie obrazy, odtwarzanie Full HD, zakresy MP4 i zabezpieczenia HTTP/HTTPS/noindex/Basic Auth — poprawne.

## Oczekujące lokalnie — uproszczenie Kurzołaków

- Usunięty przycisk Zajrzyj do Zakamarka w hero. Cztery infografiki frakcji bez linków powiększania i bez podpowiedzi Dotknij planszy; grafiki i układ zachowane. Przycisk Pokaż planszę w sekcji rozgrywki bez zmian. Skrypt simplify-hero-boards-local.php; PROD bez zmian.

- Kurzołaki lokalnie: SVG plamy odtworzone z pokazanego przez użytkownika obrysu, przygaszony róż bez konturu za nagłówkiem hero. Subtelny ruch 17 s, tekst i logo nieruchome, reduced-motion wyłącza animację. Pliki assets/kurzolaki-stain.svg i kurzolaki.css. PROD bez zmian.

- Lokalnie ruch plamy hero wzmocniony: cykl 8 s, przesunięcie około 30 px, obrót od -10 do +9 stopni i większa zmiana skali. Reduced-motion zachowane. PROD bez zmian.

- Kurzołaki lokalnie: osobny wyróżniony blok Słodkie stwory. Niesłodkie zagrania. z akapitem o taktycznej rozgrywce mimo słodkich ilustracji. Różowy wariant oprawy bloku dwujęzyczności, plakietka GRA TAKTYCZNA. Umieszczony przed blokiem PL/EN w sekcji Małe stwory. PROD bez zmian.

- Lokalnie blok PL/EN przeniesiony z sekcji Małe stwory przed opis dodatku Sweet Revenge. Blok taktyczny pozostał przy wstępie; oprawa PL/EN zachowana po przeniesieniu. PROD bez zmian.

- Lokalnie blok taktyczny: zaakceptowany nowy opis o prostych zasadach, zagadce każdej tury i rewanżu. Nowy podobny blok Wspólna gra. Równe szanse. (FAMILY+) przed sekcją Zobacz, jak to działa, o graniu w parze/trójkę i handicapach przy młodszym dziecku, na podstawie ulotki UVP. PROD bez zmian.

- Lokalnie przed filmami nowy blok 2 GRACZY / We dwoje? W sam raz. podkreślający dobry tryb dwuosobowy. Dotychczasowy FAMILY+ zachowany, przeniesiony niżej przed recenzje. PROD bez zmian.

- Lokalnie hero Kurzołaków: metryka 2–3 graczy, około 30 min, wiek 10+ (ulotka UVP) oraz dopisek o rozgrywce także w 4 osoby z dodatkiem Sweet Revenge. Edytowalne bloki. PROD bez zmian.

- Kurzołaki, podstawka (806): lokalnie włączony rozbudowany widok produktu. Własna treść o taktyce, trzech frakcjach, grze we dwoje, PL/EN oraz zawartości zestawu; ilustracja planszy. Zachowane ceny, stany i galeria. Sprawdzone 390/1440 px, jasny/ciemny motyw, galeria i brak poziomego przewijania. Do wdrożenia razem z pozostałymi poprawkami Kurzołaków.

- Fanpack (804): opis i lista zawartości tylko przy cenie, niżej wyłącznie ilustracja zestawu; usunięty dopisek o ograniczonej ilości oraz dawny ręczny tekst o najniższej cenie. Kolejność Sweet Revenge (805) = 3, Fanpack = 4. Lokalny odbiór strony i kolejności sklepu poprawny.

- Fanpack (804): usunięta cała dolna sekcja z powtórzonym zdjęciem. Sweet Revenge (805): korekta i formatowanie opisów, nagłówek Słodki rewanż, zachowana ilustracja planszetki; usunięta nieaktualna przedsprzedaż i ręczna historyczna cena. Ceny, stany, galeria i kolejność zachowane. Odbiór HTTP obu produktów poprawny, zmiany lokalne.

- Próba wdrożenia aktualizacji Kurzołaków 28.10: paczka backups/kurzolaki-release/20261028/update gotowa. SSH cluster131:22 wielokrotnie przekroczył czas połączenia, przed uruchomieniem importu i zmianą plików serwera. HTTPS strony odpowiada 200. Zmiany nadal oczekują na wdrożenie.

## Kurzołaki — aktualizacja wdrożona 28.10.2026

Wdrożone wszystkie powyższe oczekujące poprawki strony Kurzołaków oraz produktów: podstawka, Fanpack i Sweet Revenge. Fanpack bez dolnej sekcji i powtórzeń, za Sweet Revenge. Sweet Revenge bez przedsprzedaży. Podstawka z rozbudowanym opisem i wspólną galerią. Plama SVG, ruch i bloki taktyka/PL-EN/duet/Family+ oraz metryka są na PROD. Ceny, stany, powiązania i galerie produktów zachowane — importer potwierdził zgodność.

Paczka lokalna backups/kurzolaki-release/20261028/update; kopie treści i kodu oraz receipt na OVH w .wtf-staging/kurzolaki-update-20261028. Odbiór PROD: produkt podstawowy 390/1440 px, jasny/ciemny; strona gry 390/1440 px, menu, grafiki i materiały; treść Fanpack/Sweet Revenge i kolejność sklepu poprawne. Początkowe problemy SSH ustąpiły; wdrożenie zakończone.

- 28.10.2026: zdjęcia produktów Kurzołaki i Sweet Revenge zsynchronizowane z lokalnej biblioteki na PROD. Kurzołaki: główne 3282, galeria 3368/3369/725; Sweet Revenge: główne 3283, galeria 718/3370 (lokalne ID, na serwerze mapowane). Wyłącznie zdjęcia i ich kolejność, bez zmiany opisów/cen/stanów. Odbiór stron i wszystkich zdjęć HTTP 200 poprawny. Paczka backups/kurzolaki-release/20261028/photos, kopie i receipt .wtf-staging/kurzolaki-photos-20261028.

- Lokalnie: animacja plamy hero Kurzołaków usunięta, plama nieruchoma. Do 650 px pudełko przed logo i tekstem (CSS order); desktop zachowuje układ. Kontrola szerokości, kolejności i braku animacji: 360/390/650/768/1440 px. PROD bez zmian.

- Lokalnie zaakceptowane hasło hero Kurzołaków: Słodki cel. Brudne zagrania. Logo z podtytułem bez zmian. Odbiór HTML poprawny; PROD bez zmian.

- Wdrożono na PROD 28.10.2026: hasło Słodki cel. Brudne zagrania.; plama bez animacji; do 650 px pudełko przed logo. Desktop bez zmiany układu. Sprawdzenie 360/390/650/768/1440 px: brak poziomego przewijania i animacji, poprawna kolejność. Kopie strony i CSS: .wtf-staging/kurzolaki-static-hero-20261028. Produkty i galerie bez zmian.

- Lokalnie Plamy (803): opis bez powtórzeń, lista 9 plam według kolorów/frakcji, dolna sekcja Plamy na planszy z dotychczasową ilustracją i podpisem. Usunięty ręczny tekst historycznej ceny. Nazwa, ceny, stan, galeria i kolejność zachowane. Odbiór HTML poprawny; PROD bez zmian.

- Plamy (803): opis i formatowanie wdrożone na PROD 28.10.2026. Odbiór HTML potwierdził pojedynczy opis, listę i ilustrację w sekcji Plamy na planszy. Ceny, stany i zdjęcia zachowane. Kopia i receipt na OVH .wtf-staging/plamy-description-20261028; manifest lokalny backups/kurzolaki-release/20261028/plamy.
