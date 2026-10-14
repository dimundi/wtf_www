# Implementacja wersji docelowej — punkty 1–3

2026-10-11 użytkownik wznowił implementację. Wykonano pierwszy etap w `D:\GIT\Dimundi\wtf_www`. Zatrzymujemy dalsze przenoszenie na ocenę użytkownika.

## Środowisko

- Osobne repozytorium Git (zainicjalizowane, bez automatycznego commita).
- Dev: http://localhost:8081/, panel http://localhost:8081/wp-admin/. Przykładowe konto wyłącznie lokalne `admin` / `admin`.
- Compose: `wtf-www-dev`. Kontenery: `wtf-www-dev-wordpress-1`, `wtf-www-dev-db-1`; wolumen bazy `wtf-www-dev_db-dev`. Osobne media skopiowane do nowego projektu.
- WordPress 7.1.3, PHP 8.3, MariaDB 11.4. Prototyp nadal działa na 8080 i nie został zastąpiony.
- Start: `deploy/dev/start-dev.bat`; budowanie: `deploy/dev/build-dev.bat`. Konfiguracja w `deploy/dev`, `.env` poza Git. Nasłuch tylko na 127.0.0.1.
- Skrypty inicjalizacji po pierwszym starcie zachowują zapisane strony i części szablonu. Reguły adresów odświeżane po konfiguracji języków w osobnym procesie WP-CLI.

## Motyw, bloki i edycja

- Jeden motyw blokowy `wordpress/wp-content/themes/wtf`: szablony `index`, `page`, `front-page`, części `header`/`footer`, CSS i JS. Układ oparty na zatwierdzonym wariancie prototypu.
- Wtyczka `wordpress/wp-content/plugins/wtf-core` rejestruje dynamiczne bloki sekcji. Treści zapisują się w atrybutach bloków w bazie, niezależnie od kodu renderującego. Domyślne teksty w kodzie służą do wstawiania nowych bloków i pierwszej inicjalizacji.
- Główna PL: ID 13; EN: ID 16. Sześć sekcji: baner, trzy karty „W co dziś gramy?”, przykładowa promocja, dwie karty dla dwóch graczy, wydawnictwo i zaproszenie do sklepu.
- Edycja: Strony → Strona główna lub Home → Edytuj. Zaznaczony blok pokazuje formularz tekstów, grafik i linków. Karty i slajdy można dodawać, usuwać i przestawiać. Sekcje można przestawiać lub usuwać w widoku listy. Standardowy blok „Własny HTML” pozostaje dostępny dla krótkich kampanii.
- Nagłówek i stopka są zapisane jako części szablonu w bazie i edytowalne w Wygląd → Edytor. Pola pozwalają zmieniać nawigację, grafiki gier, e-mail oraz linki stopki.
- Mobilne menu: pełny ekran, zwarta lista od góry, logowanie jako ostatni link, język i motyw na dole po prawej. Kafle gier przewijane poziomo. Koszyk korzysta z WooCommerce, gdy będzie dostępne; nie przeniesiono koszyka testowego localStorage.
- Jasny/ciemny wariant jest zapamiętywany lokalnie. Baner ma slajdy, ręczny wybór, pauzę i respektuje ograniczony ruch.

## Języki i moduły

- Zainstalowano darmowy Polylang 3.8.10. Główne PL/EN są połączone, z osobnymi treściami w blokach. Początkowe EN wykorzystuje teksty prototypu, a nie automatyczne tłumaczenie u odwiedzającego.
- Napisy wspólnego nagłówka/stopki są rejestrowane w panelu Polylang na podstawie ich aktualnej treści. Tłumaczenia edytuje się przez Języki → Tłumaczenia ciągów tekstowych.
- Przygotowano szablon „Aplikacja React” oraz punkt integracji `wtf_react_modules`: moduł może zachować nagłówek i stopkę oraz otrzymać język i początkowy motyw. Zmiana motywu emituje zdarzenie `wtf:themechange`. Nie zbudowano jeszcze konkretnej aplikacji ani interfejsu wstawiania modułu.
- WooCommerce, dodatki wielowalutowe i płatne dodatki tłumaczeń nie zostały zainstalowane. Dyskusja i otwarte decyzje w [[sklep/jezyki-i-waluty-sklepu|jezyki-i-waluty-sklepu]].

## Media i zakres

Zarejestrowano dziewięć assetów w bibliotece WordPressa: grafiki Klanarchii, Kurzołaków, Elekta, Tsukuyumi i Echa Światów, trzy warianty logo SVG oraz obowiązkowy banner płatności. SVG pochodzą ze sprawdzonych lokalnych plików; nie odblokowano dowolnego uploadu SVG.

Linki do nieprzeniesionych gier, katalogu, sklepu i kontaktu prowadzą do dotychczasowej witryny. Nie utworzono pustych stron imitujących ukończoną migrację. Promocja −10% / ELEKT10 pozostaje oznaczonym przykładem i nie tworzy kuponu.

## Weryfikacja

- Konfiguracja Compose poprawna; oba kontenery zdrowe, strona odpowiada HTTP 200.
- Sprawdzona składnia plików PHP i JavaScript.
- Test w przeglądarce: sześć sekcji, układ kart 3 + 2, poprawne PL/EN, zdjęcia głównej, brak poziomego przepełnienia przy 1280 i 390 px.
- Menu przy 390 × 844: pełny ekran, duże poziome kafle, logowanie na końcu, blokada tła oraz jej cofnięcie po zamknięciu. Przełączanie motywu działa.
- Edytor rozpoznaje wszystkie sześć bloków. Zapisano zmianę nagłówka, potwierdzono ją na publicznej stronie i przywrócono pierwotny tekst. Brak wyjątków JavaScript w tym przebiegu.
- Ponowienie inicjalizacji głównej zachowało istniejącą treść.
- Artefakty pierwszej weryfikacji w prototypie: `preview/verify-target.cjs`, `preview/wtf-www-desktop.png`, `preview/wtf-www-mobile-menu.png`, `preview/wtf-www-mobile.png`.

Nie sprawdzano produkcji, płatności ani koszyka WooCommerce — są poza pierwszym etapem.
