# Lista przekierowań na deploy

Do wprowadzenia podczas wdrożenia w proxy nginx, jako dokładne wpisy `map`. Nie są jeszcze aktywne.

| Stary adres | Docelowy adres | Typ |
| --- | --- | --- |
| `/download/Klanarchia/` | `/klanarchia/download/` | 301 |
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

Archetypy: również dokładne wpisy dla wariantów bez końcowego ukośnika. Zachować parametry, w szczególności `?wynik=quiz`. Nowe adresy wynikają z rodzica strony Klanarchia; ID stron i identyfikatory wyników quizu pozostają bez zmian. Linki wewnętrzne i adresy wyników quizu wskazują nowe ścieżki. Reguły proxy wdrażamy podczas deployu.

**Przekierowanie dotyczy wyłącznie strony katalogu. Nie stosować reguły dla całego prefiksu `/download/Klanarchia/`.**

PDF-y i tapety zachowują oryginalne ścieżki. Zapewnić także obsługę wariantu katalogu `/download/klanarchia/`. `kpostaci` nie przenosimy. Przykłady, które muszą nadal działać bez zmiany adresu:

- `/download/Klanarchia/Klanarchia_Starter.pdf`
- `/download/Klanarchia/Klanarchia_karty_postaci.pdf`
- `/download/Klanarchia/Krew_wiecznej_zmarzliny_karty_postaci.pdf`
- `/download/Klanarchia/assets/Tapeta_desktop.jpg`
- `/download/Klanarchia/assets/Tapeta_mobile.jpg`

Podczas deployu dostarczyć wyłącznie PDF-y i tapety do pobrania, z zachowaniem ścieżek (w tym `assets/` dla dotychczasowych tapet). Nie wdrażać starej strony HTML ani `kpostaci`. Pliki są poza Gitem. Weryfikacja: nowa strona 200, stara strona dokładnie 301, pliki 200/206 i zgodne sumy SHA-256. Nie nadpisywać nowszych plików produkcyjnych starszą kopią lokalną.

Zmiany adresów produktów nie otrzymują przekierowań — decyzja użytkownika. Dawne adresy mat zwracają 404, nowe /produkt/elekt/mata-1/ i /produkt/elekt/mata-2/ są aktywne.