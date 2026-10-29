# Podstawowe SEO PL

29.10.2026: wdrożone i sprawdzone na PROD na wyraźne polecenie użytkownika. Zakres: 11 najważniejszych stron PL, dane marki i ikona. Nie zmieniano nagłówków widocznych na stronie, produktów, menu ani statusów EN.

## Edycja

W edytorze strony lub produktu panel „SEO — tytuł i opis w Google”. Meta `_wtf_seo_title` zawiera pełny tytuł (łącznie z marką), `_wtf_seo_description` — opis. Puste pola zachowują tytuł WordPressa i nie dodają opisu. Zapis wymaga uprawnień edycji i nonce; autosave oraz rewizje pomijane. Metadane wyświetlane dla publicznych PL; konto, koszyk i checkout pomijane.

WTF Core `seo.php`: tytuł dokumentu, pojedynczy meta description, Open Graph title/description/site_name/url. Na głównej JSON-LD `Organization` i `WebSite` (What The Frog / Dimundi sp. z o.o.). WooCommerce `/sklep/` traktowany jako strona oferty z własnym tytułem i kanonicznym URL. Dotychczasowe filtry mapy i tag Search Console zachowane.

Motyw `functions.php`: stałe adresy favicon SVG i PNG 96 × 96 px. PNG jest rasteryzacją istniejącej łapki z SVG, z przezroczystością; bez zmiany znaku i koloru.

## Treści

| Strona | Pełny tytuł |
| --- | --- |
| Główna | What The Frog — wydawnictwo gier planszowych i fabularnych |
| Sklep | Sklep What The Frog — gry i dodatki |
| Kontakt | Kontakt — wydawnictwo What The Frog |
| Katalog | Katalog gier i publikacji — What The Frog |
| Elekt | Elekt — gra karciana w świecie Dominatu \| What The Frog |
| Kurzołaki | Kurzołaki — gra planszowa o walce o landrynkę \| What The Frog |
| Klanarchia | Klanarchia — gra fabularna, druga edycja \| What The Frog |
| Quiz Klanarchii | Quiz Klanarchii — kim jesteś na Rubieży? \| What The Frog |
| Materiały Klanarchii | Klanarchia — starter i materiały do pobrania \| What The Frog |
| Echo Światów | Echo Światów — muzyka i publikacje \| What The Frog |
| Tsukuyumi | Tsukuyumi — zasady, frakcje i materiały do gry |

Opis głównej: „Poznaj Elekt, Kurzołaki i Klanarchię. Odkrywaj gry What The Frog, pobieraj materiały i kupuj bezpośrednio od wydawnictwa.”

Tsukuyumi: bez promocji w opisach głównej/katalogu, bez słów „nasza gra”, aktualnej licencji ani reprezentacji. Własny opis dotyczy zasad, frakcji, materiałów i produktów w sprzedaży. Decyzja użytkownika zapisana również w AGENTS.md.

Komplet opisów i tytułów: `local-scripts/one-off/klanarchia/seo-pl-copy-20261029.json`; ręczny lokalny zapis: `setup-seo-pl-20261029.php`. Nie uruchamiać importu przy starcie środowiska. Kopia poprzednich wartości w lokalnym kontenerze `/tmp/seo-pl-before-20261029.json`.

## Sprawdzenie

`local-scripts/one-off/klanarchia/check-small-seo-pl-20261029.cjs` przeszedł: 11 unikalnych tytułów/opisów i Open Graph, po jednym canonical, poprawny JSON-LD, dostępne logo, PNG 96 × 96, neutralne Tsukuyumi, zachowany tag Google, pominięte strony użytkowe, standardowy produkt bez nadpisania i EN nadal 302. Składnia PHP i git diff --check poprawne.

Zakres wdrożenia: `seo.php`, zmiana favicony w `functions.php`, nowy `assets/favicon-96.png` i wyłącznie metadane 11 stron (z kopiami). Bez przenoszenia całej bazy. Tytuły i opisy są wskazówką dla Google; nie można zagwarantować wyświetlanej treści ani kolejności linków pod wynikiem.

Test check-small-seo-pl-20261029.cjs --prod przeszedł dla wszystkich 11 stron. Kontrola publicznej mapy (48 stron + 33 produkty), tagu Google i konfiguracji analityki również poprawna. Podmieniono seo.php, punktowo funkcję favicon w functions.php, dodano PNG i 22 wartości meta; bez zmiany treści stron i reszty bazy. Kopie: .wtf-staging/seo-pl-release-20261029/before/; paczka i receipt lokalnie: backups/seo-pl-release-20261029/. Aktualizacja wyników Google wymaga ponownego odczytu i przetworzenia stron.
