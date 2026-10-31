# Promocja Kurzołaków

## Aktualny priorytet

**Ostatnie poprawki mobilne i planszy wdrożone na PROD 31.10.2026:** brak białej kartki pod planszą, zajawka ukryta na telefonach, marginesy PL/EN po 12 px, większa kompozycja pudełka. Sprawdzone na trzech rozmiarach telefonu i desktopie. Logo nadal przy dole pierwszego ekranu; minigra pozostaje lokalna.

Kolejna poprawka wyłącznie lokalna: usunięto białe tło, padding, obrót i prostokątny cień spod planszy w sekcji „Jak grać”. Przezroczysta ilustracja leży bezpośrednio na papierowym tle strony, z delikatnym cieniem dopasowanym do elementów gry. CSS `kurzolaki.css`.

**31.10.2026 — powyższe poprawki strony Kurzołaków wdrożone na PROD na polecenie użytkownika:** zajawka i trzy wyróżniki, pogrubienie asymetrii w akapicie fabularnym, mobilne logo przy dole pierwszego ekranu, ograniczona plama nagłówka oraz cieniowanie kolorowych boksów. Test PROD: 320×568, 390×844, 430×932 i powrót do desktopu, teksty/gradienty poprawne, bez przepełnienia i nachodzenia plamy na zajawkę. Minigra nadal 404. Kopie: `.wtf-staging/kurz-copy-release-20261031/` oraz `backups/promo-release/20261031/kurz-copy-before.tar.gz`.

Lokalnie ograniczono plamę za głównym hasłem, aby nie zachodziła na zajawkę. Plama, kolorowe wyróżniki, boksy informacyjne, plakietki, notatki i tło dodatku otrzymały gradienty, rozświetlenie i miękkie cienie spójne z tłem pudełka; nieregularne kształty i kolory zachowane. Przyciski zachowują ciemny charakterystyczny cień. Obejrzano 390/1440 px, brak przepełnienia. Zmiana w `kurzolaki.css`, bez wdrożenia PROD.

Lokalnie na telefonach do 650 px ilustracja i logo Kurzołaków wypełniają pierwszy ekran; logo kończy się 12 px nad dolną krawędzią, a hasło i opis zaczynają się poniżej. JS przenosi istniejące elementy tylko w układzie mobilnym i przywraca desktop; bez JS zachowana zwykła kolejność strony. Sprawdzone 320×568, 390×844, 430×932 i powrót do 1440 px. Style `kurzolaki.css`, zachowanie `kurzolaki.js`. Wyłącznie lokalnie.

31.10.2026 po wdrożeniu promocji przygotowano kolejną poprawkę **wyłącznie lokalnie**: zajawka „Nie daj się zwieść słodkim pyszczkom…” w pierwszej sekcji oraz trzy wyróżniki przed menu i zwiastunem: asymetryczne frakcje, taktyczne decyzje, bezpośrednia rywalizacja. W akapicie fabularnym pogrubiono „Asymetryczne frakcje”; zastąpiono wcześniejszy długi blok o taktyce, bez powtarzania treści. Edytowalne bloki strony 566 i style `kurzolaki.css`. Sprawdzono układ 1440/390 px, bez przepełnienia. Bez kolejnego wdrożenia PROD.

30.10.2026 na polecenie użytkownika wdrożono na PROD dwa przyciski „Kup Kurzołaki” na każdej ze stron `/kurzolaki/` i `/kurzolaki/historyjki/` oraz subtelny link w opisie gry. Wszystkie prowadzą do `https://whatthefrog.pl/sklep/?game=kurzolaki`. Zmieniono punktowo treści PROD oraz dodano wyłącznie reguły CSS tych linków, bez podmiany stron lokalnymi i bez minigry. HTTP obu stron i sklepu 200; po dwa przyciski, link tekstowy i aktywny filtr potwierdzone. Kopie i receipt: prywatny katalog hostingu `.wtf-staging/kurzolaki-shop-links-20261030/`.

Przygotować testową kampanię na Facebooku z zaakceptowanych ilustracji: landing page, Meta Pixel i pomiar efektów. Najpierw przygotowanie lokalne; wdrożenie i start reklam na polecenie użytkownika. Minigra wykluczona.

Kolejność: ustalić stronę docelową → przygotować landing i pomiar → sprawdzić zgody, przejście do koszyka oraz zdarzenia → skonfigurować kampanię, budżet i czas testu → uruchomić i ocenić wyniki.

Stan techniczny: 31.10.2026 wdrożono na PROD Pixel `2354007262067045` („What The Frog — strona i sklep”) z osobną zgodą marketingową i uzupełnieniem polityk. Na początek `PageView` i `ViewContent`; koszyk i zakup kolejnym etapem. Wdrożono także podglądy społecznościowe stron i produktów, SEO historyjek, Facebook w stopce i przełącznik grafik wydawnictwa. **Aktywna grafika tymczasowa: AleGramy, stoisko A64, Mucha.** Po targach wyłączyć ją w Ustawienia → Ogólne i zapisać. Minigra nadal poza PROD (404). Szczegóły: `wtfDoc/srodowiska/promocja-release-20261031.md`. Odbiór zdarzeń w Meta do potwierdzenia.

Stroną docelową będzie obecna `/kurzolaki/`, bez osobnego landingu. Odczyt publicznego HTML 30.10.2026: podstawowe parametry gry są na początku, ale brak bezpośredniego linku do zakupu podstawki; link zakupowy w treści prowadzi tylko do Sweet Revenge. Strona podstawki odpowiada HTTP 200 i ma przycisk „Dodaj do koszyka”. Najbliższa proponowana poprawka: „Kup Kurzołaki” na początku i na końcu strony gry, prowadzące do `/produkt/kurzolaki/podstawka/`. Bez zmian podczas audytu; wygląd na telefonie i pełny proces zakupu jeszcze niesprawdzone.

Przygotowujemy stronę do promocji Kurzołaków przed świętami. Chcemy też zachęcić do odwiedzenia naszego stoiska na targach za tydzień.

Główny kierunek promocji: **Kurzołaki jako prezent świąteczny**.

Pierwszy etap pokazuje zalety gry, bez choinki i bez bezpośredniego hasła „prezent”. Motyw świąteczny wprowadzimy później.

Grafiki: energiczne, kolorowe kompozycje z postaciami i charakterystycznym liternictwem, ze stonowaną ilością drobnych ozdobników w tle. Bez przechodzenia w minimalistyczne układy. Wzór: zaakceptowane kompozycje frakcyjne `4` i `5`, wersje `v3`, w `marketing/kurzolaki/02-kurzolaki-reklama/1/`.

## Kanały internetowe

Takie same dwa przyciski dodano lokalnie na początku i końcu `/kurzolaki/historyjki/` (PL 4117); zachowane dotychczasowe linki poznawania postaci i gry. Cel: `/sklep/?game=kurzolaki`. Kopia: `backups/kurzolaki-shop-links/20261030/stories-before.html`.

30.10.2026 lokalnie dodano „Kup Kurzołaki” na początku i na końcu strony oraz subtelny link w opisie gry. Wszystkie trzy prowadzą do `/sklep/?game=kurzolaki`, czyli oferty Kurzołaków. Odczyt HTTP strony i sklepu: 200; dwa przyciski i aktywny filtr potwierdzone. Kopia treści: `backups/kurzolaki-shop-links/20261030/page-before.html`; skrypt: `local-scripts/one-off/kurzolaki-assets/shop-links-local.php`. Bez wdrożenia na PROD.


- Facebook, Instagram i YouTube — mamy własne kanały.
- TikTok — możemy założyć konto na potrzeby promocji.
- Płatne reklamy Meta — wchodzą w grę.
- Newsletter — na razie odpada: brak narzędzi i problem z blokowaniem domeny. Do rozwiązania osobno.

## Materiały źródłowe

W `D:\GoogleDrive - firmowy\Mój dysk\Gry\KurzołakiNew\`:

- `MaterialyPrasowe/` — opisy gry, materiały PL/EN i assety.
- `Marketing/` — grafiki, zdjęcia z wydarzeń, instrukcja i materiały dla recenzentów.
- `Links/` — ilustracje i pliki źródłowe elementów gry oraz frakcji.
- `Logo/` — logo PL/EN i pliki źródłowe.
- `Trailer/` — film „Trailer na targi.mp4”, audio i plansze końcowe.
- `Sweet Revenge/` — materiały dodatku.
- `!Zbiorka/` — grafiki, galeria i materiały wcześniejszej kampanii.
- `www/` — materiały obecnej strony.

## Plan publikacji

ALEgramy: 7–8.11.2026, AMBEREXPO, Gdańsk. Stoisko A64, jak w latach ubiegłych. W poście zapowiadamy też matę materiałową i kość splugawienia ze zbiórki Klanarchii; bez ich ilustracji na grafice. Klanarchia nie dominuje przekazu.

Materiały w `marketing/kurzolaki/`, jeden katalog na akcję, ze wspólnymi tekstami i plikami dla wszystkich kanałów:

- `01-alegramy/` — publikacje związane z ALEgramy.
- `02-kurzolaki-reklama/` — post Kurzołaków i materiały do płatnej reklamy Meta.

W każdej akcji kolejne publikacje numerujemy od `1/`. Katalog tworzymy dopiero przy przygotowaniu publikacji, bez pustych katalogów na zapas. Teksty, grafiki i filmy danej publikacji trzymamy razem, niezależnie od kanału. Pierwszy post targowy i jego grafika są w `01-alegramy/1/`.

Pierwszy post publikujemy równocześnie na Facebooku i Instagramie. Podziękowania za zakończoną zbiórkę Klanarchii już opublikowano.

Przygotowujemy też krótkie filmy na YouTube Shorts promujące Kurzołaki.

| Kiedy | Temat | Materiał |
|---|---|---|
| 31.10, 7 dni przed targami | Targi i nasze stoisko — Facebook i Instagram równocześnie | Zaproszenie; będzie można zobaczyć m.in. matę ze zbiórki i Kurzołaki. Klanarchia nie jest głównym tematem. |
| Drugi post, termin do ustalenia | Kurzołaki — post wsparty płatną reklamą Meta | Zalety gry, przejście do sklepu; bez oprawy świątecznej. |
| 3 dni przed | Jak się w to gra? | Krótki film: ciekawy moment lub decyzja gracza; zaproszenie do wypróbowania na targach. |
| Dzień przed | Spotkajmy się na stoisku | Grafika: termin, miejsce, numer stoiska i atrakcje. |
| Podczas targów | Kurzołaki przy stole | Codziennie kilka relacji: rozgrywki, reakcje uczestników, stoisko. |
| 1–2 dni po targach | Gramy dalej | Zdjęcia lub montaż, podziękowanie i przejście do sklepu. |

Filmy oprzemy na istniejącym zwiastunie; scenariusz, napisy i plan montażu przygotujemy tutaj. Wykonawca montażu jeszcze nieustalony. Bez montażu zaczynamy od grafiki i karuzeli; materiał o zasadach również może być karuzelą.

Płatną reklamą Meta wspieramy drugi post, poświęcony Kurzołakom.
