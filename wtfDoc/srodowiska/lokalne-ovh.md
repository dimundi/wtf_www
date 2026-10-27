# Lokalne środowisko pod OVH

Uruchomione 27.10.2026 na `http://localhost:8081`. Start nadal przez `deploy/dev/start-dev.bat`.

| Parametr | Hosting OVH — odczyt WWW/SSH | Lokalnie |
| --- | --- | --- |
| PHP | 8.4.27, FPM za Apache | 8.4.27, FPM za Apache |
| Baza | Percona 8.0.46-37 | Percona 8.0.46-37 |
| Tryb SQL | `NO_ENGINE_SUBSTITUTION` | taki sam |
| Charset / collation serwera bazy | `utf8mb4` / `utf8mb4_0900_ai_ci` | takie same |
| Pakiet MySQL | 32 MiB | 32 MiB |
| Pamięć PHP / czas / liczba pól | 512M / 165 s / 16000 | takie same |
| Upload / całe żądanie | 128M / 130M | 32M / 40M — zachowany limit DEV |
| Session upload progress | wyłączony | wyłączony |

Apache odczytuje `.htaccess`; PHP działa przez FPM, bez `mod_php`. Istotne rozszerzenia WordPressa (m.in. mysqli, curl, intl, mbstring, GD, Imagick, ZIP, EXIF, bcmath) są dostępne w obu środowiskach. OVH ma również dodatkowe rozszerzenia, których obecna strona nie używa. Nie kopiujemy starych wersji bibliotek systemowych OVH; lokalny GD obsługuje też AVIF, którego GD na OVH nie obsługuje. Format obrazów pozostaje WebP.

Lokalnie zachowane są logowanie błędów oraz blokady maili klientów, płatności, webhooków, zewnętrznego HTTP, crona i automatycznego Action Schedulera. Konfiguracji DEV nie wdrażamy na produkcję. CDN, limity współdzielonego hostingu i HTTPS sprawdzamy osobno na OVH.

## Dane i weryfikacja

- Lokalną MariaDB przeniesiono do osobnego wolumenu `wtf-www-dev_db-ovh-dev`. Porównanie wszystkich rekordów w 54 tabelach przed uruchomieniem WordPressa: zero różnic.
- Stary wolumen `wtf-www-dev_db-dev` i obraz `wtf-www-wordpress:before-ovh-20261027` zachowane. Eksport, sumy i konfiguracja powrotu: `backups/hosting-runtime/20261027/`, poza Gitem. Powrót do starego wolumenu cofa również późniejsze zmiany danych — najpierw zachować aktualną bazę.
- PHP WWW OVH ma poprawki krytycznych luk oraz wyłączony upload progress; tymczasowa sonda została usunięta po odczycie.
- Testy: główna, Kurzołaki, sklep, konto, quiz i REST API — HTTP 200; `.htaccess` — przekierowanie 301 i odmowa dostępu 403; kontrola lokalnych blokad i konfiguracji bazy — poprawna.
- Jednorazowe narzędzia tej zmiany: `local-scripts/one-off/hosting-runtime/`. Lokalne narzędzia backupu/restore próbnej migracji klientów dostosowano do klienta MySQL oraz MariaDB; stare archiwa pozostają bez zmian.

Powtarzalna kontrola:

```powershell
docker compose --env-file deploy/dev/.env -f deploy/dev/docker-compose.dev.yml exec -T wordpress wp eval-file /usr/local/bin/check-environment.php --allow-root
```

Obrazy bazowe użyte przy budowie: WordPress FPM `sha256:ce66e0f143d377813f439cb1070c04d0ce6241a59765152ecab65ff192fa8111`, CLI `sha256:c33cbd5fa6508a6515c57d6851edf15c3bf54583d41bb32c79190d2bf269534a`. Przebudowa z `--pull` może pobrać nowszą poprawioną wersję PHP 8.4; build odmawia wersji starszej niż 8.4.27.
