# WTF Core

Własna techniczna wtyczka projektu docelowego `wtf_www`. Źródła: `wordpress/wp-content/plugins/wtf-core/`. Nie modyfikuje kodu WordPressa ani Polylang.

## Zakres

- Media: wielokrotne grupy plików, filtry siatki/listy i wyboru grafiki oraz początkowe przypisanie istniejących plików. Opis: [[panel-wordpress/grupy-mediow|grupy-mediow]].

- Strony PL/EN: rozwijane grupy gier, filtr, wyszukiwanie i ręczne przypisanie grupy w edytorze, wspólne dla pary. Opis: [[panel-wordpress/strony-pl-en|strony-pl-en]].

- Rejestruje edytowalne bloki katalogu Wszystkie gry, w tym publikacje i odsłuch, oraz ręczny import. Opis: [[przenoszenie/przeniesienie-katalogu|przeniesienie-katalogu]].

- Rejestruje bloki treści Elekta i Kurzołaków oraz ręczne importy tych stron. Opisy: [[przenoszenie/przeniesienie-elekta|przeniesienie-elekta]], [[przenoszenie/przeniesienie-kurzolakow|przeniesienie-kurzolakow]].

- Rejestruje edytowalne bloki Echo Światów, w tym audio wybierane z Mediów. Import jest uruchamiany ręcznie; treści pozostają w bazie. Opis: [[przenoszenie/przeniesienie-echa-swiatow|przeniesienie-echa-swiatow]].

- Rejestruje bloki układów, tekstów, grafik i filmów Tsukuyumi. Treści są zapisane w bazie stron; JSON eksportu służy wyłącznie jawnie uruchamianemu importowi. Opis: [[przenoszenie/przeniesienie-tsukuyumi|przeniesienie-tsukuyumi]].

- Wyświetla grafiki banera, kart, menu gier i promocji z metadanych biblioteki, z doborem rozmiarów `srcset`/`sizes`. Konwersję wykonuje osobna darmowa wtyczka Modern Image Formats. Szczegóły: [[panel-wordpress/optymalizacja-grafik|optymalizacja-grafik]].

- Rejestruje i renderuje edytowalne bloki: baner, karty gier, promocję, wydawnictwo, zaproszenie do sklepu, nagłówek i stopkę. Treści zapisują się w bazie w atrybutach bloków.
- Rejestruje wspólne napisy nagłówka i stopki w panelu tłumaczeń Polylang.
- Zawiera skrypty WP-CLI pierwszej konfiguracji: media, główna PL/EN i połączenie tłumaczeń. Nie uruchamia ich podczas zwykłych odwiedzin strony.
- Udostępnia punkt integracji przyszłych modułów React: język, początkowy motyw i kontener aplikacji. Konkretna aplikacja nie jest jeszcze wdrożona.
- Dodaje menu informacyjne WTF Core oraz osobne menu Strony PL/EN.

Nie realizuje płatności, wielowalutowości ani zakupów. Wygląd strony i jej zachowanie w przeglądarce należą do motywu `wtf`. Szczegóły pierwszego etapu: [[przenoszenie/implementacja-etap-1|implementacja-etap-1]].

## Grupowanie stron — 2026-10-11

Użytkownik zlecił dodanie menu wtyczki po lewej oraz opcji grupowania listy stron. Implementacja w `admin.php`, wczytywanym tylko w panelu administracyjnym.

**WTF Core → Ustawienia → Grupuj strony według tłumaczeń PL/EN → Zapisz ustawienia.** Ustawienie jest zapisywane w bazie jako `wtf_group_pages`. Domyślnie jest wyłączone w nowej instalacji; w bieżącym dev zostało włączone podczas weryfikacji.

Po włączeniu kliknięcie standardowego menu „Strony” przekierowuje na widok **WTF Core → Strony PL/EN**:

- Jeden wiersz na powiązaną parę, kolumny Strona, PL i EN; nazwa grupy pochodzi z polskiej wersji, a jeśli jej nie ma — angielskiej.
- Linki do edycji konkretnych wersji wraz z tytułem i statusem.
- Przycisk „Dodaj tłumaczenie” dla brakującej wersji, z parametrami i nonce Polylang. Nowa wersja otwiera się w edytorze; ten przycisk nie publikuje strony.
- Strony bez języka są widoczne z linkiem do edycji i przypisania języka.
- Wyszukiwanie po tytule PL lub EN; paginacja po 20 grup.
- Link „Standardowa lista stron” omija przekierowanie (`wtf_view=standard`), umożliwiając standardowe akcje zbiorcze i kosz. Żądania akcji i kosza nie są przekierowywane.

Lista odczytuje istniejące powiązania Polylang. Nie zmienia tłumaczeń, tytułów, kolejności stron w menu witryny ani publicznych adresów. Dotyczy stron WordPressa, nie produktów i wpisów. Wyłączenie opcji przywraca standardowe menu „Strony”; własny widok pozostaje dostępny przez WTF Core.

Informacyjny ekran WTF Core wymaga `manage_options`. Lista PL/EN wymaga `edit_pages`, a linki do wersji uwzgl?dniaj? uprawnienie edycji danej strony. Bez aktywnego Polylang widok informuje o brakuj?cej zale?no?ci; standardowa lista pozostaje dost?pna.

## Pliki

- `wtf-core.php`: bloki, renderowanie, tłumaczenia wspólnych napisów i kontrakt React.
- `editor.js`: formularze edycji bloków w Gutenbergu.
- `admin.php`: menu wtyczki i osobna grupowana lista stron.
- `setup-home.php`, `setup-languages.php`, `english.json`: pierwsza inicjalizacja lokalna i początkowe treści EN z prototypu.
- `repair-initial-content.php`: jednorazowa korekta nowych linii po pierwszym imporcie dev; nie jest częścią standardowego startu.

## Weryfikacja listy

Sprawdzono w przeglądarce dwa niezale?ne menu, brak checkboxa i przekierowania, jeden wiersz dla „Strona główna” i „Home”, linki do obu edytorów, wyszukiwanie po angielskim tytule oraz dostęp do standardowej listy. Test: `preview/verify-page-groups.cjs` w projekcie referencyjnym; screenshot `preview/wtf-page-groups.png`.Panel ma dwa niezale?ne menu po lewej: **Strony** otwiera standardow? list? WordPressa, a **Strony PL/EN** zawsze otwiera widok grupowany. WTF Core zawiera opis technicznej wtyczki. Nie ma checkboxa grupowania ani przekierowania standardowej listy; usuni?to dawn? opcj? `wtf_group_pages` z bazy dev. Implementacja w `admin.php`, wczytywanym tylko w panelu administracyjnym.

Widok Strony PL/EN:

- Jeden wiersz na powiązaną parę, kolumny Strona, PL i EN; nazwa grupy pochodzi z polskiej wersji, a jeśli jej nie ma — angielskiej.
- Linki do edycji konkretnych wersji wraz z tytułem i statusem.
- Przycisk „Dodaj tłumaczenie” dla brakującej wersji, z parametrami i nonce Polylang. Nowa wersja otwiera się w edytorze; ten przycisk nie publikuje strony.
- Strony bez języka są widoczne z linkiem do edycji i przypisania języka.
- Wyszukiwanie po tytule PL lub EN; paginacja po 20 grup.
- Link „Standardowa lista stron” omija przekierowanie (`wtf_view=standard`), umożliwiając standardowe akcje zbiorcze i kosz. Żądania akcji i kosza nie są przekierowywane.

Lista odczytuje istniejące powiązania Polylang. Nie zmienia tłumaczeń, tytułów, kolejności stron w menu witryny ani publicznych adresów. Dotyczy stron WordPressa, nie produktów i wpisów. Wyłączenie opcji przywraca standardowe menu „Strony”; własny widok pozostaje dostępny przez WTF Core.

Zmiana ustawienia wymaga `manage_options` i standardowego nonce Settings API. Lista wymaga `edit_pages`, a linki do wersji uwzględniają uprawnienie edycji danej strony. Bez aktywnego Polylang nie następuje przekierowanie; widok informuje o brakującej zależności.

## Pliki

- `wtf-core.php`: bloki, renderowanie, tłumaczenia wspólnych napisów i kontrakt React.
- `editor.js`: formularze edycji bloków w Gutenbergu.
- `admin.php`: menu wtyczki, ustawienie i grupowana lista stron.
- `setup-home.php`, `setup-languages.php`, `english.json`: pierwsza inicjalizacja lokalna i początkowe treści EN z prototypu.
- `repair-initial-content.php`: jednorazowa korekta nowych linii po pierwszym imporcie dev; nie jest częścią standardowego startu.

## Weryfikacja listy

Sprawdzono w przeglądarce zapis opcji, przekierowanie z menu Strony, jeden wiersz dla „Strona główna” i „Home”, linki do obu edytorów, wyszukiwanie po angielskim tytule oraz dostęp do standardowej listy. Test: `preview/verify-page-groups.cjs` w projekcie referencyjnym; screenshot `preview/wtf-page-groups.png`.
