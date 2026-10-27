# Plan uruchomienia

Miejsce wdrożenia: obecny hosting OVH Performance, klaster 131. Nowa strona otrzymuje osobny katalog i osobną bazę obok starej. Docker pozostaje lokalnie; na hostingu wdrażamy pliki i dane, a przekierowania 301 konfigurujemy w `.htaccess` zamiast nginx. Listę adresów zachowujemy w Git.

0. **Wykonane 27.10.2026:** lokalne kontenery dostosowane do hostingu.
   - PHP 8.4.27, Apache + FPM i `.htaccess`, Percona 8.0.46-37 oraz istotne ustawienia PHP/MySQL zgodne z odczytem OVH.
   - Dane przeniesione do osobnego wolumenu; 54 tabele bez różnic. Blokady developerskie zachowane i sprawdzone.
   - Różnice (m.in. niższy limit uploadu DEV, dodatkowe rozszerzenia OVH, CDN) i wyniki: [lokalne środowisko pod OVH](lokalne-ovh.md).

1. **Wykonane lokalnie 27.10.2026:** na start publikujemy tylko wersję PL.
   - Ukrywamy wersję EN oraz przełącznik języka; treści EN zachowujemy do późniejszego uruchomienia.
   - Przełącznik jasnego/ciemnego trybu jest dostępny wyłącznie w DEV.
   - Na produkcji tryb automatycznie odpowiada ustawieniom użytkownika w systemie/przeglądarce (`prefers-color-scheme`), bez ręcznego nadpisania zapisanego przez przełącznik DEV.
   - Wyjątek: Klanarchia i jej podstrony zachowują jedną stałą ciemną oprawę złoto/grafit, niezależnie od ustawień użytkownika.
   - Główna EN (ID 16) przeniesiona do szkicu bez zmiany treści i pary Polylang; pozostałe strony EN już były szkicami. `/en/` tymczasowo kieruje do PL. Nagłówek nie pokazuje wyboru języka ani alternatywnych linków EN.
   - Testy HTTP i przeglądarki przeszły: automatyczny motyw reaguje na zmianę ustawień systemu; tryb produkcyjny JS ignoruje wybór zapisany w DEV. Przełącznik jest renderowany tylko dla środowisk `local`/`development`.
2. **Wykonane 27.10.2026:** wersja testowa działa pod `https://whathefrog.pl` w osobnym katalogu i bazie obecnego hostingu OVH.
   - Pliki i baza zainstalowane, 54 tabele zgodne; blokady staging sprawdzone na OVH. Nowa baza jest na MySQL 8.4.11, zgodność sprawdzono w osobnym kontenerze. HTTPS obu domen, Basic Auth, strony, grafiki i API sprawdzone: [stan wersji testowej](test-na-ovh.md).
   - Pozostajemy przy PHP 8.4, bez zmiany globalnej wersji hostingu. Odczyt WWW i CLI 27.10.2026 potwierdził 8.4.27 z poprawkami CVE-2026-109618 i CVE-2026-109619 oraz wyłączony `session.upload_progress.enabled` (zalecenie dla CVE-2026-115560). Przed premierą sprawdzamy aktualność ponownie.
   - W tym kroku uruchamiamy certyfikat HTTPS dla `whathefrog.pl`, przed rozpoczęciem testów.
   - Dostęp testowy chronimy hasłem i wyłączamy indeksowanie.
   - Blokujemy prawdziwe płatności, maile do klientów i automatyczne integracje. Stary sklep nadal działa.
3. Na hostingu sprawdzamy nową stronę, menu, wersję mobilną, stare adresy i przekierowania `.htaccess`.
4. Domykamy i testujemy sklep oraz migrację danych.
   - Płatności w trybie testowym, dostawy, maile, konta, pobrania, kupony i integracje.
   - Sprawdzamy zadania automatyczne sklepu i dostarczalność maili; w razie potrzeby konfigurujemy osobny SMTP.
   - Bazą migracji jest istniejąca lokalna kopia produkcji w `D:\GIT\Dimundi\whatthefrogpl_backup` oraz wykonana próbna migracja do nowej strony.
   - Przygotowujemy synchronizację różnic od czasu wykonania kopii: nowych zamówień, powiązanych klientów i uprawnień do pobrań oraz zmian istniejących zamówień (statusy, płatności, zwroty). Bez ponownego importowania całości i bez duplikatów.
5. Przygotowujemy przełączenie domeny głównej `whatthefrog.pl` (dwa „t”).
   - Sprawdzamy certyfikat HTTPS dla `whatthefrog.pl` oraz `www.whatthefrog.pl` i jego działanie po zmianie katalogu w MultiSite OVH.
   - Przygotowujemy zmianę katalogu domeny na katalog nowej strony, adresów WordPressa i adresów w bazie oraz konfigurację płatności. DNS zmieniamy tylko, jeśli wymaga tego konfiguracja OVH; nie planujemy przenoszenia na inne IP.
   - Zachowujemy konfigurację poczty domenowej.
   - Zgodnie z decyzją nie wykonujemy dodatkowego backupu starej produkcji. Jej katalog i baza pozostają zachowane. Ustalamy sposób powrotu, uwzględniając zamówienia przyjęte już przez nowy sklep.
6. Wstrzymujemy zakupy i edycje na starej stronie; synchronizujemy ostatnie zakupy i zmiany od czasu lokalnej kopii oraz rozliczamy płatności będące w toku.
   - Podczas przełączania stary sklep pozostaje zamknięty; nie może równolegle przyjmować zamówień.
7. Przełączamy `whatthefrog.pl` oraz `www` na katalog nowej strony w MultiSite OVH i uruchamiamy stronę.
   - Ustawiamy domenę główną i aktualizujemy adresy w bazie. `whathefrog.pl` otrzymuje przekierowanie na domenę główną.
   - Sprawdzamy HTTPS i przekierowania, zdejmujemy blokadę dostępu i indeksowania oraz uruchamiamy produkcyjne maile, płatności i integracje.
   - Wykonujemy kontrolny zakup, sprawdzamy konto klienta, powiadomienia płatności oraz pobieranie plików; otwieramy sklep dla klientów.
8. Obserwujemy działanie po premierze i wykonujemy dalsze poprawki.

Źródło wymagań PHP (sprawdzone 27.10.2026): [oficjalne komunikaty PHP z 21 i 23 października](https://www.php.net/archive/2026.php/). Aktualizacje PHP hostingu wykonuje OVH; wersję WWW i dodatkowe zabezpieczenie sesji potwierdzono odczytem.

