# Migracja danych sklepu

## Ustalenia — 15.10.2026

- Początkową ofertę importujemy z produktów widocznych w katalogu starego sklepu, do dalszej edycji. Zachowujemy ścieżki adresów i zapisujemy mapowania nowych ID. Zakres: [uruchomienie sklepu](uruchomienie-sklepu.md).
- Zachowujemy historię zamówień. Pozycje zamówień mają pozostać zgodne z zakupem: nazwa z chwili zakupu, ilość, cena, podatki i zapisane warianty. Nowa oferta nie zmienia tych danych.
- Przenosimy wyłącznie konta klientów, zachowując hasła i powiązania z zamówieniami. Nadajemy im rolę WooCommerce „Klient” (`customer`), bez kopiowania dawnych ról i uprawnień. Kont administratorów i innych kont nieklienckich nie przenosimy.
- Przed uruchomieniem nowej strony uwzględniamy zakupy i pozostałe dane powstałe na produkcji podczas przebudowy.

## Historyczne produkty w zamówieniach

- Produktów fizycznych ani ich wariantów nie wiążemy z nową ofertą — również gdy nowy produkt ma tę samą nazwę.
- Dla produktów fizycznych w starych zamówieniach pozostają wyłącznie historyczne informacje o pozycjach zakupu, bez linku do nowego produktu i bez odtwarzania tych pozycji przez „zamów ponownie”.
- Przy migracji produktów fizycznych usuwamy aktywne powiązania ze starymi identyfikatorami produktów i wariantów, aby nie wskazały przypadkowych nowych produktów. Nazwy, ilości, ceny, podatki i zapisane cechy wariantów pozostają zachowane.
- **Wyjątek: produkty cyfrowe.** Wiążemy dawne produkty i warianty cyfrowe z odpowiednikami w nowym sklepie oraz przenosimy powiązania plików i uprawnienia do pobierania. Klienci zachowują dostęp wynikający z zakupów, w tym zapisane limity i terminy ważności. Historyczne dane pozycji zamówień pozostają bez zmian. Konkretne produkty i pliki dopasujemy podczas przygotowania oferty.

## Kupony

- Przenosimy wszystkie kupony, zachowując ciągłość: kody, statusy, zasady rabatu, terminy, limity i dotychczasowe wykorzystanie. Żadnych kuponów nie kasujemy; zachowujemy również kupony wygasłe i wykorzystane wraz z ich dotychczasowymi ograniczeniami.
- Kupony powiązane z produktami, wariantami lub kategoriami przypisujemy do odpowiedników w nowej ofercie. Dotyczy to także produktów fizycznych; jest to powiązanie kuponu, niezależne od historycznych pozycji zamówień.
- Kupony przenosimy pod koniec przygotowania sklepu, po utworzeniu produktów, wariantów i kategorii. Przy końcowej synchronizacji uwzględniamy najnowsze wykorzystanie kuponów.
- Przygotujemy wtyczkę albo funkcję do hurtowego tworzenia unikalnych kodów kuponów przeznaczonych dla pojedynczego produktu. Szczegóły — rodzaj rabatu, liczba kodów, format, ważność, limity wykorzystania i eksport — ustalimy później. „Unikalny” oznacza różne kody; jednorazowość wymaga osobnego ustalenia.
- Na tym etapie zapisujemy wymaganie, bez implementacji.

## Płatności, dostawy i integracje

- Na pierwszy etap zachowujemy obecne metody płatności, dostawy i integracje starego sklepu. Ich istniejącą konfigurację sprawdzamy na lokalnej kopii przed odtworzeniem w nowym sklepie.
- Dodatkowe funkcjonalności i rozszerzenia planujemy po uruchomieniu sklepu.

## Przekierowania adresów

- Stałe przekierowania zmienionych adresów obsługujemy w proxy nginx, przed WordPressem: mapa stary adres → nowy adres, przekierowania 301.
- Listę utrzymujemy w osobnym pliku w Git; nginx korzysta z niej przez `map`. Konkretne adresy ustalimy po przygotowaniu nowej oferty.
- Zachowujemy dotychczasowe ścieżki gier i frakcji, zwłaszcza adresy zapisane w kodach QR. Przekierowania dotyczą adresów rzeczywiście zmienionych.
- Dawne linki pobierania produktów cyfrowych rozpatrujemy osobno, zachowując kontrolę uprawnień WooCommerce; nie przekierowujemy ich bezpośrednio do publicznego pliku.

Źródło: [map w nginx](https://nginx.org/en/docs/http/ngx_http_map_module.html).

## MailPoet

- Przenosimy możliwie całość: subskrybentów, listy, zgody, wypisania, formularze, wiadomości i szablony, historię wysyłek, statystyki, automatyzacje oraz ustawienia.
- Zachowujemy statusy subskrypcji i wypisania. Zgodność danych i konfigurację na nowym serwerze sprawdzimy na kopii; ewentualne ograniczenia opiszemy przed migracją.

## Udostępnianie produktów cyfrowych — do szczegółowego omówienia

- WooCommerce ma wbudowaną obsługę plików do pobrania: dostęp po potwierdzeniu płatności, link w mailu i w koncie klienta, opcjonalne limity pobrań, termin ważności oraz wymóg logowania.
- Dostępne metody: wymuszone pobieranie przez PHP, wysyłanie przez serwer (X-Accel-Redirect dla nginx / X-Sendfile dla Apache) oraz przekierowanie na bezpośredni URL. Ostatnia metoda ujawnia adres pliku i nie chroni przed udostępnianiem linku.
- Propozycja dla VPS: chroniony katalog poza kontenerem, kontrola uprawnień przez WooCommerce i przesyłanie pliku przez serwer. Płatne pliki wymagają ochrony przed bezpośrednim dostępem; zwykła biblioteka Mediów jest publiczna.
- **To propozycja, nie zatwierdzona konfiguracja.** Szczegółowo omówimy metodę, lokalizację plików, zasady dostępu, limity, logowanie i zachowanie uprawnień po migracji. Przed wyborem sprawdzimy obecne rozwiązanie starego sklepu.
- Ochrona pobierania nie uniemożliwia przekazania pliku dalej po jego pobraniu.

Źródło: [obsługa produktów cyfrowych w WooCommerce](https://woocommerce.com/document/digital-downloadable-product-handling/).

Narzędzia i procedura migracji pozostają do wyboru. Stany magazynowe i media produktów omówimy osobno. Na razie ustalamy zasady i dokumentujemy je, bez implementacji.
