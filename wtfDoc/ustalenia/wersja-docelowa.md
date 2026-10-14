# Przygotowanie wersji docelowej

## Status

2026-10-13 użytkownik wznowił przenoszenie, zlecając Tsukuyumi. Przeniesiono stronę gry i 16 frakcji do edytowalnych bloków `wtf_www`; wersje EN pozostają powiązanymi szkicami do uzupełnienia tłumaczeń. Opis: [[przenoszenie/przeniesienie-tsukuyumi|przeniesienie-tsukuyumi]]. Dawne zatrzymanie po punkcie 3 zostało wznowione wyłącznie w tym zakresie.

2026-10-11 użytkownik potwierdził zakończenie prototypowania i rozpoczęcie przygotowania wersji docelowej. Dokumenty `prototyp-*.md` opisują referencje oraz historię prac; zakończenie tego etapu nie oznacza zatwierdzenia każdego szczegółu ani gotowości kodu do produkcji.

W kolejnym ustaleniu z 2026-10-11 użytkownik wybrał osobny projekt `wtf_www` dla wersji docelowej. Obecny projekt `wtf_web_page` i motyw `wtf-prototype` pozostają referencją. Po początkowym odłożeniu prac użytkownik wznowił implementację. Utworzono osobny projekt i wykonano punkty 1–3: środowisko na 8081, fundament motywu i edytowalną główną PL/EN. Dalsze przenoszenie zatrzymane na ocenę użytkownika. Szczegóły i weryfikacja: [[przenoszenie/implementacja-etap-1|implementacja-etap-1]].

## Uzgodniony pierwszy etap

Użytkownik zaakceptował plan z zatrzymaniem po punkcie 3:

1. Przygotowanie osobnego projektu `wtf_www`: repozytorium, lokalny WordPress, osobna baza i kontenery. Prototyp pozostaje uruchamialną referencją.
2. Fundament: jeden własny motyw blokowy, edycja w standardowym edytorze WordPressa (Gutenberg), wspólne style i warianty opraw stron, jasny/ciemny wariant oraz wtyczka WTF dla złożonych bloków i funkcji. Dla stron wdrożono darmowy Polylang; integracja języka przyszłego WooCommerce pozostaje do ustalenia.
3. Edytowalna strona główna: wspólne menu i stopka oraz baner, „W co dziś gramy?”, promocja, gry dla dwóch graczy, opis wydawnictwa i zaproszenie do sklepu. Treści w edytorze, formatowanie w motywie, przygotowane wzorce i warianty sekcji. Sprawdzenie wygody edycji i widoków desktop/mobile.

Po punkcie 3 zatrzymujemy się na ocenę użytkownika. Przenoszenie kolejnych podstron, uruchamianie docelowego sklepu i wdrożenie pozostają późniejszymi etapami, poza tym zakresem.

Technicznie witryna korzysta z jednego motywu; wybrane strony mogą mieć odmienne oprawy. CSS, JavaScript i szablony należą do motywu, treści do WordPressa, złożone funkcje i bloki do własnej wtyczki. Produkty, ceny i kupony docelowo obsługuje WooCommerce.

## Wymagania zachowane

### Interaktywne moduły React

Ustalenie z 2026-10-11: w niedalekiej przyszłości będziemy dodawać bardziej interaktywne moduły napisane w React, np. edytor kart postaci. Mogą zajmować fragment strony albo stanowić całą stronę. Należy uwzględnić ich integrację przy przygotowaniu architektury docelowej.

WordPress przekazuje modułowi aktualny język strony jako konfigurację podczas jego uruchamiania. W omawianym wariancie z Polylang język pochodzi z tego systemu. Moduł może mieć własne słowniki PL/EN i kolejnych języków; nie wymaga rejestrowania każdego napisu w Polylang. Wybór języka strony ma określać również język osadzonego modułu.

Szczegóły osadzania, przekazywania konfiguracji i budowania modułów pozostają do ustalenia podczas implementacji. Nie rozpoczynamy teraz budowy edytora.

### Pozostałe wymagania

- WordPress i możliwość edycji większości stron w jego edytorze; własne wtyczki dopuszczone dla bardziej złożonych funkcji.
- Języki polski i angielski oraz jasny i ciemny motyw. Prototyp Elekta ma udokumentowany wyjątek: stałą ciemną oprawę.
- Uniwersalna identyfikacja wydawnictwa i indywidualne oprawy wybranych podstron. Kolorystyka jest niezależna od układu i menu.
- Katalog z grą po lewej i dodatkami po prawej; marketingowa prezentacja wybranych gier na stronie głównej.
- WooCommerce, MailPoet, obsługa zagranicznej wysyłki i płatności. Przed zmianami integracji sprawdzamy rozwiązania starej strony.
- Media w katalogu `media/` i bibliotece WordPressa.
- Oddzielne środowiska dev i prod; VPS z proxy i WordPressem, kod montowany z hosta.
- Pierwsze wdrożenie przez paczkę migracyjną, następne przez kod i kontrolowane zmiany bazy zgodnie z [[srodowiska/aktualizacje|aktualizacje]].

## Decyzje techniczne do ustalenia

Ustalenia dotyczące osobnych zestawów produktów PL/EN, walut PLN/EUR niezależnych od języka oraz rozważanych dodatków: [[sklep/jezyki-i-waluty-sklepu|jezyki-i-waluty-sklepu]]. Dobór produktów i waluta nie zastępują obsługi języka koszyka, zamówienia i maili. Wybór integracji i zakup płatnych wtyczek pozostają otwarte.

- Szczegółowa implementacja bloków, wzorców i ograniczeń edycji w ramach uzgodnionego podziału odpowiedzialności.
- Sposób realizacji tłumaczeń stron, produktów i interfejsu.
- Zakres wykorzystania kodu prototypu w osobnym projekcie `wtf_www` oraz sposób zachowania dostępnej referencji prototypu.
- Reguły wspólnych komponentów, opraw podstron i edycji dużej liczby produktów.
- Umiejscowienie sklepu, istniejące integracje, zakres i procedura migracji danych; szczegóły osobno w [[sklep/migracja-danych-sklepu|migracja-danych-sklepu]].
- Wtyczka migracyjna i konfiguracja produkcji oraz proxy.

## Prace wynikające z ograniczeń prototypu

Wersje EN produktów i podstron na razie pozostają w szkicach. Tłumaczenia przygotujemy później; EN nie musi odpowiadać PL 1:1 — zakres i zawartość ustalamy osobno. Główna EN pozostaje opublikowana. Nie publikujemy szkiców EN w ramach poprawiania przełącznika języka.

- Przenieść treści z PHP/JSON do uzgodnionego modelu edycji; parametr `lang` i helpery prototypu nie stanowią jeszcze wybranego systemu tłumaczeń.
- Zastąpić przykładowe produkty i koszyk localStorage rzeczywistymi funkcjami WooCommerce.
- Przygotować działającą obsługę formularza kontaktowego oraz docelowe FAQ.
- Uzupełnić tłumaczenia importowanych materiałów, brakujące treści i lokalne dokumenty informacyjne. Braków nie uzupełniamy wymyślonymi informacjami.
- Dopracować elementy pozostawione jako robocze, m.in. ilustracje i kolory katalogu.
- Sprawdzić edycję treści, widoki mobilne, dostępność, wydajność, języki, motywy i proces zakupowy przed wdrożeniem.

Wybór kolejnych podstron pozostaje ustalany na bieżąco. Powyższe punkty opisują przygotowania i otwarte decyzje, nie zatwierdzony harmonogram ani wybór narzędzi.
