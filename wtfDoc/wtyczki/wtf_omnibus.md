# WTF Omnibus

Wtyczka do WooCommerce do śledzenia historii cen i prezentowania najniższej ceny z 30 dni przed obniżką, zgodnie z polskimi przepisami wdrażającymi dyrektywę Omnibus.

- Na początek dla naszego sklepu, kod wydzielony jako osobna wtyczka `wtf_omnibus`.
- Obsługa produktów prostych i wariantów; każdy wariant ma osobną historię cen.
- Na początek wyłącznie PLN.
- Czytelny panel ułatwiający zarządzanie: Produkty → Historia cen.
- Inicjalizacja i ręczne korekty dostępne dla kierownika sklepu i administratora.
- Początkową cenę odniesienia podaje użytkownik; przy braku wpisu przyjmowana jest bieżąca cena produktu lub wariantu. To wartość inicjalizacyjna, nie odtworzona historia.
- Przy inicjalizacji obecnych promocji przyjmujemy początek obniżki 14 dni przed inicjalizacją.
- Ręczne korekty bez obowiązkowego uzasadnienia i bez rejestru korekt.
- Informacja o najniższej cenie z 30 dni przed obniżką wyświetlana automatycznie przy komunikowaniu obniżki, także na kafelkach i stronie produktu; bez przełącznika. Przy zwykłej cenie bez komunikatu obniżki nie jest wyświetlana.

## Wersja lokalna 0.1.0

- Kod: `wordpress/wp-content/plugins/wtf_omnibus/`. Wtyczka aktywna na 8081; start dev ponownie ją aktywuje, bez nadpisywania danych.
- Panel: Produkty → Historia cen. Wartość początkowa i cena odniesienia edytowalne przy produkcie; kliknięcie nazwy otwiera zarejestrowane ceny. Korekty nie zmieniają ceny sprzedaży WooCommerce.
- Zainicjalizowano istniejące produkty bieżącą ceną, z początkiem obecnych obniżek 14 dni wcześniej. Dane początkowe należy zweryfikować przed produkcją.
- Zmiany przez zapis WooCommerce (panel, API, importer WooCommerce, harmonogram promocji) zapisują cenę i czas UTC w tabeli `{prefix}wtfo_prices`. Stan obniżki i wartość początkowa: `_wtfo_state`. Dezaktywacja zachowuje dane.
- Cena odniesienia pozostaje stała podczas niezmienionej obniżki; kolejna zmiana ceny promocyjnej rozpoczyna nowe wyliczenie. Warianty mają osobne historie i komunikaty. Ceny wyświetlane zgodnie z ustawieniami podatkowymi sklepu.
- Testy: `wp eval-file wp-content/plugins/wtf_omnibus/tools/check.php --allow-root` — tymczasowe produkty usuwane po teście. Sprawdzono także panel, inicjalizację, korekty, stronę produktu i mobilny katalog. Obsługa dotyczy cen i promocji WooCommerce; osobne kampanie reklamowe i zewnętrzne systemy rabatowe nie są automatycznie rozpoznawane.
