# Analityka i mapa strony

29.10.2026: wdrożone na PROD na polecenie użytkownika, z kopiami plików, ustawień i polityki cookies. GA4: `G-ZLESXQL7WK`. Tag Search Console podany przez użytkownika zapisany lokalnie i na PROD w opcji `wtf_google_site_verification`.

## Pomiar

Integracja w `wtf_cookies/analytics.php` i `assets/analytics.js`. Ustawienia → WTF Analityka: identyfikator pomiaru; puste pole wyłącza integrację. Nowa kategoria `analytics`, wersja zgód 2 — poprzednie wybory wymagają ponowienia. Analytics uruchamiany wyłącznie po zgodzie. Brak skryptu i sygnałów Google przed zgodą/po odmowie (Basic Consent Mode). Funkcje reklamowe wyłączone. Cofnięcie zgody ustawia blokadę GA, usuwa cookies pomiaru i odświeża stronę, aby usunąć bibliotekę. Cookies do 180 dni, bez automatycznego odnawiania.

DEV i staging nie wysyłają pomiarów. Wysyłka wymaga środowiska `production` oraz głównej domeny `whatthefrog.pl`. Administratorzy, wyszukiwanie, 404, konto, koszyk i checkout pomijane. Własne zdarzenia pomijają parametry adresu poza kontrolowaną listą UTM; referrer bez ścieżki i parametrów.

| Zdarzenie | Znaczenie |
| --- | --- |
| `page_view` | Jedna odsłona dokumentu po zgodzie |
| `quiz_start` | Pierwszy wybór odpowiedzi w podejściu |
| `quiz_complete` | Zakończenie quizu przed przejściem na wynik |
| `gamefound_click` | Kliknięcie linku do Gamefound, z miejscem kliknięcia |

Quiz przekazuje tylko `quiz_id`, bez odpowiedzi i archetypu. `content_group`: klanarchia, kurzolaki, elekt, tsukuyumi, echo-swiatow, sklep, ogolne. Strony porównuje się w GA4 w raporcie Strony i ekrany według ścieżki (np. `/quiz-klanarchia/`, `/klanarchia/`, `/kurzolaki/`). Własny wymiar `quiz_id` wymaga rejestracji w panelu GA4; samą liczbę zdarzeń można odczytać bez niego. Liczby obejmują użytkowników ze zgodą i bez blokady pomiaru, nie cały ruch serwera.

## Mapa i Search Console

Mapa WordPressa: `https://whatthefrog.pl/wp-sitemap.xml`. Statyczny robots.txt z Disallow: / zastąpiono produkcyjnym deploy/production/robots.txt; blog_public=1. Publiczny robots dopuszcza skanowanie i wskazuje mapę. Nie stosować do DEV/stagingu.

WTF Core `seo.php` ogranicza mapę do stron i produktów; pomija konto/koszyk/checkout, logowanie/rejestrację, zapowiedź edytora Klanarchii, archiwa taksonomii i użytkowników. Końcowa mapa lokalna: 48 stron i 33 produkty. Ustawienia → Ogólne → Google Search Console: wartość `content` z tagu HTML. Usługa Search Console: prefiks `https://whatthefrog.pl/`.

Przy wdrożeniu: skopiować pliki integracji i SEO wraz z plikami włączającymi oraz zmianami zdarzeń quizu; zachować kopie; zapisać opcje pomiaru i weryfikacji, uzupełnić stronę polityki cookies, podmienić wyłącznie produkcyjny robots. Bez przenoszenia całej bazy. Sprawdzić publiczny robots, mapę, tag, odmowę/zgodę oraz pomiar w GA4. Dopiero potem użytkownik klika Weryfikuj w Search Console i zgłasza `wp-sitemap.xml`.

## Ustawienia na koncie Google — do wykonania

- Adres strumienia zmienić z `http://whatthefrog.games` na `https://whatthefrog.pl`.
- Retencja danych użytkowników/zdarzeń: 14 miesięcy zgodnie z polityką prywatności; wyłączyć reset przy nowej aktywności.
- W pomiarze zaawansowanym wyłączyć automatyczne interakcje z formularzami i zmiany stron na podstawie historii. Zalecane wyłączyć również automatyczne wyszukiwanie i kliki/pobieranie, które mogą zbierać pełne adresy linków; nasz pomiar Gamefound pozostaje aktywny. Nie włączać danych dostarczanych przez użytkownika ani Google Signals.
- Nie podłączono kont Google w narzędziach agenta; weryfikacja usługi, zgłoszenie mapy i powyższe ustawienia wymagają działania użytkownika.

## Weryfikacja lokalna

`wtf_cookies/tests/analytics.cjs`: DEV bez wysyłki, nieaktualna zgoda, odmowa, zgoda tylko na analitykę, jedna odsłona/start, ukończenie quizu, grupy, klik Gamefound, cofnięcie i usunięcie cookies. Opcja `--real-sdk` pobiera prawdziwy skrypt Google, przechwytując wszystkie żądania pomiarowe bez wysyłki do Analytics. Oba warianty przeszły. `WTF_PLAYWRIGHT_MODULE` wskazuje lokalny playwright-core.

Regresja `wtf_cookies/tests/check.cjs` i punktacja quizu przeszły. Składnia PHP/JS poprawna. Kontrola mapy przez lokalny provider: publiczne trzy strony obecne, strony użytkowe i klasy wysyłkowe pominięte. Skrypty lokalnej konfiguracji i odczytu w `local-scripts/one-off/klanarchia/*analytics*20261029.php`; kopia poprzedniej polityki i ustawienia w kontenerze `/tmp/analytics-before-20261029.json`.

Końcowa kontrola 29.10.2026: 14 anonimowych zapytań HEAD do rzeczywistych płatnych MP3 na PROD — wszystkie 403, bez pobierania zawartości. Zmiana robots nie zmienia ochrony plików. Kontrola mapy po odchudzeniu przeszła.

Weryfikacja wdrożenia: publiczny robots/mapa/tag/aktywna konfiguracja/polityka i pominięcie stron użytkowych — poprawne. Test przeglądarkowy na PROD z prawdziwym SDK Google (wszystkie pomiary przechwycone, bez wysyłki testowych danych) — odmowa, zgoda, zdarzenia quizu, grupy, Gamefound, cofnięcie i kasowanie cookies poprawne. Otrzymania zdarzeń w panelu GA4 nie sprawdzono — brak dostępu do konta. Kopie: .wtf-staging/analytics-release-20261029/before/; paczka, receipt i raport publiczny: backups/analytics-release-20261029/. Zmieniono sześć plików wtyczek, pojedynczy include w WTF Core, robots, dwie opcje i uzupełnienie polityki cookies; bez zastępowania bazy.

29.10.2026: użytkownik potwierdził zaakceptowanie mapy w Search Console oraz odbiór danych w GA4. Wcześniejsza blokada Google wynikała ze starej kopii robots (26 B); po odświeżeniu zgłoszenie mapy zaakceptowane.
