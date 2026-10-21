# Tsukuyumi w wersji docelowej

- Usunięto powtórzenia zajawek w pełnych opisach frakcji, także w polskich treściach szkiców EN. Dodatkowe informacje zachowane, w tym historia ONI i zdolności Gospel Bearer. Puste nagłówki „Fabularnie” po usunięciu duplikatu również usunięte. Skrypt: `wtf-core/tools/remove-faction-duplicates.php` (bez argumentu tylko sprawdza, `apply` zapisuje).

- Przejrzano wszystkie 16 frakcji: korekta wyłącznie błędów pisowni, odmiany, interpunkcji i typografii, bez przeredagowywania stylu, zasad i liczb. Twarde spacje po jednoliterowych słowach. Długie listy zawartości (co najmniej 8 pozycji) mają dwie kolumny na desktopie, jedną do 650 px. Krótka lista Dark Crusade pozostaje jednokolumnowa. Dotyczy PL i polskich treści szkiców EN; liczby porównane z poprzednimi zapisami. Skrypt: `wtf-core/tools/correct-faction-texts.php`.

- Moon Circus: dwie ilustracje przed „Zawartość” zgrupowane w edytowalnym układzie — obok siebie na desktopie, jedna pod drugą na telefonie. Zachowane grafiki wybrane w edytorze. Poprawka PL 76 / EN 77: `wtf-core/tools/fix-moon-circus-art.php`.
- Moon Circus: „Taktyka” ma większy tekst, czytelne odstępy i ograniczoną długość wiersza. Lista „Zawartość” ma dwie kolumny na desktopie, jedną na telefonie. Sekcje pozostają edytowalne; treści bez zmian.
- Moon Circus: korekta polskiego opisu i interpunkcji, spójny zapis Uberclowns, twarde spacje po jednoliterowych słowach. Liczby i zasady bez zmian; korekta także w polskich treściach szkicu EN. Skrypt: `wtf-core/tools/edit-moon-circus-text.php`.

- Frakcje: wspólny rząd ramek z metryką i cechami specjalnymi, na telefonie jedna pod drugą. Dark Crusade ma dodatkowo zawartość; Władcy Utraconych Mórz na razie samą metrykę, bez dopisywania brakujących cech. Układ także w szkicach EN. FAQ zachowane.

## Status — 2026-10-13

Użytkownik wznowił przenoszenie, wskazując Tsukuyumi jako kolejną stronę. W `wtf_www` przeniesiono stronę gry Full Moon Down / Sunrise Kingdom oraz wszystkie 16 podstron frakcji z prototypu. Inne podstrony, sklep i produkcja pozostają poza zakresem tego etapu.

- Strona PL: ID 68, http://localhost:8081/tsukuyumi/.
- Powiązany szkic EN: ID 69.
- Frakcje: PL 52, 54, 56, 58, 60, 62, 64, 66, 70, 72, 74, 76, 78, 80, 82, 84; odpowiadające EN mają ID o jeden większe.
- Ścieżki frakcji PL muszą być takie jak na oryginalnej stronie — są w kodach QR. Strony są dziećmi Tsukuyumi, np. `/tsukuyumi/cs/`, `/tsukuyumi/nom/`, `/tsukuyumi/reef/`. Nazwy ścieżek pochodzą z pól `source` danych prototypu. Poprawiono linki w stronach i FAQ.
- Wszystkie 17 par są powiązane w Polylang i widoczne w **Strony PL/EN**.
- EN pozostaje szkicem: interfejs i część streszczeń pochodzą z angielskiego wariantu prototypu, lecz pełne opisy frakcji i szczegółowe zasady nadal są po polsku. Nie opublikowano ich jako gotowych tłumaczeń.
- 2026-10-13: na prośbę użytkownika załącznik ID 7 ma tytuł i slug `tsukuyumi_pudelko`, co zwolniło `/tsukuyumi/` dla strony ID 68. Zaktualizowano linki w blokach; plik grafiki pozostaje ten sam. Nadal identyfikujemy strony przez meta `_wtf_tsu_key`.

## Zachowany układ

Panorama desktopowa i osobny kadr mobilny, przezroczysty nagłówek na ilustracji, hasło poniżej; oprawa biała, czarna i czerwona z fakturą heksów. Dalej Film, Świat, Full Moon Down, Zasady, Sunrise Kingdom, wspólna lista 16 frakcji, Nowe Lądy i Gracz+, Recenzje i Pobierz.

Menu boczne, przycisk na górę, mobilne „Na tej stronie” oraz zwijanie Frakcji i Rozszerzeń zachowują zachowania prototypu. Zasady mają pięć domyślnie zamkniętych pól szczegółowych; film wprowadzający, cztery filmy zasad i jedenaście recenzji/innych filmów — łącznie 16 osadzeń.

Zachowano potwierdzone odnośniki zakupu do istniejącego sklepu i zewnętrzne PDF. Nie przenoszono produktów WooCommerce. Pozostałe linki frakcji oraz powroty prowadzą lokalnie. Link Tsukuyumi na głównej i w globalnym menu również prowadzi lokalnie, zachowując podmienione przez użytkownika grafiki i teksty.

## Edycja

W **Strony PL/EN** otwórz wersję PL Tsukuyumi. W widoku listy bloków rozdziały mają nazwy odpowiadające ich nagłówkom. Teksty i linki można zmieniać w blokach **Tekst Tsukuyumi**, obrazy wybierać z biblioteki w **Grafika Tsukuyumi**, a adresy i tytuły filmów w **Film Tsukuyumi**. Kontenery grupują rozdziały, wiersze i akordeony; nie przechowują treści w PHP.

Przy grafikach są wskazówki wymiarów i kadrowania. Panorama ma osobne pola desktop/mobile: zalecane 1920 × 1080 oraz 1080 × 1920 px. Pozostałe ilustracje: zalecana szerokość 1200 px, z zachowaniem proporcji źródła. Konwersję WebP wykonuje Modern Image Formats, a renderowanie korzysta z metadanych Mediów.

Wyjątki od edycji wizualnej: źródłowe tabele jednostek i funkcjonalna nawigacja boczna pozostają blokami **Własny HTML**, aby zachować strukturę i dostępność. Ich kod jest zapisany w treści strony, nie w szablonie. Większość treści jest rozbita na osobne edytowalne bloki; główna strona zawiera 464 bloki, w tym 250 tekstowych i 25 grafik.

## Kod i import

- Jeden istniejący motyw `wtf`: szablon `templates/tsukuyumi.html`, CSS `assets/tsukuyumi.css`, JS `assets/tsukuyumi.js`, warunkowe ładowanie i klasa w `functions.php`.
- Istniejąca wtyczka `wtf-core`: `tsukuyumi.php` rejestruje bloki i lokalizuje domyślne odnośniki gry; `tsukuyumi-editor.js` dostarcza edycję.
- `tsukuyumi-seed.json`: eksport HTML prototypu używany wyłącznie przy imporcie. Nie jest źródłem treści podczas odwiedzin strony.
- `setup-tsukuyumi.php`: jawnie uruchamiana migracja WP-CLI. Nie uruchamia się podczas startu ani odwiedzin. Działa lokalnie na przygotowanych mediach; produkcyjna migracja pozostaje osobnym procesem.
- Meta `_wtf_tsu_key` zawiera klucz `main:pl` / `slug-frakcji:pl` i odpowiedniki EN. `_wtf_tsu_imported` chroni zapisane treści przed ponownym importem, `_wtf_tsu_kind` określa stronę gry lub frakcji.
- Załączniki rozpoznawane są także przez `original_image` po konwersji i `_wtf_source_asset`, aby ponowny import nie powielał mediów.
- Przeniesiono 78 wskazanych plików źródłowych; dostępne w `media/` projektu docelowego i bibliotece WordPressa. Import respektuje istniejące załączniki. Loga/faktura SVG są znanymi lokalnymi assetami; nie odblokowano dowolnego uploadu SVG.

Import na środowisku z przygotowanymi plikami:

```text
docker exec wtf-www-dev-wordpress-1 wp eval-file wp-content/plugins/wtf-core/setup-tsukuyumi.php --allow-root
```

## Weryfikacja

- Składnia PHP i JavaScript poprawna.
- `preview/verify-tsukuyumi.cjs`: HTTP 200 dla 16/16 frakcji, 16 filmów, poprawne kotwice i lokalne linki, panorama WebP desktop/mobile, brak poziomego przepełnienia, przełączniki list i powrót na górę, zero błędów JS. Podglądy desktop 1440 px i mobile 390 px sprawdzono wizualnie.
- `preview/verify-tsukuyumi-editor.cjs`: 464 poprawne bloki, zapis zmiany nagłówka w edytorze i odczyt na stronie; testowy dopisek usunięty i zapisany pierwotny tekst.
- `preview/verify-tsukuyumi-import.php`: 34 strony, 17 par, właściwe statusy, ponowny import zachowuje treść i nie dodaje mediów. Podczas dopracowania testu usunięto nieużywane rekordy duplikatów, zachowując pliki i używane załączniki.

Skrypty QA i zrzuty znajdują się w `preview/` projektu referencyjnego. Nie są elementem publicznej strony.

## Układ postaci ONI

Na /tsukuyumi/oni/ sześć legendarnych postaci ma grafikę po lewej oraz nazwę, opis i zdolności po prawej. Do 650 px układ pionowy. Treści nadal w edytowalnych blokach. Ręczny, idempotentny skrypt: wordpress/wp-content/plugins/wtf-core/tools/layout-oni.php (WP-CLI eval-file); zachowuje kopię poprzedniej treści w metadanych strony. Sprawdzono sześć wierszy przy 1440 px i układ pionowy przy 390 px, bez poziomego przepełnienia.

## Opis Władców Utraconych Mórz

Uzupełniono PL /tsukuyumi/wum/ (ID 82) na podstawie strony 7973 w lokalnej kopii whatthefrogpl_backup (slug wum-2). Przeniesiono pełną fabułę, sześć akapitów taktyki i trzy cechy specjalne; potwierdzono tryb dla 2 graczy. Teksty pozostają edytowalnymi blokami WTF Core. EN pozostaje bez zmian. Ręczny skrypt: wordpress/wp-content/plugins/wtf-core/tools/restore-wum-description.php, dane źródłowe w wum-description.json. Kopia wcześniejszej treści w metadanych strony. Sprawdzono HTTP 200 i zgodność wszystkich akapitów ze źródłem oraz obecność FAQ. Dodano również ilustracje Wladcy, Anik/Malak i Król oraz osiem zdjęć galerii z podpisem @photoboardgamer, w kolejności źródłowej. Pliki w media/tsu-wum-*, zarejestrowane w Mediach w grupie Tsukuyumi; bloki grafik pozostają edytowalne. Galeria: dwie kolumny na komputerze, jedna na telefonie, odnośniki do pełnych zdjęć. Ręczny skrypt restore-wum-images.php zachowuje poprzednią treść i nie powiela importu. Sprawdzono ładowanie wszystkich 11 grafik przy 1440 i 390 px oraz brak poziomego przepełnienia.

Na życzenie usunięto z treści WUM parę ilustracji Anik/Malak i Król pod taktyką. Ilustracja fabularna i galeria ośmiu zdjęć pozostają. Pliki zachowano w Mediach. Ręczny skrypt: remove-wum-unit-art.php.

Galeria WUM: po obejrzeniu ośmiu fotografii nadano indywidualne tytuły, ALT opisujące kadry, podpisy z autorem oraz opisy w Mediach. ALT zsynchronizowano z blokami strony. Potwierdzono osiem unikalnych ALT w HTML i grupę Tsukuyumi dla wszystkich zdjęć. Ręczny skrypt: describe-wum-gallery.php; zachowuje poprzednie metadane i treść strony.

Lista 16 frakcji na głównej Tsukuyumi PL oraz podmenu Frakcje mają kolejność alfabetyczną według wyświetlanych nazw (polskie reguły sortowania). Zachowano treści, kotwice i adresy podstron; EN bez zmian. Ręczny skrypt sort-tsu-factions.php, kolejność tsu-faction-order.json. Sprawdzono zgodność kolejności wszystkich kart i linków menu w HTML.
