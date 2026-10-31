# Meta Pixel

31.10.2026 wdrożony na PROD na polecenie użytkownika. Nowy zestaw danych „What The Frog — strona i sklep”, ID `2354007262067045`, podany przez użytkownika. Starego „Piksela konta Elekt” nie używamy.

Test PROD przeszedł: brak ruchu przed zgodą/po odmowie, niezależna zgoda, pojedyncze `PageView` i `ViewContent`, cofnięcie i usunięcie cookies, pominięcie stron prywatnych. SDK symulowany, ruch zewnętrzny przechwycony — odbiór w panelu Meta nadal do potwierdzenia. Kopie i zakres: [wdrożenie promocji](../srodowiska/promocja-release-20261031.md).

- Integracja we wtyczce `wtf_cookies`: `meta-pixel.php`, `assets/meta-pixel.js`. Ustawienia → WTF Meta Pixel; puste ID wyłącza integrację. Lokalna opcja ustawiona; DEV nie wysyła danych.
- Osobna kategoria `marketing` — „Reklamy Meta”. Wersja zgód 3, poprzednie wybory wymagają ponowienia. Brak pobrania SDK i zdarzeń przed zgodą, po odmowie i bez JavaScript. Bez bezwarunkowego obrazka `noscript`.
- Zakres: `PageView`; na produkcie dodatkowo `ViewContent` z ID i nazwą produktu. Jeden komplet zdarzeń na odsłonę. Bez API konwersji, danych klienta i automatycznych zdarzeń (`autoConfig=false`). Konflikt z inną instalacją `fbq` blokuje start naszej integracji.
- Tylko produkcyjny `whatthefrog.pl`; pominięte konto, koszyk, checkout, wyszukiwanie, błędy i podglądy oraz nieprzewidziane parametry URL. Dodatkowa kontrola referrera. Po wycofaniu zgody: `consent revoke`, czyszczenie kolejki i cookies `_fbp`/`_fbc`, odświeżenie strony z działającym SDK. Czyszczenie cookies działa też na stronach wyłączonych z pomiaru.
- Polityki cookies i prywatności uzupełnione lokalnie; wersje tekstowe w `wtfDoc/sklep/dokumenty/`. Konfiguracja: `local-scripts/one-off/meta-pixel/setup-local.php`; kopia wcześniejszego ID i treści w `/tmp/meta-pixel-before-20261031.json` w kontenerze.

Test: `wtf_cookies/tests/meta-pixel.cjs`, zależności jak w testach cookies. Obejmuje DEV, stare zgody, odmowę, niezależną zgodę marketingową, pojedyncze odsłony/produkty, cofnięcie, cookies, strony prywatne i brak JS. SDK Meta symulowany, cały ruch zewnętrzny przechwycony; test nie potwierdza odbioru w panelu Meta. PHP/JS sprawdzone składniowo.

31.10.2026: test Pixela oraz regresje `tests/check.cjs` i `tests/analytics.cjs` przeszły. Zachowane działanie banera, YouTube, InPost i GA4. Kopia lokalnej konfiguracji i polityk: `backups/meta-pixel/20261031/before.json`.

Po wdrożeniu: sprawdzić odbiór w „Testowaniu zdarzeń” Meta oraz wyłączyć automatyczne zaawansowane dopasowywanie i automatyczne śledzenie zdarzeń w ustawieniach zestawu. Osobny dalszy etap: `AddToCart`, `InitiateCheckout`, `Purchase`, deduplikacja i test poprawności kwot bez wysyłania danych zamówienia ani kluczy w URL. Obecna integracja jeszcze nie mierzy koszyka i zakupu.
