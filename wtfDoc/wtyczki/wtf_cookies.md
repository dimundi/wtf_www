# wtf_cookies

Samodzielna wtyczka lokalna, wersja 0.1.0. Bez uruchamiania Analytics i Meta Pixel. Start DEV aktywuje ją automatycznie; PROD nie zmieniono.

- Baner: „Akceptuję wszystkie”, „Tylko niezbędne”, „Ustawienia”. Równorzędne przyciski akceptacji i odmowy, checkboxy domyślnie wyłączone.
- Ustawienia cookies w stopce; dialog obsługuje klawiaturę i powrót fokusu. Zgoda na YouTube może być udzielona również przy filmie, bez włączania innych kategorii.
- Cookie `wtf_cookies_consent`: kategorie, wersja i czas wyboru, bez identyfikatora użytkownika, 180 dni, ścieżka `/`, SameSite=Lax, Secure na HTTPS. Odmowa jest zapamiętywana tak samo jak zgoda. Uszkodzony, przeterminowany lub nieaktualny zapis oznacza brak zgody.
- Blokowanie po stronie PHP: iframe YouTube i podglądy Elekta ze zdalnymi miniaturami są zastępowane informacją i przyciskiem. Nie zależy od odczytu cookie na serwerze, więc strona może być bezpiecznie cache'owana wspólnie. MP4 lokalne pozostają bez zmian. Bez JavaScript YouTube pozostaje zablokowany, dostępny jest bezpośredni link.
- Cofnięcie zgody usuwa działające odtwarzacze; aktualizacja następuje także po powrocie na kartę. Nie da się z naszej domeny skasować cookies Google — użytkownik może usunąć je w przeglądarce. Nowa zgoda nie przywraca wycofanych kategorii automatycznie.
- Mapa InPost: zewnętrzne CSS i SDK pobierane dopiero po żądaniu wyboru Paczkomatu, bez uzależniania dostawy od zgody na YouTube. W razie błędu można ponowić próbę. Nie zmieniono tokenów ani ustawień integracji.
- Zbędny `pll_language` wygaszany, gdy WTF Core ma wyłączoną wersję EN. Przy przyszłym uruchomieniu EN ponownie ocenić cookie preferencji języka.
- Panel informacyjny: Ustawienia → WTF Cookies. Treści polityki nadal edytowane w Strony → Polityka cookies.

## Rozbudowa

Kategorie dodaje filtr PHP `wtf_cookies_categories`: klucz → `label`, `description`. Zmiana celów lub zakresu wymaga zwiększenia `WTF_COOKIES_REVISION` i aktualizacji dokumentów. Dodanie nowego klucza również unieważnia poprzedni zapis, który go nie zawiera.

API przeglądarki: `window.wtfCookies.hasConsent(category)`, `getConsent()`, `open()` i `registerService(id, {category, start, stop})`. Rejestracja uruchamia usługę tylko dla zaakceptowanej kategorii. `stop` musi zatrzymać pomiar, timery i usunąć własne cookies; samo usunięcie znacznika `<script>` nie zatrzyma działającej biblioteki. Zdarzenia: `wtf:cookies-ready` oraz `wtf:cookies-change` (aktualne i poprzednie kategorie).

Skrypt wtyczki działa w nagłówku, przed footerem. Integracje rejestrować po nim lub po gotowości DOM. Nowe skrypty analityczne wolno pobrać dopiero w `start`; nie dodawać wcześniej aktywnego `gtag`, GTM ani pikseli. Wtyczka nie przechwytuje dowolnych skryptów wstawionych przez inne wtyczki — każda nowa integracja wymaga podłączenia i testu sieciowego. Nie wstawiać adresów serwisów w atrybutach `src` przed zgodą.

## Weryfikacja

Powtarzalny test: `wordpress/wp-content/plugins/wtf_cookies/tests/check.cjs`. Wymaga `playwright-core` i zainstalowanego Chrome; zależność testowa w `tests/package.json`, bez zależności produkcyjnych. Uruchomienie z katalogu repozytorium: `node wordpress/wp-content/plugins/wtf_cookies/tests/check.cjs`; można wskazać zewnętrzny moduł przez `WTF_PLAYWRIGHT_MODULE`. Wyniki i zrzuty w ignorowanym `output/cookies/` lub w `WTF_COOKIES_ARTIFACTS`.

Test przeglądarkowy obejmuje świeżą wizytę bez połączeń z Google, odmowę i ponowne wejście, zgodę na film, usunięcie iframe po wycofaniu, cykl `start/stop`, błędne cookie i zmianę wersji, układ 360 px, Escape i powrót fokusu, Tsukuyumi, SDK InPost dopiero po kliknięciu oraz działanie bez JavaScript. Ruch zewnętrzny jest przechwytywany i blokowany; SDK InPost zastąpiono atrapą — nie jest to test realnej dostępności usługi ani wyboru punktu. Bez składania zamówień i wysyłki maili. Składnia PHP/JS poprawna.

Przed przyszłym uruchomieniem Analytics sprawdzić brak żądań analitycznych przed zgodą i po odmowie, retencję oraz cofanie zgody na rzeczywistym SDK.
