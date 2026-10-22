# Wygląd sklepu

Pierwszy wzorzec: Elekt. Hasło „PROSTO OD WYDAWNICTWA / Zabierz grę do domu.” pozostaje bez zmian.

- Wybór gry otwiera ofertę z jej oprawą. Elekt: ilustracja ze strony gry, grafit i złoto; podstawka wyróżniona, dodatki w kartach, akcesoria w zwartej liście.
- Sekcja Elekta jest także w „Wszystkich”. Pozostałe gry zachowują dotychczasowy widok do osobnego opracowania.
- Baner, jego tekst i wprowadzenie do podstawki: edycja strony Sklep → blok „Sklep WTF”. Przy wyborze grafiki są wskazówki dotyczące rozmiaru i kadrowania.
- Przełączenie gry od razu stosuje wybór; wyszukiwanie i kolejność nadal działają w jej ofercie.

## Przypisanie produktów

Produkty → edytuj produkt → Dane produktu → Ogólne:

- **Gra** — np. Elekt, Tsukuyumi, Kurzołaki.
- **Rodzaj produktu** — Podstawka, Dodatek, Akcesorium.

Są to dwie niezależne klasyfikacje, oddzielne od kategorii i typu WooCommerce. Każde pole wskazuje jedną pozycję. Listami zarządza się także przez Produkty → Gry / Rodzaje produktów.

Układ sklepu odczytuje te przypisania, nie zgaduje rodzaju po nazwie. Nieprzypisany rodzaj trafia do „Pozostałych”, aby produkt nie znikał.

Na start oferta Elekta: 1 podstawka, 6 dodatków i 8 akcesoriów. Album Echo Światów MP3 zachowuje przypisanie do Elekta wynikające ze starej oferty, bez wymuszonego rodzaju; pojawia się w „Pozostałych”. Pozostałe gry mają na razie przypisaną grę, bez podziału na rodzaje.

## Kod i odtworzenie

Próba desktop (22.10.2026): wybrany Elekt `/sklep/?game=elekt` dziedziczy jasne i ciemne kolory sklepu. Zamiast banera gry ma prosty nagłówek; zachowuje wyróżnioną podstawkę oraz podział na dodatki i akcesoria. Style próbne działają od 1101 px; mobile i widok „Wszystkie” pozostają do osobnego opracowania. Sprawdzono oba tryby przy 1440 px: 16 produktów, bez poziomego przepełnienia.

WTF Core: `shop.php`, `shop-classification.php`; wygląd i przełączanie: `wtf/assets/shop.css/js`.

Początkowe przypisania zapisuje `setup-shop-elekt.php`, również wywoływany przez `deploy/shop/import-products.ps1`. Powtórzenie zachowuje ręczne zmiany, także wyczyszczone pola. Nie uruchamiamy go automatycznie przy każdym wejściu do panelu.

Sprawdzone w Chrome: podział Elekta (1/6/8 + album w Pozostałych), baner, telefon bez poziomego przepełnienia, przełączanie gry, 33 produkty w „Wszystkich”, wyszukiwanie w grze, dodanie podstawki do koszyka i pola Gra/Rodzaj w edycji produktu. Hasło sklepu zachowane. Powtórny setup przeszedł bez duplikowania przypisań.
Zdjęcia w próbie desktop Elekta: kwadratowe pola 1:1, dopasowanie całego obrazu bez przycinania, margines 10%, tło zgodne z trybem sklepu. Docelowy materiał: 1600 × 1600 px z przezroczystością; obecne pliki pozostają bez zmian.

Zaakceptowany układ desktop Elekta: zwarta pozioma podstawka (pole zdjęcia maks. 320 px), dodatki we wspólnych wypełnionych kartach, akcesoria w lżejszych kartach z ramką. Liczba kolumn zależy od szerokości sekcji (minimalnie 290 px dodatki, 230 px akcesoria); puste kolumny są zachowane, pojedyncze produkty i niepełne rzędy nie rozciągają kafelków. Kwadratowe pola pokazują całe grafiki bez kadrowania, z nieprzyciętych wariantów medium_large. Ciemny wariant kart: #273247. Mobilki pozostają do opracowania na podstawie zatwierdzonego desktopu.

Wersja mobilna wybranego Elekta wdrożona: wspólne kolory sklepu na wszystkich ekranach, filtry nad ofertą do 700 px, podstawka pionowo z polem zdjęcia do 260 px, kafelki w dwóch kolumnach na typowych telefonach i do trzech na szerszych ekranach bez bocznego menu. Przyciski mają co najmniej 44 px wysokości. Zachowane kwadratowe pola bez przycinania, rozróżnienie dodatków i akcesoriów oraz puste miejsca w niepełnych rzędach. Chrome: 360, 390, 700, 768, 1024 i 1440 px, oba tryby, 16 produktów, bez poziomego przepełnienia; widok desktop zachowany.

Widok Wszystkie: jednolita siatka 33 produktów, bez banera i grupowania oferty Elekta; zachowuje globalną kolejność Polecane oraz sortowanie i wyszukiwanie. Karty ze wspólnym tłem zdjęcia i opisu, kwadratowe nieprzycięte obrazy, automatyczne kolumny z zachowaniem pustych miejsc. Mobilne filtry wspólne z widokiem gry. Sprawdzono 360/390/700/768/1024/1440 px w obu trybach: 33 produkty, kwadratowe pola, brak poziomego przepełnienia.

Sortowanie dostępne tylko we Wszystkich, nad siatką po prawej stronie licznika produktów. Zmiana opcji automatycznie wysyła formularz z zachowaniem wyszukiwania. Widoki wybranych gier nie pokazują sortowania i zawsze korzystają z menu_order, także przy starym parametrze sort w URL. Sprawdzono działanie formularza oraz układ mobilny i desktop.

Wybrana gra ma kompaktowy nagłówek sklepu z zachowanym zatwierdzonym hasłem; dodatkowa zajawka nagłówka jest ukryta. Nazwa gry i licznik produktów współdzielą wiersz. W Elekt usunięto wizualne powtórzenie nagłówka Podstawka, skrócono odstępy nawigacji. Przy 1440 px pierwsza karta zaczyna się około 346 px od początku strony. Widok Wszystkie zachowuje duży nagłówek. Sprawdzono sześć szerokości i oba tryby bez przepełnienia.

Korekta: przywrócono pełne formatowanie nagłówka również w widoku gry: PROSTO OD WYDAWNICTWA, duże Zabierz grę do domu. oraz Zacznij od gry. Dobierz dodatki z jej świata. Zachowano skompaktowany wiersz nazwy gry/licznika i nawigację oferty.

Karty produktów nie mają dodatkowego linku Zobacz opis; do strony produktu prowadzą zdjęcie i nazwa, również w wyróżnionej podstawce.
