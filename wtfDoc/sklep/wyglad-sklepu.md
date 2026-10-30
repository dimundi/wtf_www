# Wygląd sklepu

31.10.2026: na polecenie użytkownika wdrożono na PROD cały opisany niżej mechanizm zestawów, konfigurację składu, widok gratisów, porównanie podstawki/zestawu i wyłączenie opinii. Wcześniejsze oznaczenia tych zmian jako „lokalne / PROD bez zmian” są historyczne. Nowy produkt PROD 4130: „Kurzołaki + Sweet Revenge”, 250 zł, `/produkt/kurzolaki/zestaw/`; skład mapowany po slugach na istniejące produkty PROD, plamy ×1 gratis. Osobne plamy bez promocji, 35 zł. Zakres: 10 plików kodu/stylów, nowy produkt, jeden obraz/9 plików, wyłącznie usunięcie promocji w istniejącym produkcie plam. Pozostałe dane istniejących produktów porównane i zachowane; bez importu bazy, zmian zamówień/klientów i minigry. Kopie kodu, danych produktów i potwierdzenie: `.wtf-staging/bundle-release-20261031/`; lokalna paczka w `local-scripts/one-off/bundle-release-20261031/`. Po wdrożeniu odświeżono routing w nowym procesie WP-CLI. Testy PROD 320/390/1440 px: widoczność zestawu tylko przy Kurzołakach, ceny, grafika gratisu i link do opisu, trzy opisy składników w ramkach, ilustracje wewnątrz ramek, mobilne skracanie bez migania, brak opinii i poziomego przepełnienia. Minigra nadal 404.

30.10.2026, lokalnie: rodzaj produktu „Zestaw” (`zestaw`) tworzy wyróżnioną sekcję „Kup w zestawie” przed podstawką w ofercie wybranej gry. Zestawy nie pojawiają się we „Wszystkich”, również przy wyszukiwaniu; pola Gra/Rodzaj produktu pozwalają użyć mechanizmu przy innych grach. Kurzołaki + Sweet Revenge: produkt 4205, 250 zł, `/produkt/kurzolaki/zestaw/`, grafika użytkownika z `Pudelko/Render/wer8/Pudlo_Kurzołaki-zestaw.png` zarejestrowana w Mediach jako zoptymalizowany WebP. Osobny produkt prosty, dostępny bez liczbowego zarządzania magazynem; brak synchronizacji stanów składników, do osobnego ustalenia. Skrypt jednorazowy: `local-scripts/one-off/sweet-revenge/create-bundle-local.php`. Bez wdrożenia na PROD.

Wspólny wzorzec oferty dla Elekta, Kurzołaków i Tsukuyumi. Hasło „PROSTO OD WYDAWNICTWA / Zabierz grę do domu.” pozostaje bez zmian.

- Wybór gry otwiera ofertę w kolorach sklepu (jasny/ciemny), bez banera gry: podstawka wyróżniona, dodatki w kartach z tłem, akcesoria w lżejszych kartach z ramką.
- „Wszystkie” pokazują jednolitą siatkę produktów; sortowanie znajduje się nad siatką po prawej. W widokach gier obowiązuje `menu_order`.
- Nagłówek sklepu i wprowadzenie podstawki Elekta są edytowalne w bloku „Sklep WTF”.
- Przełączenie gry stosuje wybór od razu; wyszukiwanie działa w wybranej ofercie. Zdjęcia mają kwadratowe pola bez przycinania, liczba kolumn zależy od szerokości sekcji; niepełne rzędy nie rozciągają kart.

## Przypisanie produktów

Rozdzielone teksty zestawu: karta na liście sklepu pokazuje edytowalny „Krótki opis produktu” (obecnie „Podstawka + Sweet Revenge + akrylowe plamy gratis.”). Podsumowanie na stronie produktu jest listą składników z konfiguracji, niezależną od zajawki. Mobilna strona produktu ukrywa listę przy cenie; pozostawia duże boksy z opisami i przyciskiem Rozwiń/Zwiń. Mechanizm skracania ustabilizowany, bez zapętlenia pomiarów. Zmiany lokalne.

Opis zestawu lokalnie: pod boksami pozostają teraz pełne, stale widoczne opisy składników, pobierane z ich produktów według kolejności konfiguracji. Nagłówki zawierają nazwę, ilość i Gratis. Zachowane formatowanie i grafiki opisów; lista przy cenie i boksy bez zmian. Sprawdzone trzy sekcje i trzy boksy przy 390/1440 px, bez poziomego przepełnienia. Opis plam składa się obecnie z grafiki. PROD bez zmian.

Aktualny opis zestawu lokalnie: skrócona lista produktów, ilości i gratisów pod ceną; w sekcji „Zawartość zestawu” karty składników ze zdjęciem, nazwą, ilością, krótkim opisem i linkiem. Oba widoki odczytują ten sam zapis konfiguracji, bez ręcznego powielania zawartości. Karty gratisów wyróżnione; układ 3/2/1 kolumn zależnie od szerokości. Zestaw Kurzołaków sprawdzony przy 390/1440 px: trzy pozycje pod ceną, trzy karty, jeden gratis, bez przepełnienia. PROD bez zmian.

Opis produktu rodzaju „Zestaw” jest renderowany z konfiguracji jako „Zawartość zestawu”: wszystkie składniki, ilości i oznaczenia Gratis, z linkami do opublikowanych produktów. Dotyczy standardowego i nowego szablonu produktu; zapisany wcześniej opis pozostaje w bazie, ale nie jest pokazywany zamiast listy składu. Sekcja Opinie usunięta ze wszystkich produktów, bez usuwania zapisanych opinii z bazy. Zmiany lokalne; zweryfikowano zestaw i obie wersje szablonu produktu.

### Konfiguracja zestawu — aktualnie lokalnie

Produkty → edytuj → Dane produktu → Ogólne: wybierz Grę oraz rodzaj „Zestaw”. W sekcji „Zawartość zestawu” przyciskiem „Dodaj produkt” tworzysz wiersze: produkt, liczba sztuk, checkbox „Gratis”. „Usuń” usuwa pozycję. Cena i obraz zestawu pozostają standardowymi polami WooCommerce. Lista zastępuje wcześniejsze pole pojedynczego gratisu; pozwala wskazać wiele gratisów, ich grafiki i ilości pojawiają się na karcie zestawu.

Skład zapisany w `_wtf_bundle_items`: `product_id`, `quantity`, `is_gift`. Tylko opublikowane produkty proste, bez powtórzeń, zagnieżdżonych zestawów i samego zestawu; ilość całkowita 1–9999. Błędny skład nie zastępuje poprzedniego, edytor pokazuje komunikat. Stare pole `_wtf_bundle_gift_id` odczytywane do pierwszego zapisu nowej listy. Nie ma automatycznej zmiany ceny, stanów składników ani pozycji koszyka/zamówienia.

Zestaw Kurzołaków 4205: podstawka 806 ×1, Sweet Revenge 805 ×1, plamy 803 ×1 jako gratis; cena 250 zł. Konfiguracja wykonana przez `configure-bundle-items-local.php`. Kod: `shop-bundles.php/js/css`, integracja `shop-classification.php` i `shop.php`. Testy: zapis/wyczyszczenie listy, ilości, dwa gratisy z różnymi ilościami, nonce, błędne dane, dodawanie/usuwanie wierszy i przełączanie rodzaju produktu. Widok sklepu 320/390/1440 px w obu motywach poprawny. Narzędzia w `local-scripts/one-off/sweet-revenge/`. PROD bez zmian.

Lokalnie: przy ofercie z zestawem od 1001 px podstawka i zestaw stoją obok siebie, grafiki nad opisem, ceny i przyciski na wspólnej linii. Węższe ekrany zachowują kolejność pionową podstawka → zestaw. Wspólny mechanizm dla gier z oboma rodzajami produktów; pozostałe sekcje pod spodem na pełną szerokość.

Plakietka gratisu lokalnie: prowadzi do opisu zestawu (kotwica zależna od szablonu produktu), bez podpisu „Do tego zestawu”. Grafika plam zachowuje oryginalną przezroczystość, usunięto białe tło CSS. Etykieta GRATIS używa kształtu i łapki jak oznaczenia rabatu. Sprawdzone 320/390/1440 px, jasny/ciemny motyw; PROD bez zmian.

31.10.2026, lokalnie: Dane produktu → Ogólne → „Produkt gratisowy w zestawie” (`_wtf_bundle_gift_id`). Wybrany opublikowany produkt dostarcza grafikę, nazwę i link do plakietki GRATIS na karcie zestawu. Wybór pusty wyłącza plakietkę; produkt nie może być własnym gratisem. Mechanizm wspólny, bez identyfikatorów wpisanych w kodzie renderowania i bez synchronizacji stanów składników. Dla zestawu Kurzołaków wskazano plamy 803; cena zestawu nadal 250 zł. Krótki dopisek o gratisie zastąpiono grafiką, pełny opis nadal zawiera plamy. PHP sprawdzony; testy panelu wyboru/wyłączenia/własnego gratisu oraz widoku 320/390/1440 px w obu motywach przeszły. Narzędzia: `configure-gift-local.php`, `check-gift-admin-local.php`, `check-gift-local.cjs` w `local-scripts/one-off/sweet-revenge/`. PROD bez zmian.

Lokalny zestaw 4205: nazwa „Kurzołaki + Sweet Revenge”, cena 250 zł; akrylowe plamy gratis, ujęte w krótkim i pełnym opisie. Osobne akrylowe plamy 803 bez ceny promocyjnej i terminów promocji, cena regularna 35 zł. Nadtytuły kart podstawki i zestawu: 14 px, pogrubione. Skrypty `rename-bundle-local.php` i `bundle-free-stains-local.php` w `local-scripts/one-off/sweet-revenge/`. PROD bez zmian.

Korekta lokalna 30.10.2026: podstawka przed zestawem. Zestaw bez osobnego widocznego nagłówka sekcji „ZESTAW / Kup w zestawie”; nadtytuł pozostaje wewnątrz karty. Obie karty używają wspólnego układu podstawki, identycznych kolumn, rozmiarów nagłówka i wyrównania ceny/przycisku. Zestaw wyróżnia tylko gradient i ramka. Potwierdzone przy 390/1440 px, w obu motywach; na desktopie identyczna pozycja początku tekstu obu kart.

Weryfikacja zestawu lokalnie: ukryty we „Wszystkich” i przy Elekcie, widoczny przy Kurzołakach; poprawny adres produktu, dodanie do koszyka, jasny/ciemny motyw przy 390/1440 px, pełna szerokość wyróżnienia i brak przepełnienia. PHP `shop.php` bez błędów składni. Test: `local-scripts/one-off/sweet-revenge/check-bundle-local.cjs`.

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

Logowanie (22.10.2026): wyśrodkowany układ dwóch kolumn, formularz po lewej i stworek po prawej; poniżej 650 px sam formularz. Oryginał grafiki w media/witaj_stworek.png, Media ID 3115 (pliki mediów poza Git). Układ przez hooki w shop-account.php, style account.css; pola, nonce i obsługa logowania WooCommerce zachowane. Sprawdzono składnię PHP i podgląd desktopowy.
Adres rozliczeniowy: pusty kraj domyślnie PL, zapisany kraj zachowany. Checkbox Chcę otrzymać fakturę VAT zapisuje billing_vat_invoice. Zaznaczony wymaga firmy i NIP, odznaczony imienia i nazwiska; pozostałe pola opcjonalne. Istniejące dane firmy/NIP wstępnie włączają opcję, jeśli brak zapisanego wyboru. account.js zmienia wymagalność, shop-account.php ustala walidację serwera. Sprawdzono oba warianty dla PL/DE, kraj domyślny i zapisany oraz składnię PHP/JS, bez zapisu danych klientów.

Dostawy — ustalenie 22.10.2026: zachowujemy pięć klas ze starego sklepu, przypisania produktów i ceny, do późniejszej korekty. Sprawdzono 33 produkty: Paczkomat A — 10, Paczkomat B — 2, bez klasy — 21; klasy Darmowa dostawa, Paczkomat C i Tylko kurier pozostają dostępne, obecnie bez przypisanych produktów w nowej ofercie. Nie przypisujemy klas na podstawie domysłów.

| Klasa | Paczkomat | Kurier |
|---|---:|---:|
| Darmowa dostawa | 0 zł | 0 zł |
| Paczkomat A | 14 zł | 17 zł |
| Paczkomat B | 15 zł | 17 zł |
| Paczkomat C | 17 zł | 20 zł |
| Tylko kurier | niedostępny | 17 zł |
| Bez klasy | 15 zł | 17 zł |

Strefa Polska, obliczanie według najwyższej stawki w koszyku (jak w starej konfiguracji). Weryfikacja wszystkich przypisań i zapisanych stawek przeszła. Mapowania i stan sprzed importu w opcji wtf_checkout_shipping_import; skrypty i eksport w local-scripts/one-off/account/. To ustalenie dotyczy dostaw; przebudowa formularza zamówienia pozostaje niedokończona po zatrzymaniu prac.
Zamówienie — dane kontaktowe (22.10.2026): nagłówek Dane kontaktowe; podtytuł tylko Jeszcze chwila i gotowe. Niezalogowany może zaznaczyć domyślnie wyłączone Utwórz konto, odsłaniające Hasło i Powtórz hasło. Konto tworzy natywny proces WooCommerce przy finalizacji zakupu; bez zaznaczenia pozostaje zakup gościnny. Wymagalność i zgodność haseł sprawdzane na serwerze. Sprawdzono przełączanie w przeglądarce oraz brak hasła, różne i zgodne hasła, bez tworzenia kont lub zamówień.
Doprecyzowanie interfejsu zamówienia: checkbox ma etykietę Zamów i utwórz konto. Rozwijany panel logowania ma pola w jednej kolumnie, jednakowe szerokości i wysokości, osobny wiersz Zapamiętaj mnie oraz pełnej szerokości przycisk Zaloguj się. Sprawdzono desktop i brak poziomego przewijania przy 390 px.

Osobna rejestracja konta (22.10.2026): wejście Nie masz konta? / Załóż konto pod formularzem logowania. Adres /moje-konto/?rejestracja=1. Najpierw sam e-mail, następnie jednorazowy link ważny 24 godziny, ustawienie hasła i potwierdzenia (minimum 8 znaków), utworzenie konta customer i zalogowanie. Przed użyciem linku i zatwierdzeniem hasła nie powstaje użytkownik. Token jest przechowywany jako hash w transientach; limit ponownej wysyłki dla adresu i limit żądań z IP. Dotychczasowe konta bez zmian. Kod: shop-registration.php, views/account-registration.php, account.css.

Weryfikacja: pełna aktywacja przez formularz HTTP, błędne potwierdzenie hasła, hasło zapisane poprawnie, rola customer, brak konta przed aktywacją, odrzucenie użytego/nieznanego/wygasłego linku i zajętego e-maila; ekran mobilny bez poziomego przewijania. Konto testowe usunięte, bez zamówień. Lokalnie wp_mail pozostaje zablokowane; test przechwycił treść maila i użył linku testowego bez zewnętrznej wysyłki. MU-guard zachowuje klasy e-maili WooCommerce i blokuje ich wysyłkę zamiast usuwać klasy — usuwa to błąd krytyczny przy tworzeniu konta.
SMTP lokalnie (22.10.2026): zainstalowano i aktywowano darmowy WP Mail SMTP 4.10.3 na 8081. Konfigurację dostawcy i danych dostępowych wykonuje użytkownik w panelu WP Mail SMTP. Lokalny commerce-guard nadal blokuje wysyłkę; instalacja nie znosi tej blokady. Bez zmiany konfiguracji produkcji.
Wyjątek poczty lokalnej (22.10.2026): commerce-guard dopuszcza wp_mail wyłącznie do kotow55@gmail.com, m.in. wiadomości aktywacyjne rejestracji i testy WP Mail SMTP. Inni odbiorcy oraz mieszane listy To/CC/BCC są blokowane; dodatkowo sprawdzana jest końcowa lista odbiorców PHPMailer. Automatyczne e-maile WooCommerce nadal wyłączone. Testy reguł przeszły bez wysyłania wiadomości; konfigurację SMTP wykonuje użytkownik.
Nagłówek checkoutu: tytuł strony Finalizowanie zamówienia, ścieżka /zamowienie/ zachowana. Od nagłówka witryny oddziela go większy odstęp i cienka linia; na telefonie mniejszy odstęp. Tytuł ustawiony w bazie strony 701 i jako wartość początkowa dla nowych instalacji.
Koszyk i finalizowanie zamówienia: wspólny zwarty nagłówek 26–34 px, cienka linia pod tytułem, ograniczone odstępy. Obie strony mają własny szablon motywu i wyśrodkowany obszar treści do 1240 px. Tabela koszyka wypełnia ten obszar. Sprawdzono w przeglądarce wyśrodkowanie tabeli i brak poziomego przewijania na desktopie.
Dane kontaktowe checkoutu: e-mail i opcjonalny telefon w jednym wierszu na desktopie, na telefonie pod sobą. Dostawa ma osobne wymagane pola telefonu i e-maila odbiorcy dla kuriera i Paczkomatu; przy każdym przycisk Kopiuj z kontaktu. Kopiowanie odbywa się na żądanie, potem pola można zmieniać niezależnie. Telefon kontaktowy pozostaje billing_phone, telefon dostawy shipping_phone, e-mail dostawy w metadanych zamówienia _shipping_email (widoczny w panelu zamówienia); potwierdzenie zamówienia nadal na billing_email. Sprawdzono układ, kopiowanie, oba sposoby dostawy i walidację na serwerze bez zamówień ani wysyłki maili.
Mapa InPost — przygotowanie (22.10.2026): zainstalowano i aktywowano oficjalną wtyczkę InPost PL (inpost-for-woocommerce) 1.9.9 na lokalnym 8081. Ustawienia: WooCommerce → Ustawienia → InPost, pole Geowidget sandbox token dla testów. Token dostarcza użytkownik. Mapa została podłączona do własnego formularza checkoutu po zapisaniu tokenu sandbox; dotychczasowe stawki i klasy wysyłkowe zachowane. Instrukcja InPost: https://dokumentacja-inpost.atlassian.net/wiki/spaces/PL/pages/50069505 — dla sandbox na localhost generowanie tokenu bez wskazywania domeny.
GeoWidget sandbox uruchomiony: publiczny token bez ograniczenia domeny zapisany w ustawieniach InPost, poza kodem Git. Przycisk Wybierz Paczkomat na mapie otwiera okno mapy InPost, po wyborze pokazuje kod i adres oraz przycisk Zmień Paczkomat. SDK i konfiguracja pobierane z oficjalnej wtyczki, domyślny filtr API PARCEL_LOCKER. Kod i adres zapisują się w _wtf_locker_point / _wtf_locker_address oraz _parcel_machine_id / _parcel_machine_desc. Sprawdzono faktyczne ładowanie sandboxa, odpowiedź API 200, wybór rzeczywistego punktu przez API widgetu, przeniesienie kodu/adresu do formularza, zachowanie po przeliczeniu i wymiary mapy na 390 px; bez zamówień i nadawania przesyłek.
GeoWidget: poprawiony argument changePointsType na PARCEL_LOCKER, zgodnie z typem FilterPointsType oficjalnego API. Zweryfikowano widoczne znaczniki i grupy punktów na mapie Warszawy. Flagi UK/UA są natywnym przełącznikiem języka wewnątrz mapy InPost; domyślny język PL.
Tryb ciemny checkoutu: pola, autofill, selecty i kupon używają przygaszonego granatowego tła z jasnym tekstem; tryb jasny zachowuje białe pola. Nakładka przeliczania koszyka/checkoutu korzysta z tła bieżącego motywu, kręciołek to mały pierścień z zielonym akcentem zamiast domyślnej grafiki. Sprawdzono kolory obu trybów i podgląd nakładki w przeglądarce.
Koszyk — zwarty układ: na desktopie produkty po lewej, przyklejone podsumowanie i przejście dalej po prawej; poniżej 900 px jedna kolumna. Usunięto kalkulator dostawy i komunikat o aktualizacji metod podczas finalizacji. Tabela bez zewnętrznego obramowania, jedna linia pod tytułem; mniejsze miniatury i odstępy, równe wysokości pola kuponu/przycisku. Nowe shop-cart.php i cart.css. Testy: dwa produkty w obu motywach, CTA widoczne bez przewijania przy 1440×900, kupon wyrównany, brak kalkulatora/komunikatu, zmiana ilości przelicza kwoty przez natywne AJAX WooCommerce, 390 px bez poziomego przewijania. Bez zamówień i maili.
Koszyk: kolejność kontrolek Zaktualizuj koszyk → Kod kuponu → Wykorzystaj kupon, także po odświeżeniu AJAX; układ w cart.js/cart.css. Checkout: #order_review i tabela podsumowania na pełną szerokość wnętrza panelu Twoje zamówienie (bez domyślnego float WooCommerce). Sprawdzono kolejność po zmianie ilości oraz rzeczywistą szerokość tabeli w przeglądarce.
Doprecyzowanie: Zaktualizuj koszyk w osobnym wierszu nad polem kodu i przyciskiem Wykorzystaj kupon.
