# Szablon produktu — wersja robocza

Pierwszy produkt do oceny: `/produkt/gra-karciana-elekt-wydanie-2/` (ID 802, źródłowe 13255). Pozostałe produkty korzystają z dotychczasowego szablonu.

- U góry galeria i panel zakupu: nazwa, krótka zajawka, cena, dotychczasowa informacja cenowa, ilość i przycisk WooCommerce. Na komputerze panel przyklejony w obrębie galerii; na telefonie kolumny układają się jedna pod drugą.
- Niżej informacje o produkcie z krótkiego opisu, pełny opis i zawartość oraz trzy dostępne dodatki tej samej gry. Oprawa Elekta: grafit i złoto.
- Standardowe opisy, ceny i przypisania obrazów zachowane. Dobór ilustracji nadal wymaga oceny użytkownika; na razie służą jako materiał roboczy do szablonu.
- Identyczne akapity występujące w krótkim i pełnym opisie są wyświetlane raz. Oryginalne pola w bazie pozostają bez zmian.

Edycja: Produkty → produkt → Dane produktu → Ogólne: **Nowy szablon produktu** i **Krótka zajawka przy cenie**. Pozostałe treści, galeria i zakup używają standardowych pól WooCommerce. Informacja cenowa pochodzi z pierwszego akapitu istniejącego krótkiego opisu; szablon nie oblicza historycznych cen.

Kod: WTF Core `shop-product.php`, motyw `templates/single-product.html` i `assets/product.css`. Galeria, powiększanie i formularz zakupu korzystają z WooCommerce. Ręczny `setup-product-elekt.php` włącza wariant tylko na podstawce Elekta; powtórzenie zachowuje późniejsze ustawienia. Wywołuje go również procedura importu produktów.

Lokalny start dev wyłącza ekran „Wkrótce otwarcie” WooCommerce, aby sklep był dostępny także bez logowania.
