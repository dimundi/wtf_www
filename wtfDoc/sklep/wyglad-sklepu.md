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

WTF Core: `shop.php`, `shop-classification.php`; wygląd i przełączanie: `wtf/assets/shop.css/js`.

Początkowe przypisania zapisuje `setup-shop-elekt.php`, również wywoływany przez `deploy/shop/import-products.ps1`. Powtórzenie zachowuje ręczne zmiany, także wyczyszczone pola. Nie uruchamiamy go automatycznie przy każdym wejściu do panelu.

Sprawdzone w Chrome: podział Elekta (1/6/8 + album w Pozostałych), baner, telefon bez poziomego przepełnienia, przełączanie gry, 33 produkty w „Wszystkich”, wyszukiwanie w grze, dodanie podstawki do koszyka i pola Gra/Rodzaj w edycji produktu. Hasło sklepu zachowane. Powtórny setup przeszedł bez duplikowania przypisań.
