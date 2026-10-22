# Próbna migracja klientów, zamówień i pobrań

## Stan po próbie — 22.10.2026

Źródło: lokalna kopia starego sklepu `whatthefrogpl-backup-wordpress-1`, port 8082, prefiks `wor4480_`, zamówienia CPT. Cel: `wtf-www-dev-wordpress-1`, port 8081, zamówienia HPOS, bez synchronizacji CPT. Obie kopie WooCommerce 10.3.8. Produkcyjnego serwera nie modyfikowano.

| Dane | Przeniesiono |
| --- | ---: |
| Konta klientów | 765 |
| Zamówienia | 2099 |
| Zwroty | 1 |
| Pozycje zamówień i zwrotów | 5959 |
| Notatki | 11946 |
| Uprawnienia do pobrań | 2100 |
| Wpisy indeksu klientów i gości | 1186 |
| Wpisy statystyk zamówień | 2100 |

- Źródło ma 776 użytkowników. Pominięto 4 administratorów, 5 subskrybentów, edytora i konto bez roli. Zaimportowane konta mają wyłącznie rolę `customer`; istniejący administrator celu pozostał bez zmian. Hashe haseł zachowane dokładnie, bez ponownego haszowania. Nie przenoszono sesji, tokenów ani dawnych uprawnień.
- Zachowane daty, statusy (1962 zrealizowane, 123 anulowane, 13 wstrzymanych, 1 zwrócone), numery zamówień, klucze zamówień, adresy, dane płatności, kwoty, podatki, metadane dostaw/faktur, notatki i ich oznaczenie jako notatki dla klienta. Autor nieprzeniesionego konta ma techniczne `user_id=0`, ale treść i podpis notatki pozostają.
- Nieprzeniesione konta oraz zakupy gościnne nie są przypisywane przypadkowym użytkownikom celu: `customer_id=0`, z zachowaniem danych zakupu. Same rekordy indeksu gości nie tworzą kont WordPressa.
- Produkty fizyczne mają w historii `product_id=0` i `variation_id=0`; nazwy, ilości, ceny i cechy pozostają. Oryginalne ID zapisano pomocniczo przy pozycjach. „Zamów ponownie” jest wyłączone dla historycznych zamówień.
- Album MP3: źródłowy produkt **7196 → 825**, 14 plików, te same identyfikatory pobrań. Wszystkie 2100 uprawnień zachowuje limity, liczniki, daty przyznania i ważność. „Zestaw a-moll” (2850) miał flagę cyfrową, ale zero plików i uprawnień; pozostaje odłączoną pozycją historyczną, bez tworzenia nowego produktu.
- Ceny, stany magazynowe i pozostałe dane obecnej oferty nie zostały zmienione. Nie przenoszono kuponów, MailPoet, konfiguracji płatności/dostaw, tokenów płatniczych ani starych kolejek cron/Action Scheduler. To kolejne etapy, nie część tej próby.

## Narzędzia i prywatne archiwa

Zgodnie z `AGENTS.md` skrypty są w **`local-scripts/one-off/customer-migration/`**, poza Gitem. Nie usuwać; katalog i końcową kopię **`migration-tools-final.zip`** trzeba zachować do migracji docelowej. W nowym klonie repozytorium narzędzia trzeba skopiować oddzielnie.

- `run.ps1`: backup, eksport, kontrola wstępna, import, weryfikacja, kontrola powtórzenia i archiwum przebiegu.
- `export.php`, `export-indices.php`: odczyt źródła; brak zmiany danych biznesowych źródła.
- `common.php`, `import.php`: adapter CPT → HPOS (wersja 2), walidacja i import transakcyjny.
- `import-indices.php`: indeks klientów i zbiorcze statystyki zamówień. Nie odbudowuje historii sprzedaży według nowych produktów ani raportów kuponów.
- `verify.php`, `verify-indices.php`, `invariants.php`: kontrola kompletności i zgodności; `file-hashes.php`: pliki cyfrowe.
- `backup-target.sh`, `restore-target.sh`: lokalny backup/rollback przez kontener MariaDB; obraz WordPressa nie ma programu `mysqldump`.
- Mapowania w opcjach **`wtf_customer_migration_map`** i **`wtf_customer_migration_lookup_map`**. Obejmują klientów, zamówienia, pozycje, notatki, uprawnienia i indeksy. Nie usuwać samych mapowań, pozostawiając zaimportowane dane.
- Obsługa historycznych numerów i blokada „zamów ponownie”: `wtf-core/shop-history.php` — ten plik pozostaje w Git i musi być wdrożony z nowym sklepem.

Pierwotna kopia celu sprzed pierwszego importu: **`backups/customer-migration/before-target.sql`**. Kompletny sprawdzony przebieg: **`backups/customer-migration/20261022-131422/`**. Zawiera backup celu tego przebiegu, `source.json`, `indices.json`, mapowania, raporty, skrypty w ZIP oraz `manifest.json` z SHA256 i rozmiarami. Backup w tym datowanym przebiegu powstał już po pierwszej próbie — do całkowitego cofnięcia pierwszego importu służy pierwotna kopia.

Eksporty zawierają dane osobowe i hashe haseł. Pozostają poza Gitem, poza publicznym uploads; kopie narzędzi w kontenerach są w `/tmp/wtf-customer-migration/`. Archiwa przekazywać i przechowywać jako prywatne kopie baz, nie jako załączniki do publicznego repozytorium.

## Powtórzenie lokalne

1. Uruchomić źródło na 8082 i cel na 8081. Zachować najnowsze zmiany strony/oferty. Nie uruchamiać dwóch migracji równocześnie.
2. Sprawdzić blokady lokalne `deploy/dev/commerce-guard.php`, montowane przez Compose jako MU-plugin: cron i automatyczny Action Scheduler wyłączone, maile/webhooki/bramki płatnicze zablokowane, HTTP zewnętrzne zablokowane. Wyjątek: oficjalne paczki WordPress.org przez WP-CLI do odtwarzania dev. Nie kopiować lokalnej konfiguracji do produkcji.
3. Z katalogu projektu, w PowerShellu:

```powershell
.\local-scripts\one-off\customer-migration\run.ps1 -Mode preflight
.\local-scripts\one-off\customer-migration\run.ps1 -Mode apply
```

`preflight` tworzy backup i pakiet, sprawdza zgodność, ale nie importuje klientów/zamówień. `apply` dopisuje brakujące dane i pomija niezmienione. Gdy wcześniej zaimportowane dane źródłowe się zmieniły, zatrzymuje się zamiast nadpisywać je niejawnie.

Po przeglądzie zmian można wykonać **`-Mode sync`**. Aktualizuje wyłącznie wcześniej mapowane rekordy i dopisuje nowe. Cel musi być w tym czasie wolny od równoległych edycji klientów/zamówień. Zniknięcie wcześniej przeniesionych rekordów ze źródła wymaga osobnej decyzji — nie ma automatycznego kasowania.

Każdy przebieg ma osobny datowany katalog archiwum. Podczas zapisu działa tryb konserwacji. Błąd SQL powoduje rollback głównego importu; indeksy mają osobną transakcję. Błąd kontroli zatrzymuje procedurę i pozostawia tryb konserwacji do wyjaśnienia.

Kontrola wstępna odmawia pracy przy kolizjach loginów/e-maili, brakujących mapowaniach lub plikach cyfrowych, niezgodnej wersji WooCommerce, synchronizacji HPOS↔CPT albo nieodpowiednim środowisku. Nie scala automatycznie kont i nie kojarzy produktów po nazwie/SKU.

## Wyniki weryfikacji

- **366149 porównań danych — zero różnic** po uwzględnieniu jawnych mapowań; sprawdzono również hashe haseł i role.
- **43632 porównania indeksów/statystyk — zero różnic**.
- Powtórny import: **0 nowych i 0 zmienionych klientów/zamówień/uprawnień**, pominięto 4965 niezmienionych rekordów głównych.
- Test kontrolowanego błędu po rozpoczętej aktualizacji klienta potwierdził rollback zapisu i zachowanie mapowań; oryginalny pakiet został przywrócony.
- Wszystkie 14 MP3 ma zgodne rozmiary i SHA256 źródło/cel. Hash manifestu plików: `4a6bfade70f7a7a9b84a15c4636954e2b1602beb4c1fada3181d4dd1efbb350e`.
- Przeglądarka: konto klienta pokazuje ostatnie zamówienie, listę i szczegóły jego zamówień oraz zakupione pliki; cudze zamówienie jest niedostępne. Pobranie z uprawnieniem: HTTP 200 i załącznik. Bezpośredni dostęp anonimowy do pliku/katalogu: HTTP 403. Widok pobrań bez przepełnienia przy 390 i 1440 px.
- Sesja testowa została unieważniona. Liczniki pobrań, które WooCommerce potrafi zwiększyć także przy żądaniu HEAD, przywrócono do wartości źródłowych przed końcową weryfikacją.
- Ważny szczegół adaptera: **HPOS `wc_orders.tax_amount` przechowuje `cart_tax`, nie łączny `total_tax`**. Podatek wysyłki jest oddzielnie w `wc_order_operational_data.shipping_tax_amount`. Weryfikacja porównuje obie składowe i sumę.

## Migracja docelowa

1. Dokończyć nową ofertę, płatności/dostawy, kupony, MailPoet i przekierowania zgodnie z [listą przeniesienia](lista-przeniesienia-sklepu.md). Nie uznawać obecnego pakietu za aktualne dane produkcyjne.
2. Przed końcowym eksportem wyznaczyć okno migracji, zatrzymać nowe zakupy/edycje starego sklepu i uzgodnić obsługę płatności w toku. Wykonać świeże, spójne kopie źródła i celu oraz plików chronionych.
3. Sprawdzić wersje WooCommerce i mapowania nowych produktów cyfrowych. W razie zmiany schematu/wersji ponownie sprawdzić adapter; nie usuwać jego zabezpieczeń bez przeglądu. Obecny migrator odmawia bezpośredniego zapisu na domenie produkcyjnej — końcowy runner wymaga kontrolowanego dopuszczenia konkretnego celu i blokad na czas importu.
4. Powtórzyć migrację na świeżej kopii izolowanej; rozwiązać kolizje i rekordy usunięte w źródle. Na docelowym sklepie przenosić wyłącznie wskazane dane z mapowaniami, **nie zastępować całej bazy produkcyjnej bazą lokalną**. Porządkowanie danych próbnych uzgodnić oddzielnie; nie kasować kont ani mapowań w ciemno.
5. W końcowym oknie powtórzyć eksport i import/sync, porównania wszystkich rekordów, plików, uprawnień i kwot. Sprawdzić rzeczywiste logowanie klienta, zamówienia gościnne, pobrania, kupony, płatności i maile. Tożsamości źródłowe i wewnętrzne ID celu mogą się różnić — używać mapowań.
6. Osobno rozwiązać dawne linki pobierania (kontrola uprawnień i stare ID), konfigurację integracji i raporty kuponów. Po pozytywnych testach przełączyć ruch, dopiero wtedy kontrolowanie włączyć integracje i nowe zadania. Zachować stare archiwa oraz plan rollbacku.

## Cofnięcie próby lokalnej

Rollback odtwarza cały wybrany snapshot celu i usuwa późniejsze zmiany w jego bazie. Najpierw zachować aktualną bazę. Źródło 8082 pozostaje nietknięte. Nie wykonywać tych poleceń na produkcji.

```powershell
docker exec wtf-www-dev-wordpress-1 wp maintenance-mode activate --allow-root
docker cp backups/customer-migration/before-target.sql wtf-www-dev-db-1:/tmp/wtf-restore.sql
docker cp local-scripts/one-off/customer-migration/restore-target.sh wtf-www-dev-db-1:/tmp/wtf-restore-target.sh
docker exec wtf-www-dev-db-1 sh /tmp/wtf-restore-target.sh restore-local-only
docker exec wtf-www-dev-wordpress-1 wp cache flush --allow-root
docker exec wtf-www-dev-wordpress-1 wp maintenance-mode deactivate --allow-root
```

Nie używać `docker compose down -v`. Przywrócenie bazy nie cofa plików kodu ani lokalnych blokad integracji.
