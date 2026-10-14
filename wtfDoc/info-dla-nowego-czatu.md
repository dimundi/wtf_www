# Kontynuacja — 14.10.2026

- Pracujemy w `D:\GIT\Dimundi\wtf_www`. Przeczytaj `AGENTS.md` i dokumentację dotyczącą aktualnego zadania. Indeks: `wtfDoc/README.md`.
- **Kopia whatthefrog.pl odtworzona w `D:\GIT\Dimundi\whatthefrogpl_backup`, `http://localhost:8082/`, panel `/wp-admin/`. WordPress 6.8.11, PHP 8.4, MySQL 8.0, nginx. 187 tabel, 776 użytkowników, 158 produktów, 2099 zamówień. WordPress bez internetu; poczta, płatności, cron i integracje produkcyjne zablokowane. Start/stop: `start.bat` / `stop.bat`. Backupy źródłowe zachowane bez zmian. Instrukcja i SSH: `srodowiska/backup-starej-strony.md`. Prace nad sklepem nowej strony odłożone.**
- Klanarchię utworzymy na samym końcu.
- EN produktów i podstron pozostają w szkicach. Tłumaczenia i odmienną zawartość EN ustalimy później; nie zakładać kopii PL 1:1 ani nie publikować EN przy poprawkach nawigacji. Główna EN jest opublikowana.
- Kopia na 8082: aktywny kod w wolumenie `whatthefrogpl-backup_site-local`, bez wolnego montowania z Windows; katalog `wordpress/` jest pomocniczy. Po poprawce odpowiedzi głównej/sklepu około 0,4–0,5 s. Nie usuwać wolumenów przy zatrzymaniu.

## Projekty

- Prototyp/referencja: `D:\GIT\Dimundi\wtf_web_page`, localhost:8080.
- Wersja docelowa: `D:\GIT\Dimundi\wtf_www`, localhost:8081; Compose `wtf-www-dev`.
- Jeden motyw blokowy `wtf`, techniczna wtyczka `wtf-core`, samodzielna `wtf_faq`.
- Treści w edytorze WordPressa; CSS/JS i szablony w kodzie. Zachowujemy istniejące edycje przy ponownym imporcie.
- Darmowy Polylang: PL/EN, pary tłumaczeń. Większość przeniesionych EN jest szkicami do sprawdzenia i tłumaczenia.
- Produkcja nie została wdrożona. Dev i prototyp mają osobne bazy oraz media.

## Przeniesione do wtf_www

- Strona główna, nagłówek i stopka.
- Tsukuyumi i 16 podstron frakcji.
- Elekt z 10 dodatkami na stronie gry; osobne podstrony tylko Powołanie 2, Rubież, Wiedźmy, Wiedźmy 2.
- Echo Światów z nagraniami i filmami.
- Kurzołaki.
- Wszystkie gry: `/katalog/`.
- Kontakt: `/kontakt/`, PL 600 / EN 602 szkic. Dane i układ z prototypu, edytowalne teksty i etykiety. Formularz demonstracyjny: pola i wysyłanie wyłączone, kontakt przez e-mail. Poprawione linki menu i stopki; sprawdzony desktop, telefon, zapis w edytorze i ponowny import.
- **Ścieżki gier, dodatków i podstron zachowujemy jak na whatthefrog.pl. Frakcje mają linki powiązane z kodami QR.**

## FAQ i panel

- `wtf_faq`: cały kod wydzielony; gra → grupa → pytania, dodawanie pytania z kontekstem grupy, shortcode, dziedziczenie oprawy strony.
- Głosy pomocne/niepomocne bez logowania, zapamiętanie kliknięcia; dostępne głosy źródłowe przeniesione.
- Tsukuyumi: 32 pytania / 12 grup, FAQ na stronie gry i odpowiednich frakcjach.
- Elekt: 23 pytania / 4 grupy, na stronie gry i `/elekt-faq/`; wykonana korekta językowa.
- Standardowe „Strony” oraz osobne „Strony PL/EN”: jeden wiersz na parę, grupowanie według gier, filtry i wyszukiwanie.
- Media: jeden plik może należeć do wielu grup. Filtry w bibliotece i wyborze grafik w edytorze. Uporządkowano 189 plików bez zmiany adresów; grupy gier, ogólne, banery i sklep.
- Darmowe Modern Image Formats tworzy WebP, zachowując oryginały. Przy polach grafik opisujemy wymagane proporcje, zalecaną rozdzielczość i kadrowanie.

## Następny etap — do omówienia

- Utworzenie sklepu oraz migracja produktów, użytkowników, zamówień, list mailingowych i powiązanych danych.
- Stara strona korzysta z WooCommerce; mailing obsługuje MailPoet. WooCommerce nie jest jeszcze zainstalowany w `wtf_www`.
- Najpierw sprawdzamy istniejące integracje i ustalamy zakres migracji; nie zakładamy go samodzielnie.
- PL i EN mogą prezentować różne zestawy produktów.
- PLN/EUR niezależne od języka; waluta obejmuje cenę, koszyk, zamówienie i płatność. VAT ustalamy osobno.
- Ceny EUR z kursu lub ręczne — decyzja otwarta.
- Integracja języka WooCommerce i wybór dodatków otwarte; brak zgody na zakup płatnych wtyczek. Darmowy tryb WPML Multilingual & Multicurrency bez WPML był kandydatem, wymaga weryfikacji.
- Użytkownik ma Paynow. Bramki konfigurujemy osobno po ustaleniu walut.
- Pierwsze wdrożenie: paczka migracyjna z uwzględnieniem nowych danych starej produkcji. Kolejne: kod i wybrane zmiany, bez nadpisywania całej produkcyjnej bazy lokalną kopią. Backup i próba na świeżej kopii produkcji.

## Dokumentacja

- `sklep/migracja-danych-sklepu.md`, `sklep/jezyki-i-waluty-sklepu.md`, `srodowiska/aktualizacje.md`, `ustalenia/wersja-docelowa.md`.
- `przenoszenie/przeniesienie-kontaktu.md`, `panel-wordpress/strony-pl-en.md`, `panel-wordpress/grupy-mediow.md`, `panel-wordpress/optymalizacja-grafik.md`, `wtyczki/wtf_faq.md`.
- Dokumentacja dla jednej osoby: krótko, konkretnie. Komunikacja po polsku; pytania numerowane, maksymalnie dwa naraz.
