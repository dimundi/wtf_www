# Lista przekierowań na deploy

Na obecnym hostingu OVH przekierowania wdrażamy w `.htaccess`, przed regułami WordPressa. Katalog `/download/Klanarchia/` wdrożony na PROD 28.10.2026 jako dokładne 301 do `/klanarchia/download/`, także bez końcowego ukośnika i dla małych liter, z zachowaniem parametrów. Pliki wewnątrz katalogu nie są objęte regułą.

29.10.2026: na polecenie użytkownika wdrożono na PROD dziewięć przekierowań archetypów oraz `/wum`. Fragmenty w Git: `deploy/redirects/klanarchia-archetypes.htaccess` i `deploy/redirects/tsukuyumi-wum.htaccess`. Reguły przed blokiem WordPressa, dotychczasowe reguły domeny i materiałów zachowane. Cele na PROD wskazują bezpośrednio kanoniczne HTTPS.

Sprawdzono 40 wariantów 301 (ukośnik/brak ukośnika, z parametrami/bez), 10 stron docelowych 200 i nieznane podścieżki 404. Dotychczasowe przekierowanie katalogu Klanarchii, PDF i alias domeny nadal działają. Raport: `backups/launch-20261028/pending-redirects-prod-verification.json`. Kopia reguł: `.wtf-staging/launch-20261028/redirects-release-20261029/htaccess-before`.

| Stary adres | Docelowy adres | Typ |
| --- | --- | --- |
| `/download/Klanarchia/` | `/klanarchia/download/` | 301 |
| `/wum` i `/wum/` | `/tsukuyumi/wum/` | 301 |
| `/archetyp-egzorcysta/` | `/klanarchia/archetyp-egzorcysta/` | 301 |
| `/archetyp-inkwizytor/` | `/klanarchia/archetyp-inkwizytor/` | 301 |
| `/archetyp-kronikarz/` | `/klanarchia/archetyp-kronikarz/` | 301 |
| `/archetyp-rzecznik/` | `/klanarchia/archetyp-rzecznik/` | 301 |
| `/archetyp-siepacz/` | `/klanarchia/archetyp-siepacz/` | 301 |
| `/archetyp-szabrownik/` | `/klanarchia/archetyp-szabrownik/` | 301 |
| `/archetyp-wiedzmiarz/` | `/klanarchia/archetyp-wiedzmiarz/` | 301 |
| `/archetyp-zarmistrz/` | `/klanarchia/archetyp-zarmistrz/` | 301 |
| `/archetyp-zerca/` | `/klanarchia/archetyp-zerca/` | 301 |

Uwzględnić również wariant bez końcowego ukośnika `/download/Klanarchia`. Zachować parametry zapytania, jeśli występują.

Przekierowanie `/wum` działa lokalnie i na PROD; zachowuje parametry i obejmuje tylko dokładną ścieżkę z ukośnikiem lub bez. Wskazanie `whatthefro.pl` potraktowano jako literówkę w nazwie domeny sklepu; nie konfigurowano nowej domeny.

Archetypy: również dokładne wpisy dla wariantów bez końcowego ukośnika. Zachować parametry, w szczególności `?wynik=quiz`. Nowe adresy wynikają z rodzica strony Klanarchia; ID stron i identyfikatory wyników quizu pozostają bez zmian. Linki wewnętrzne i adresy wyników quizu wskazują nowe ścieżki. Reguły `.htaccess` wdrażamy podczas deployu.

**Przekierowanie dotyczy wyłącznie strony katalogu. Nie stosować reguły dla całego prefiksu `/download/Klanarchia/`.**

PDF-y i tapety zachowują oryginalne ścieżki. Zapewnić także obsługę wariantu katalogu `/download/klanarchia/`. `kpostaci` nie przenosimy. Przykłady, które muszą nadal działać bez zmiany adresu:

- `/download/Klanarchia/Klanarchia_Starter.pdf`
- `/download/Klanarchia/Klanarchia_karty_postaci.pdf`
- `/download/Klanarchia/Krew_wiecznej_zmarzliny_karty_postaci.pdf`
- `/download/Klanarchia/assets/Tapeta_desktop.jpg`
- `/download/Klanarchia/assets/Tapeta_mobile.jpg`

Podczas deployu dostarczyć wyłącznie PDF-y i tapety do pobrania, z zachowaniem ścieżek (w tym `assets/` dla dotychczasowych tapet). Nie wdrażać starej strony HTML ani `kpostaci`. Pliki są poza Gitem. Weryfikacja: nowa strona 200, stara strona dokładnie 301, pliki 200/206 i zgodne sumy SHA-256. Nie nadpisywać nowszych plików produkcyjnych starszą kopią lokalną.

Zmiany adresów produktów nie otrzymują przekierowań — decyzja użytkownika. Dawne adresy mat zwracają 404, nowe /produkt/elekt/mata-1/ i /produkt/elekt/mata-2/ są aktywne.
