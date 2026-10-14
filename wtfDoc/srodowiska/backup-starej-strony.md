# Lokalna kopia whatthefrog.pl

## Połączenie SSH

- Serwer: `ssh.cluster131.hosting.ovh.net`, port `22`.
- Użytkownik: `oekkhvv-agent`.
- Klucz prywatny na tym komputerze: `%USERPROFILE%\.ssh\wtf_backup_agent`. Nie kopiować do repozytorium ani dokumentacji. Klucz publiczny został dodany do `~/.ssh/authorized_keys` na hostingu.
- Logowanie kluczem sprawdzone 14.10.2026.

W PowerShellu:

```powershell
ssh -i "$env:USERPROFILE\.ssh\wtf_backup_agent" -p 22 oekkhvv-agent@ssh.cluster131.hosting.ovh.net
```

Sprawdzenie dostępu bez pytania o hasło i bez zmian na serwerze:

```powershell
ssh -i "$env:USERPROFILE\.ssh\wtf_backup_agent" -o BatchMode=yes -o ConnectTimeout=15 -o StrictHostKeyChecking=yes -p 22 oekkhvv-agent@ssh.cluster131.hosting.ovh.net pwd
```

Na innym komputerze potrzebny jest własny klucz i dodanie jego części publicznej na serwerze. Przy pierwszym połączeniu należy zweryfikować klucz serwera; nie wyłączać jego sprawdzania.

## Stan

14.10.2026 wykonano i sprawdzono backup, przygotowano Docker oraz odtworzono lokalne pliki i bazę. Strona działa pod `http://localhost:8082/`.

- Lokalny katalog kopii: `D:\GIT\Dimundi\whatthefrogpl_backup`, osobny od projektu nowej strony.
- Eksport na serwerze: `/home/oekkhvv-agent/wtf-export-20261013-223633` (nazwa według UTC; lokalnie 14.10.2026).
- `database.sql.gz`: 43 521 154 bajty (około 42 MiB), 187 tabel i znacznik zakończenia eksportu.
- `site-files.tar.gz`: 1 689 271 719 bajtów (około 1,6 GiB), 44 083 wpisy archiwum. Zawiera konfigurację, rdzeń, motywy, wtyczki i media; wykluczone stare backupy.
- SHA-256 obu lokalnych plików zgadza się z serwerem. SQL odczytany do końca; archiwum poprawnie odczytane na serwerze i lokalnie. Manifest: `D:\GIT\Dimundi\whatthefrogpl_backup\manifest.json`.
- Jedno ostrzeżenie tar: katalog `wp-content` zmienił się podczas odczytu. Nie zgłoszono zmiany odczytywanych plików. Kopia pochodzi z działającej strony, bez wstrzymania zamówień i edycji.
- Archiwa nadal są w prywatnym katalogu eksportu na serwerze; nie zostały usunięte. Pierwsza nieudana próba zostawiła pusty SQL i log w `/home/oekkhvv-agent/wtf-export-20261013-223610`; nie jest używana.

- Katalog strony na hostingu: `/home/oekkhvv-agent/whatthefrog.pl`.
- WordPress 6.8.11; konfiguracja hostingu PHP 8.4, CLI PHP 8.4.22.
- Baza MySQL 8.0.46-37: 187 tabel, około 587 MB danych i indeksów. 185 tabel InnoDB, jedna MyISAM, jedna MEMORY.
- Pliki około 3,8 GB, w tym media 1,1 GB i istniejące kopie UpdraftPlus 1,3 GB.
- Dostępne `mysqldump`, `mysql`, `tar`, `gzip`; WP-CLI nie znaleziono w PATH. Wersja klienta MariaDB 10.3.39 nie jest wersją serwera bazy.
- W katalogu są m.in. WooCommerce, MailPoet, Paynow, InPost, SMTP, Wordfence oraz motyw `dimundi`. Sama obecność nie potwierdza aktywacji.

## Odtworzenie

### Gotowe środowisko Docker

Katalog: `D:\GIT\Dimundi\whatthefrogpl_backup`. Compose: `whatthefrogpl-backup`.

- WordPress 6.8.11 z backupu, motyw `dimundi`, PHP 8.4.26, MySQL 8.0.46 i proxy nginx. Wszystkie trzy kontenery zdrowe.
- Strona główna (GET i HEAD), `/sklep/`, `/wp-login.php` i próbna grafika z Mediów odpowiadają HTTP 200. `/wp-admin/` zwraca 302 do lokalnego formularza logowania. Logowanie danymi dotychczasowego konta; hasła użytkowników pozostają z backupu. Panel: `http://localhost:8082/wp-admin/`.
- Odtworzono 187 tabel, 776 użytkowników, 158 produktów i 2099 zamówień. Wynik sprawdzenia: `restore-verification.json` w katalogu kopii; stan po imporcie: `restore-summary.json`.
- WordPress i baza korzystają wyłącznie z sieci `internal: true`. Proxy łączy tę sieć z portem `127.0.0.1:8082`; baza nie ma opublikowanego portu. Sprawdzono, że WordPress nie może połączyć się z internetem.
- PHP `mail()` wyłączone. Wtyczka MU `local-copy-guard.php` blokuje także `wp_mail`, HTTP WordPressa, bramki płatnicze, maile WooCommerce i automatyczny runner Action Scheduler. CSP ogranicza skrypty, połączenia i wysyłanie formularzy z przeglądarki do kopii lokalnej.
- Cron i automatyczne aktualizacje wyłączone w lokalnym `wp-config.php`; bezpośredni `/wp-cron.php` zwraca 403. Własny entrypoint uruchamia Apache bez instalowania nowego WordPressa z obrazu.
- Lokalnie wyłączone: Paynow, InPost, SMTP, UpdraftPlus, Wordfence i WPS Hide Login. Pliki i oryginalne ustawienia pozostają w backupie.
- Aktywne pliki są w wolumenie `whatthefrogpl-backup_site-local`, baza w `whatthefrogpl-backup_db-local`. Katalog Windows `wordpress/` jest kopią pomocniczą po odtworzeniu; nie jest już montowany jako aktywna strona. Lokalne hasła wygenerowano w `.env`; nie są hasłami hostingu.
- Konfiguracja: `compose.yaml`, `Dockerfile`, `local.ini`, `nginx.conf`. Szablony w projekcie nowej strony: `deploy/backup-old/docker-template/`.

Uruchomienie: `start.bat`. Zatrzymanie: `stop.bat` (zachowuje bazę). Z PowerShella w katalogu kopii:

```powershell
docker compose up -d --wait
docker compose ps
docker compose down
```

Nie używać `down -v`, jeżeli chcemy zachować lokalną bazę.

### Szybkość lokalnej kopii

Bezpośredni odczyt kodu z montowanego katalogu Windows powodował odpowiedzi około 30,7 s. Przeniesiono pliki do linuksowego wolumenu i zwiększono OPcache do 256 MB / 30 000 plików. Pomiar po zmianie: główna 1,51 s przy pierwszym odczycie i 0,42 s przy kolejnym; sklep 0,46 s. Dane i blokady integracji ponownie sprawdzone.

Wolumen zasilono przez `seed-volume.sh`: rozpakowanie oryginalnego archiwum oraz nałożenie lokalnego `wp-config.php`, `.htaccess`, `.user.ini`, wtyczki MU i dziewięciu zmienionych plików z `local-overlays.txt`. Zmiany plików w katalogu Windows nie pojawiają się automatycznie na stronie — trzeba skopiować je do wolumenu. Nie uruchamiać ponownie pełnego zasilania działającej kopii bez zachowania jej bieżących plików; zastępuje je stanem backupu. Cache PHP sprawdza zmiany co 60 s; restart kontenera WordPressa wymusza ich natychmiastowe wczytanie.

### Wykonane odtworzenie

1. Zatrzymano proxy i WordPressa. Rozpakowano `site-files.tar.gz` do `wordpress/`.
2. Skopiowano SQL do kontenera bazy i wykonano `deploy/backup-old/import-database.sh`. Skrypt odmawia importu do niepustej bazy.
3. `prepare-local.php` przygotował lokalny `wp-config.php`, `.htaccess`, `.user.ini` i wtyczkę MU, używając lokalnych danych z `.env`. Oryginalna konfiguracja z hostingu pozostaje tylko w niezmienionym archiwum.
4. WP-CLI podmienił adresy z obsługą serializacji, bez zmiany GUID. Pierwszy szeroki przebieg zgłosił nieznane klasy historycznych obiektów; podmianę dokończono przez `replace-urls.sh` z załadowanymi wtyczkami, bez dalszego przetwarzania logów, kolejki Action Scheduler i sesji klientów. W tych historycznych danych mogą pozostać stare adresy; integracje są zablokowane.
   Końcowe przebiegi dla czterech wariantów domeny zakończyły się sukcesem i nie znalazły dalszych adresów do zmiany w przetwarzanych tabelach.
5. W dziewięciu lokalnych plikach motywu i własnej wtyczki zmieniono zaszyte adresy na `localhost:8082`. Filtry wtyczki MU wymuszają lokalne URL także w Polylang i przekierowaniach.
6. Uruchomiono kontenery; `verify-restored.php` potwierdził liczby użytkowników, produktów i zamówień oraz blokady maili, HTTP, płatności i cron. Sprawdzono odpowiedzi HTTP i dostępność grafiki.

Skrypty odtworzenia są w `wtf_www/deploy/backup-old/`. Nie uruchamiać `prepare-local.php` ponownie na działającej kopii — chroni raport źródłowy przed nadpisaniem. Dla nowego odtworzenia najpierw zachować obecną lokalną bazę i przygotować świeże środowisko.

- Pełna kopia plików strony i eksport wszystkich tabel bazy. Na polecenie użytkownika pomijamy istniejące kopie: `wp-content/updraft` i `wp-content/upgrade-temp-backup`. Sama wtyczka UpdraftPlus pozostaje w archiwum.
- Oryginalny backup zachowujemy bez zmian w `D:\GIT\Dimundi\whatthefrogpl_backup`, poza Gitem.
- Osobny Compose, baza MySQL 8.0 i PHP 8.4; planowany adres `http://localhost:8082`, tylko na interfejsie lokalnym. Porty 8080 i 8081 pozostają dla prototypu i nowej strony.
- Pliki kopii montowane z osobnego katalogu, bez nadpisywania `wordpress/` i `media/` nowej strony.
- Przed startem blokujemy pocztę, cron i połączenia z usługami produkcyjnymi. Zmieniamy tylko lokalne ustawienia bazy i adresy, z obsługą serializacji WordPressa.
- Eksport używa `--single-transaction --skip-lock-tables`, bez blokowania działającego sklepu. Dwie tabele poza InnoDB: `wor4480_mgmlp_folders` (foldery mediów, MyISAM) oraz `wor4480_wfls_role_counts` (Wordfence, MEMORY); dla nich eksport nie gwarantuje spójnego momentu. Tabele sklepu są InnoDB.

Skrypt odczytu informacji o bazie: `deploy/backup-old/inspect-database.php`, przesyłany na standardowe wejście PHP przez SSH; nie uruchamia WordPressa i nie wypisuje danych dostępowych.

Skrypt tworzenia kopii: `deploy/backup-old/create-backup.php`, uruchamiany tak samo. Tworzy prywatny katalog poza stroną publiczną, eksport SQL, archiwum oraz `manifest.json` z sumami SHA-256. Tymczasowy plik danych dostępowych ma uprawnienia 0600 i jest usuwany po eksporcie; haseł nie przekazujemy w argumentach procesu.

W tej kopii manifest zapisano lokalnie przez `deploy/backup-old/verify-copy.ps1` po ręcznej kontroli ostrzeżenia katalogu. Skrypt tworzenia kopii poprawiono, aby rozpoznawał to konkretne ostrzeżenie; inne błędy nadal przerywają operację.

## Ponowne wykonanie etapu 1

Z PowerShella w `D:\GIT\Dimundi\wtf_www`:

```powershell
Get-Content -LiteralPath '.\deploy\backup-old\create-backup.php' -Raw | ssh -i "$env:USERPROFILE\.ssh\wtf_backup_agent" -o BatchMode=yes -o StrictHostKeyChecking=yes -p 22 oekkhvv-agent@ssh.cluster131.hosting.ovh.net php
```

Skrypt wypisuje `EXPORT_DIRECTORY` i po sukcesie `BACKUP_READY`. Do czasu zakończenia nie pobieraj archiwum plików. Nową kopię zapisuj w osobnym podkatalogu daty, bez nadpisywania poprzedniej. Pobierz `database.sql.gz`, `site-files.tar.gz` i `manifest.json` przez SCP z wypisanego katalogu. Porównaj SHA-256 i wykonaj `verify-copy.ps1` z katalogiem lokalnym, obiema sumami z serwera i ścieżką eksportu. Skrypt weryfikacji obecnie oczekuje 187 tabel; przy kolejnych kopiach porównaj tę liczbę z aktualną bazą.

Odświeżenie lokalnej kopii w przyszłości: ponowny pełny eksport bazy i zastąpienie lokalnej bazy po zachowaniu jej kopii. Nadpisuje to lokalne zmiany; nie jest procedurą synchronizacji zamówień z przebudowanym `wtf_www`.
