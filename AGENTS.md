# WTF — wersja docelowa

- Klanarchia: lokalny prototyp `/klanarchia/` (PL 3119), złoto/grafit, nagłówek WTF na ilustracji, boczne menu sekcji jak Tsukuyumi. Sekcja quizu pokazuje sześć postaci bez kadrowania. Samodzielna wtyczka `wtf_quiz`, menu WTF Quiz: quizy/pytania, zakładki pytań/wyników/ustawień. Quiz PL `/quiz-klanarchia/` (3155, quiz 3144), 10 pytań, 9 archetypów i 19 ilustracji z 8082; +1 do wskazanych archetypów, remis losowany. Dziewięć stron `/archetyp-.../` zachowuje adresy; EN nie utworzono. Import ręczny poza Git w `local-scripts/one-off/klanarchia/`, mapowanie `wtfq_klanarchia_import_map`; ponowienie zachowuje edycje. Dokumentacja `wtfDoc/wtyczki/wtf_quiz.md` oraz `wtfDoc/przenoszenie/klanarchia-kierunek-wizualny.md`.

- 22.10.2026: próbna migracja 8082 → 8081 wykonana: 765 klientów (tylko customer, hashe haseł zachowane), 2099 zamówień + 1 zwrot, 11946 notatek, 2100 uprawnień MP3. Cel HPOS, nowe ID/mapowania, fizyczne produkty historyczne odłączone od oferty, cyfrowy album 7196→825. Mapy: `wtf_customer_migration_map`, `wtf_customer_migration_lookup_map`; nie usuwać bez odpowiadających im danych. Narzędzia poza Git w `local-scripts/one-off/customer-migration/`, archiwa prywatne w `backups/customer-migration/`. Procedura i rollback: `wtfDoc/sklep/probna-migracja-klientow-zamowien.md`. Lokalny MU-guard `deploy/dev/commerce-guard.php` blokuje maile, płatności, webhooki, zewnętrzne HTTP i automatyczne zadania. Produkcja, kupony, MailPoet oraz konfiguracja integracji nadal osobno.

- Jednorazowe skrypty migracji, importu i poprawek treści oraz ich pliki pomocnicze umieszczamy wyłącznie w `local-scripts/one-off/` (np. `wtf-core/`), poza kodem wtyczek. Cały katalog `local-scripts/` jest wyłączony z Gita i nie trafia na produkcję. Nie usuwać tych plików bez polecenia użytkownika. Stałe narzędzia inicjalizacji i powtarzalne testy pozostają w kodzie projektu.

- Roboczy szablon pojedynczego produktu jest włączony tylko na podstawce Elekta `/produkt/gra-karciana-elekt-wydanie-2/` (ID 802). Duża galeria, panel zakupu, informacje i opis, polecane dodatki; grafit i złoto. Pola „Nowy szablon produktu” i „Krótka zajawka przy cenie” w Dane produktu → Ogólne. Obecne ilustracje robocze do późniejszej oceny. Inne produkty używają dotychczasowego szablonu. Kod `wtf-core/shop-product.php`, motyw `assets/product.css`; dokumentacja `wtfDoc/sklep/szablon-produktu.md`.

- Sklep: Elekt, Kurzołaki i Tsukuyumi mają wspólny responsywny układ oferty w jasnym i ciemnym trybie sklepu: zwarta wyróżniona podstawka, karty dodatków i lżejsze karty akcesoriów. „Wszystkie” to jednolita siatka z sortowaniem; widoki gier nie mają sortowania. Pola „Gra” i „Rodzaj produktu” w Dane produktu → Ogólne (`wtf_product_game`, `wtf_product_kind`) sterują sekcjami; brak rodzaju trafia do „Pozostałych”. Kod `wtf-core/shop.php`, `assets/shop.css`. Dokumentacja: `wtfDoc/sklep/wyglad-sklepu.md`.

- Zatwierdzone hasło sklepu: nadtytuł „PROSTO OD WYDAWNICTWA”, nagłówek „Zabierz grę do domu.”. Zachować przy zmianach wyglądu; nie zastępować bez decyzji użytkownika. Szczegóły: `wtfDoc/sklep/uruchomienie-sklepu.md`.

- 15.10.2026: WooCommerce 10.3.8 działa na 8081, `/sklep/`, z rzeczywistym koszykiem i układem według prototypu. Przeniesiono tylko 33 produkty widoczne w katalogu 8082; stary sklep ukrywa wyprzedane. Wszystkie wybrane produkty są proste. Ścieżki `/produkt/.../` zachowane; mapowania ID produktów/kategorii/mediów w `wtfDoc/sklep/mapowanie-produktow.json` i opcji `wtf_shop_import_map`. Ręczny import: `deploy/shop/import-products.ps1`, bez nadpisywania produktów i bez automatycznego importu przy starcie. Zewnętrzny WooCommerce w szybkim wolumenie `wtf-www-dev_woocommerce-dev`; własny kod nadal montowany z projektu. Klienci, zamówienia, kupony i integracje osobno. Zakres, procedura i testy: `wtfDoc/sklep/uruchomienie-sklepu.md`.

- Sklep: na start zachowujemy obecne płatności, dostawy i integracje; rozszerzenia po uruchomieniu. Szczegóły nowej oferty, SKU i wariantów ustalamy podczas implementacji, nie przy określaniu zakresu migracji. Dokumentacja: `wtfDoc/sklep/migracja-danych-sklepu.md`.

- Stałe przekierowania zmienionych adresów: 301 w proxy nginx przez `map`, osobny plik stary adres → nowy adres w Git. Zachować ścieżki gier/frakcji i QR. Linki pobierania cyfrowych zakupów wymagają osobnego rozwiązania z kontrolą uprawnień WooCommerce. Ustalenia: `wtfDoc/sklep/migracja-danych-sklepu.md`.

- Migracja kont sklepu: przenosimy tylko klientów, z hasłami i powiązaniami z zamówieniami; nadajemy role nowego systemu, bez dawnych ról i uprawnień. Bez kont administratorów i innych kont nieklienckich. Ustalenia: `wtfDoc/sklep/migracja-danych-sklepu.md`; numerowane tematy: `wtfDoc/sklep/lista-przeniesienia-sklepu.md`.

- Sklep: przenosimy wszystkie kupony bez kasowania, zachowując statusy, zasady, terminy, limity i wykorzystanie. Migracja kuponów pod koniec, po stworzeniu nowej oferty; kupony produktowe wiążemy z nowymi produktami/wariantami/kategoriami, również fizycznymi. MailPoet przenosimy możliwie w całości. Planowany generator unikalnych kodów dla pojedynczego produktu — szczegóły do ustalenia, bez implementacji. Dokumentacja: `wtfDoc/sklep/migracja-danych-sklepu.md`.

- Sklep: początkową ofertę w `wtf_www` importujemy z produktów widocznych w katalogu starej kopii 8082 (bez ukrytych i wyprzedanych wykluczonych przez ustawienia). Zachowujemy ścieżki produktów i zapisujemy mapowania nowych ID. Zakres i wykonanie: `wtfDoc/sklep/uruchomienie-sklepu.md`. Fizycznych produktów i wariantów nie wiążemy z nowymi; w zamówieniach pozostają historyczne informacje. Wyjątek: cyfrowe produkty i warianty wiążemy z nowymi odpowiednikami, zachowując pliki i uprawnienia do pobierania (limity i ważność). Historyczne nazwy, ilości, ceny, podatki i cechy wariantów bez zmian; stare identyfikatory nie mogą wskazać przypadkowych nowych produktów. Zasady migracji zamówień (osobny etap). Szczegóły: `wtfDoc/sklep/migracja-danych-sklepu.md`.

- Wersje EN produktów i podstron pozostają na razie w szkicach; nie publikować ich przy poprawianiu nawigacji. Tłumaczenia i zawartość EN opracujemy później. Strony EN nie muszą być kopiami PL 1:1; ich treść i układ ustalamy osobno. Opublikowana główna EN pozostaje bez zmian.

- Lokalna kopia starej strony odtworzona 14.10.2026: `D:\GIT\Dimundi\whatthefrogpl_backup`, Compose `whatthefrogpl-backup`, PHP 8.4/MySQL 8.0/nginx, `http://localhost:8082/`, panel `/wp-admin/`. 187 tabel, 776 użytkowników, 158 produktów, 2099 zamówień. WordPress bez internetu; maile, płatności, cron i integracje produkcyjne zablokowane. Start/stop: `start.bat` / `stop.bat`. Archiwa zachowane bez zmian. Procedura i SSH: `wtfDoc/srodowiska/backup-starej-strony.md`.
- Kopia na 8082 używa aktywnych plików w wolumenie `whatthefrogpl-backup_site-local` zamiast montowania kodu z Windows (przyspieszenie z 30,7 s do około 0,42 s). Katalog `wordpress/` na Windows jest pomocniczy; jego zmiany nie trafiają automatycznie do kontenera. Baza pozostaje w osobnym wolumenie; nie używać `down -v`.

- Aktywny projekt i dokumentacja: `D:\GIT\Dimundi\wtf_www`. Dokumenty pogrupowane tematycznie; indeks: `wtfDoc/README.md`. Prototypy i archiwum pozostają w `../wtf_web_page/wtfDoc`.

- `wtf_www`: Media mają wielokrotne grupy w taksonomii `wtf_media_group` (WTF Core): Ogólne, Elekt, Kurzołaki, Tsukuyumi, Klanarchia, Echo Światów, Banery, Sklep. Checkboxy przy pliku; filtry siatki/listy i wyboru grafiki, zarządzanie Media → Grupy mediów. Początkowo przypisano 189 plików bez zmiany plików/URL. Ręczny idempotentny skrypt `setup-media-groups.php`; dokumentacja `wtfDoc/panel-wordpress/grupy-mediow.md`.

- `wtf_www`: Strony PL/EN mają rozwijane grupy Elekt, Kurzołaki, Tsukuyumi, Klanarchia, Strony ogólne i Publikacje, filtr oraz wyszukiwanie PL/EN. Nadal jeden wiersz na parę. Grupa rozpoznawana z metadanych i rodzica; ręczna zmiana w edytorze „Grupa w Strony PL/EN”, wspólna dla pary. Kod w WTF Core `pages-admin.php/css`; standardowe Strony bez zmian. Dokumentacja `wtfDoc/panel-wordpress/strony-pl-en.md`.

- Katalog „Wszystkie gry” przeniesiony do `wtf_www`: `/katalog/`, PL 579 / EN 580 szkic, para Polylang. Cztery gry z dodatkami i dwie publikacje, boczne menu na desktopie / pasek na telefonie, odsłuch Wiedźm. Edytowalne bloki WTF Core; menu i baner głównej oraz powroty z Elekta/Kurzołaków podłączone. Klanarchia nadal zewnętrzna. Dokumentacja `wtfDoc/przenoszenie/przeniesienie-katalogu.md`.

- Kurzołaki przeniesione do `wtf_www`: `/kurzolaki/`, PL 566 / EN 567 (szkic), para Polylang. Układ prototypu i jasna papierowa oprawa; Sweet Revenge na tej samej stronie, 2 filmy, opinie i autorzy. Edytowalne bloki WTF Core, 4 grafiki zarejestrowane w Mediach. Dokumentacja `wtfDoc/przenoszenie/przeniesienie-kurzolakow.md`.

- FAQ Elekta w `wtf_www`: 23 pytania / 4 grupy, głosy źródłowe 37/2. Shortcode w `/elekt/#faq`; zachowana także podstrona `/elekt-faq/` (PL 564, EN 565 szkic). Import `wtf_faq/tools/import-elekt.php`; osadzenie `wtf-core/setup-elekt-faq.php`. Treści zgodne z oryginałem, kolory dziedziczone. Dokumentacja `wtfDoc/wtyczki/wtf_faq.md`.

- Echo Światów przeniesione do `wtf_www`: `/echo-swiatow/`, PL 504 / EN 505 (szkic), para Polylang. Układ prototypu, 10 nagrań i 2 filmy, ciemna oprawa. Edytowalne bloki WTF Core, w tym wybór audio z Mediów; preload none, jedno nagranie naraz. Dokumentacja `wtfDoc/przenoszenie/przeniesienie-echa-swiatow.md`.

- Elekt przeniesiony do `wtf_www` w układzie prototypu, z 10 dodatkami na stronie gry. Osobne podstrony tylko Powołanie 2, Rubież, Wiedźmy i Wiedźmy 2, pod oryginalnymi ścieżkami `/elekt/powolanie2/`, `/elekt/rubiez/`, `/elekt/wiedzmy/`, `/elekt/wiedzmy2/`. PL opublikowane, powiązane EN w szkicach; pełne materiały nadal po polsku. Wiedźmy 2 mają dostępną zajawkę. Treści w blokach WTF Core; dokumentacja `wtfDoc/przenoszenie/przeniesienie-elekta.md`.

- Zachowujemy adresy ze starej strony `whatthefrog.pl` dla gier, dodatków i ich podstron. Przy przenoszeniu sprawdzaj oryginalną ścieżkę; nie twórz nowych slugów ani hierarchii adresów. Lokalnie zmienia się tylko domena i port.

- Frakcje Tsukuyumi: adresy PL identyczne z oryginalnymi ścieżkami na `whatthefrog.pl`, ze względu na kody QR. Dzieci strony Tsukuyumi: `/tsukuyumi/cs/`, `/tsukuyumi/nom/` itd. Nie zmieniać tych slugów. Import nowych stron zachowuje hierarchię; lokalne linki w treściach zostały poprawione.

- `wtf_faq` przeniesiona z prototypu: samodzielna wtyczka, menu WTF FAQ, hierarchia gra → grupa → pytania, filtry i dodawanie pytania z kontekstem. Shortcode na Tsukuyumi PL `/tsukuyumi/#tsu-faq`, 32 pytania / 12 grup, głosy 66/3. Media zarejestrowane, EN do tłumaczenia. Dokumentacja `wtfDoc/wtyczki/wtf_faq.md`. Eksport/import aktualnych danych w `plugins/wtf_faq/tools/`; powtórzenie zachowuje edycje. Start dev aktywuje wtyczkę.
- Dokumentacja dla jednej osoby: krótko, konkretnie, bez pustych sekcji i historii rozmowy. Pytania numeruj, najwyżej dwa naraz.

- 2026-10-13: docelowy adres Tsukuyumi to `/tsukuyumi/`, PL ID 68. Załącznik ID 7 przemianowany na `tsukuyumi_pudelko` (tytuł i slug), bez zmiany pliku. Zaktualizowano odnośniki; wcześniejsze wzmianki o `tsukuyumi-2` są historyczne.

- 2026-10-13: użytkownik wznowił przenoszenie, zlecając Tsukuyumi. Przeniesiono stronę główną gry PL ID 68, slug `tsukuyumi-2` (kolizja z załącznikiem), i 16 frakcji; 17 par Polylang, EN jako szkice z nieprzetłumaczonymi materiałami. Treści w blokach WTF Core, szablon `tsukuyumi`, CSS/JS warunkowo, media zarejestrowane. Identyfikacja `_wtf_tsu_key`, import `setup-tsukuyumi.php` nie nadpisuje edycji ani mediów. Dokumentacja `wtfDoc/przenoszenie/przeniesienie-tsukuyumi.md`. Inne podstrony, sklep i produkcja nie są objęte wznowieniem.

- Automatyczne WebP: darmowa zewnętrzna wtyczka Modern Image Formats (`webp-uploads`), format WebP w Ustawienia → Media; instalowana przez init-dev.sh. Banery, karty, menu gier i promocje korzystają z metadanych załącznika i srcset/sizes; oryginały są zachowywane. Nie pisać własnego konwertera. Szczegóły: `wtfDoc/panel-wordpress/optymalizacja-grafik.md`.

- Dev: limit pojedynczego uploadu 32 MB, całego żądania 40 MB; konfiguracja `deploy/dev/uploads.ini` montowana do PHP przez Compose. Limit nie zastępuje optymalizacji grafik.

- Przy polach grafik w edytorze WordPressa zawsze podawaj wymagane rozmiary lub proporcje, jeżeli format ich wymaga. Odróżniaj wymagania od zalecanej rozdzielczości; opisuj istotne kadrowanie i miejsce na tekst. Baner główny: zalecane 1920 × 800 px, bez wbudowanych napisów, motyw po prawej, tło pod tekst po lewej; obecnie jeden obraz jest kadrowany także na telefonach.

- 2026-10-11: panel ma dwa niezale?ne menu: standardowe Strony oraz stale dost?pne Strony PL/EN z jednym wierszem na par? Polylang. Usuni?to opcj? grupowania i przekierowanie standardowej listy. WTF Core zawiera opis technicznej wtyczki; dokumentacja: `wtfDoc/wtyczki/wtf-core.md`. Nie modyfikujemy WordPressa ani Polylang.

- 2026-10-11: punkty 1–3 wykonane i sprawdzone; dalsze przenoszenie zatrzymane na ocenę użytkownika. Główna PL/EN ID 13/16, darmowy Polylang 3.8.10 aktywny. Test zapisu bloku w edytorze i odczytu na stronie przeszedł; ponowny start zachowuje treści. Szczegóły `wtfDoc/przenoszenie/implementacja-etap-1.md`.

- Komunikacja i dokumentacja po polsku. Ten projekt to `wtf_www`; prototyp w `../wtf_web_page` jest referencją.
- Zakres pierwszego etapu: środowisko, fundament motywu i edytowalna strona główna. Po nim zatrzymujemy prace na ocenę użytkownika. Nie przenosić jeszcze podstron i sklepu ani nie wdrażać produkcji.
- Jeden motyw blokowy `wtf`; własne bloki i funkcje we wtyczce `wtf-core`. Treści w bazie WordPressa, CSS/JS w motywie. Nie kodować kampanii i opisów w szablonach PHP.
- Moduły React mogą być częściami lub całymi stronami, zachowując nagłówek i stopkę. Otrzymują język hosta i mogą mieć własne słowniki.
- PL/EN, opcjonalnie przyszłe ES; treści przygotowane i zatwierdzone, bez automatycznych tłumaczeń u odwiedzającego. Darmowy Polylang dla stron; integracja języka WooCommerce i wybór dodatków nadal otwarte. Nie kupować płatnych zależności bez decyzji użytkownika.
- Dobór produktów PL/EN może być różny. PLN/EUR wybierane niezależnie od języka, dla cen, koszyka i płatności. VAT ustalany osobno. Szczegóły w `wtfDoc/sklep/jezyki-i-waluty-sklepu.md`.
- Oficjalne kolory: #90C11D, #1C263C, #FFFFFF, #000000. Nie zmieniać całego kierunku wizualnego bez uzgodnienia. Nie dodawać ozdobnych strzałek; wyjątek dwa linki banera ze strzałką ↗.
- Media w `media/`, montowane do uploads/media i rejestrowane w bibliotece WP. Tylko zweryfikowane lokalne SVG; bez odblokowywania dowolnego uploadu SVG.
- Dev: `http://localhost:8081`, Compose `wtf-www-dev`, kontenery `wtf-www-dev-wordpress-1` i `wtf-www-dev-db-1`. Prototyp pozostaje na 8080, bazy i media osobne.
- Uruchomienie: `deploy/dev/start-dev.bat`. Walidacja: z `deploy/dev` `docker compose --env-file .env.example -f docker-compose.dev.yml config --quiet`. Skrypt inicjalizacji nie nadpisuje zapisanych treści.
- Produkcja i migracja danych są poza aktualnym etapem. Kolejne wdrożenia nie mogą zastępować całej bazy produkcyjnej lokalną bazą.
- Szczegółowe ustalenia w `wtfDoc`; aktualizować dokumentację o potwierdzone decyzje i wyniki weryfikacji.

- Kontakt: `/kontakt/`, PL 600 / EN 602 szkic, para Polylang; edytowalne bloki WTF Core, formularz demonstracyjny bez wysyłania. Dokumentacja `wtfDoc/przenoszenie/przeniesienie-kontaktu.md`.
