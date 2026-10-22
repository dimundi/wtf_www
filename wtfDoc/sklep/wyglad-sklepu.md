# Wygląd sklepu

Wspólny wzorzec oferty dla Elekta, Kurzołaków i Tsukuyumi. Hasło „PROSTO OD WYDAWNICTWA / Zabierz grę do domu.” pozostaje bez zmian.

- Wybór gry otwiera ofertę w kolorach sklepu (jasny/ciemny), bez banera gry: podstawka wyróżniona, dodatki w kartach z tłem, akcesoria w lżejszych kartach z ramką.
- „Wszystkie” pokazują jednolitą siatkę produktów; sortowanie znajduje się nad siatką po prawej. W widokach gier obowiązuje `menu_order`.
- Nagłówek sklepu i wprowadzenie podstawki Elekta są edytowalne w bloku „Sklep WTF”.
- Przełączenie gry stosuje wybór od razu; wyszukiwanie działa w wybranej ofercie. Zdjęcia mają kwadratowe pola bez przycinania, liczba kolumn zależy od szerokości sekcji; niepełne rzędy nie rozciągają kart.

## Przypisanie produktów

Produkty → edytuj produkt → Dane produktu → Ogólne:

- **Gra** — np. Elekt, Tsukuyumi, Kurzołaki.
- **Rodzaj produktu** — Podstawka, Dodatek, Akcesorium.

Są to dwie niezależne klasyfikacje, oddzielne od kategorii i typu WooCommerce. Każde pole wskazuje jedną pozycję. Listami zarządza się także przez Produkty → Gry / Rodzaje produktów.

Układ sklepu odczytuje te przypisania, nie zgaduje rodzaju po nazwie. Nieprzypisany rodzaj trafia do „Pozostałych”, aby produkt nie znikał.

Oferta Elekta: 1 podstawka, 6 dodatków, 8 akcesoriów i album Echo Światów MP3 w „Pozostałych”. Kurzołaki: 1 podstawka, 2 dodatki (FanPack, Sweet Revenge), 1 akcesorium (akrylowe plamy). Tsukuyumi: 1 podstawka, 8 dodatków, 3 akcesoria (dwa zestawy planszetek i plastikowe podstawki), komiks w „Pozostałych”. Przypisania można zmieniać w edycji produktu.

Kurzołaki i Tsukuyumi korzystają z tego samego renderowania i CSS co Elekt (`wtf_shop_game`, `.shop-game`). Zachowano adresy sekcji Elekta, nowe gry mają analogiczne kotwice z własnym slugiem. Jednorazowe przypisanie rodzajów: `local-scripts/one-off/shop/classify-other-games.php` (poza Git, zachowuje ręczne zmiany). Sprawdzono podziały produktów, kotwice, wyszukiwanie, przełączanie gier oraz 360/390/768/1440/1920 px w obu trybach; „Wszystkie” nadal pokazują 33 produkty.

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

Moje konto: własne podsumowanie WooCommerce w WTF Core (shop-account.php, views/account-dashboard.php), style account.css. Kokpit nazwany Podsumowaniem; menu z aktywną pozycją, skróty do zamówień/pobrań/danych oraz ostatnie zamówienie bieżącego klienta lub komunikat o braku zakupów. Pozostałe zakładki i logowanie obsługuje WooCommerce. Sprawdzono pięć szerokości 360–1440 px, oba tryby, zakładki konta, widok gościa i podsumowanie zamówienia; bez zapisu testowych zamówień.

Administrator: Użytkownicy → kolumna Konto klienta → Zaloguj jako klient. Dostęp wyłącznie z uprawnieniami manage_options i edit_users, cel wyłącznie z rolą customer. Powrót przez pasek nad stroną lub pozycję menu konta; działa przez godzinę, bez zmiany hasła. To rzeczywista sesja klienta. Powrót wymaga nadal ważnej pierwotnej sesji administratora; unieważnia tylko utworzoną sesję podglądu. Koszyki obu osób zachowane, identyfikatory sesji WooCommerce nie są mieszane. Kod customer-switch.php, styl customer-switch.css. Testy: przełączenie/powrót, oryginalna sesja i koszyk administratora, odrzucenie błędnych nonce, podmienionego biletu, kolejnego przełączenia przez klienta i ponownego użycia powrotu; telefon i desktop.

Lista zamówień na koncie: numer z przedrostkiem #, data dd.mm.yyyy, wartość bez liczby pozycji. Wiersze mają oprawę podsumowania konta i status w etykiecie; na mniejszych ekranach przechodzą w karty. Zachowane akcje i stronicowanie WooCommerce. Sprawdzono widok jasny i ciemny przy 390, 768 i 1440 px oraz otwieranie szczegółów zamówienia.

Szczegóły zamówienia: filtr możliwości ponownego zakupu pobiera zamówienie z kontekstu adresu (WooCommerce przekazuje tylko listę statusów). Historyczne zamówienia nadal bez ponownego zakupu. Test sprawdza także brak błędu krytycznego i obecność stopki po szczegółach; mobilne karty bez wewnętrznych ramek.

Konto na telefonie: menu na pełnej szerokości bez ramki i tła, aktywna zakładka bez bocznej kreski. Szczegóły zamówienia: nagłówek 24/21 px, adresy 20/18 px (desktop/telefon), zwarte odstępy tabeli i sekcji; adresy w jednej kolumnie na telefonie. Sprawdzono 390, 768 i 1440 px w obu trybach, bez przewijania poziomego.

Szczegóły zamówienia mają własne widoki w wtf-core/views/account-{view-order,order-details,order-item}.php, oparte na szablonach WooCommerce 10.1.0/5.2.0 i stosowane tylko w koncie → szczegóły. Nagłówek z numerem, data dd.mm.yyyy i status; produkty z osobną ilością, oddzielne podsumowanie z wyróżnioną sumą oraz forma płatności. Zachowane metadane pozycji, zwroty, notatki klienta, pobrania i akcje WooCommerce. Mobilne menu: ciaśniejsze pozycje, separatory |, subtelne tło. Sprawdzono pełne szczegóły i brak przewijania poziomego przy 390/768/1440 px, w obu trybach.

Mobilne menu konta: Podsumowanie zastąpione ikoną domu (nazwa nadal dostępna dla czytników ekranu); aktywna zakładka podkreślona bez tła. Separatory przy krawędzi nowego wiersza są przycinane, więc wiersz nie zaczyna się od |.

Zakładka Adresy: dwie karty na desktopie, jedna kolumna poniżej 1100 px; nagłówki 20/18 px, adresy bez kursywy, krótkie linki Edytuj/Dodaj z pełną nazwą dla czytnika ekranu. Widok account-addresses.php zachowuje hook po adresie. Sprawdzono oba tryby, telefon/desktop i przejście do formularza edycji.

Formularz adresu: siatka pól imię/nazwisko, kod/miasto, telefon/e-mail (kontakt osobno na telefonie), pozostałe pola pełnej szerokości. Mniejszy nagłówek i odstępy, kontrolki co najmniej 44 px. Bez zmiany walidacji i zapisu WooCommerce. Sprawdzono pozycje pól oraz brak przewijania poziomego przy 390/1440 px w obu trybach.

Adres rozliczeniowy konta: opcjonalne pola Nazwa firmy (billing_company) i NIP (billing_nip), zapis przez WC_Customer w usermeta. Odczyt dawnych kluczy NIP bez automatycznej zmiany danych; świadome wyczyszczenie pola nie przywraca starej wartości. NIP widoczny w karcie adresu, a w szczegółach historycznego zamówienia odczytywany wyłącznie z jego własnych metadanych. Bez zmian checkoutu i bez nadpisywania dawnych zamówień. Test na usuniętym potem koncie tymczasowym: dawny klucz, zapis firmy/NIP, wyczyszczenie; formularz sprawdzony na telefonie i desktopie w obu trybach.
