# Szablon produktu — wersja robocza

Pierwszy produkt z osobnym szablonem do oceny: `/produkt/gra-karciana-elekt-wydanie-2/` (ID 802, źródłowe 13255).

Lokalnie poprawiono także standardowy widok pozostałych produktów, oceniany na Wiedźmach 2: dwie kolumny galerii i opisu, mniejszy tytuł, czytelna cena oraz ilość i przycisk zakupu przed krótkim opisem. Polecane produkty zajmują osobny pełny rząd z siatką kart. Na telefonie jedna kolumna, przycisk minimum 48 px. Kolory dziedziczone ze sklepu w obu trybach. Puste akapity odziedziczone w krótkim opisie pomijane wyłącznie przy renderowaniu, bez zmiany treści w bazie. Osobny prototyp ID 802 pozostaje bez zmian. Chrome: 320/390/768/1440 px w obu motywach, geometria galerii i polecanych, powiększanie zdjęcia oraz rzeczywiste dodanie jednej sztuki do lokalnego koszyka poprawne. Zmiany wdrożone na whathefrog.pl na polecenie użytkownika.

- U góry galeria i panel zakupu: nazwa, krótka zajawka, cena, dotychczasowa informacja cenowa, ilość i przycisk WooCommerce. Na komputerze panel przyklejony w obrębie galerii; na telefonie kolumny układają się jedna pod drugą.
- Niżej informacje o produkcie z krótkiego opisu, pełny opis i zawartość oraz trzy dostępne dodatki tej samej gry. Lokalnie oprawa zgodna ze sklepem: jasny/ciemny tryb, granat, biel i zieleń; bez osobnej złotej palety.
- Standardowe opisy, ceny i przypisania obrazów zachowane. Dobór ilustracji nadal wymaga oceny użytkownika; na razie służą jako materiał roboczy do szablonu.
- Identyczne akapity występujące w krótkim i pełnym opisie są wyświetlane raz. Oryginalne pola w bazie pozostają bez zmian.

Edycja: Produkty → produkt → Dane produktu → Ogólne: **Nowy szablon produktu** i **Krótka zajawka przy cenie**. Pozostałe treści, galeria i zakup używają standardowych pól WooCommerce. Informacja cenowa pochodzi z pierwszego akapitu istniejącego krótkiego opisu; szablon nie oblicza historycznych cen.

Kod: WTF Core `shop-product.php`, motyw `templates/single-product.html` i `assets/product.css`. Galeria, powiększanie i formularz zakupu korzystają z WooCommerce. Ręczny `setup-product-elekt.php` włącza wariant tylko na podstawce Elekta; powtórzenie zachowuje późniejsze ustawienia. Wywołuje go również procedura importu produktów.

Lokalny start dev wyłącza ekran „Wkrótce otwarcie” WooCommerce, aby sklep był dostępny także bez logowania.

Standardowy breadcrumb ma strukturę Sklep → gra → produkt; gra wskazuje ten sam filtr game co wybór gry w sklepie. Kategoria pod opisem usunięta. Zdjęcia główne, miniatury i zdjęcia polecanych: kwadratowe pola bez kadrowania; tło z var(--surface), zachowana przezroczystość plików (potwierdzona w grafice Wiedźm 2). Krótki opis Wiedźm 2 poprawiony lokalnie wraz z adresem /elekt/wiedzmy2/. Sprawdzone w Chrome przy 320/390/768/1440 px, oba tryby; wdrożone na whathefrog.pl.
Podstawka ID 802 ma lokalnie rozbudowaną treść z bieżącego opisu produktu i strony Elekta: krótka zajawka przy cenie, parametry gry, wprowadzenie i przebieg rywalizacji, różnice drugiego wydania oraz pełna zawartość pudełka. Usunięto dawny baner innej kampanii i wzmiankę o gratisach. Przypisania grafik pozostają robocze i bez zmian. Opisy zapisane w standardowych polach WooCommerce; zmiana wdrożona na whathefrog.pl.
Galeria podstawki lokalnie: slideshow co 3 s z miniaturami zdjęć w jednym przewijanym rzędzie, bez napisu i licznika zdjęć. Wskazanie zdjęcia zatrzymuje automat; zachowane powiększanie WooCommerce. Wspólny komponent product-gallery.js obsługuje wszystkie produkty w obu szablonach. Przy kilku zdjęciach: karuzela 3 s i miniatury w jednym rzędzie, ręczny wybór zatrzymuje automat. Przy jednym zdjęciu brak karuzeli i panelu miniatur.
