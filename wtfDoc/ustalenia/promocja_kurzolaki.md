# Promocja Kurzołaków

## Aktualny priorytet

30.10.2026 na polecenie użytkownika wdrożono na PROD dwa przyciski „Kup Kurzołaki” na każdej ze stron `/kurzolaki/` i `/kurzolaki/historyjki/` oraz subtelny link w opisie gry. Wszystkie prowadzą do `https://whatthefrog.pl/sklep/?game=kurzolaki`. Zmieniono punktowo treści PROD oraz dodano wyłącznie reguły CSS tych linków, bez podmiany stron lokalnymi i bez minigry. HTTP obu stron i sklepu 200; po dwa przyciski, link tekstowy i aktywny filtr potwierdzone. Kopie i receipt: prywatny katalog hostingu `.wtf-staging/kurzolaki-shop-links-20261030/`.

Przygotować testową kampanię na Facebooku z zaakceptowanych ilustracji: landing page, Meta Pixel i pomiar efektów. Najpierw przygotowanie lokalne; wdrożenie i start reklam na polecenie użytkownika. Minigra wykluczona.

Kolejność: ustalić stronę docelową → przygotować landing i pomiar → sprawdzić zgody, przejście do koszyka oraz zdarzenia → skonfigurować kampanię, budżet i czas testu → uruchomić i ocenić wyniki.

Stan techniczny: GA4 i baner zgód istnieją; Meta Pixel nie jest uruchomiony. Integracja Pixela wymaga podłączenia do zgód oraz aktualizacji informacji o pomiarze. Mierzymy drogę od reklamy do zakupu, nie tylko kliknięcia.

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
