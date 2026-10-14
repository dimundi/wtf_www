# Uruchomienie sklepu lokalnego

## Zatwierdzone hasło sklepu

Nadtytuł: **PROSTO OD WYDAWNICTWA**

Nagłówek: **Zabierz grę do domu.**

Zatwierdzone przez użytkownika 15.10.2026. Zachować przy dalszych zmianach wyglądu sklepu; nie zastępować bez jego decyzji. Użytkownik zaakceptował pozostawienie hasła po sprawdzeniu wcześniejszego użycia identycznego zwrotu przez Flying Tiger.

## Zakres

Zakres zatwierdzony 15.10.2026:

1. Zainstalować WooCommerce w `wtf_www` (8081).
2. Zbudować sklep według prototypu na 8080, korzystając z rzeczywistych produktów i koszyka WooCommerce.
3. Przenieść tylko opublikowane produkty widoczne w katalogu starego sklepu na 8082: opisy, ceny, warianty, dostępność, kategorie i media. Pominąć produkty ukryte, dostępne tylko w wyszukiwarce oraz wykluczone z katalogu przez ustawienia sklepu. Wyprzedane przenosić tylko wtedy, gdy stary sklep je pokazuje.
4. Nadać nowe ID i zapisać mapowania produktów, wariantów, kategorii i mediów. Mapowania wykorzystamy przy późniejszym przenoszeniu kuponów oraz dostępu do produktów cyfrowych.
5. Zachować ścieżki adresów produktów; lokalnie zmienia się domena i port. Podłączyć lokalne linki sklepu i zakupów.
6. Sprawdzić katalog, filtry, strony produktów i rzeczywisty koszyk na komputerze i telefonie.

Import stanowi początkową ofertę do dalszej edycji. Powtórzenie importu nie powinno tworzyć duplikatów ani nadpisywać ręcznych zmian.

Klienci, zamówienia, kupony, MailPoet i konfiguracja integracji pozostają osobnymi etapami. Nie publikujemy szkiców EN ani nie wdrażamy na produkcję. Zasady historycznych zamówień: [migracja danych](migracja-danych-sklepu.md).

## Wykonanie

- WooCommerce 10.3.8, sklep: `http://localhost:8081/sklep/`. Koszyk `/koszyk/`, zamówienie `/zamowienie/`, konto `/moje-konto/`.
- Przeniesiono 33 produkty widoczne w starym katalogu. Pozostałe opublikowane produkty były wykluczone przez ukrywanie wyprzedanych. W wybranej ofercie wszystkie produkty są proste, bez wariantów.
- Zachowane opisy, ceny i promocje, dostępność, SKU, parametry, kategorie, klasy wysyłkowe i ścieżki `/produkt/.../`. Grafiki i próbki audio zarejestrowane w Mediach, grupa Sklep.
- Cyfrowy album Echo Światów: 14 plików i zachowane identyfikatory pobrań. Pliki w `uploads/woocommerce_uploads`, bez bezpośredniego dostępu HTTP; metoda wymuszonego pobierania. Dostępy dawnych klientów nadal czekają na migrację zamówień.
- Mapowania: [mapowanie-produktow.json](mapowanie-produktow.json); również opcja `wtf_shop_import_map` w bazie i metadane `_wtf_source_product_id`. Dodatkowo mapowania kategorii, klas wysyłkowych oraz mediów.
- Wygląd w motywie `wtf`, obsługa katalogu w WTF Core. Nagłówek sklepu edytuje się w bloku „Sklep WTF” na stronie Sklep; produkty w panelu WooCommerce.

## Sprawdzenie

Zgodność danych wszystkich 33 produktów i niezmienione ścieżki potwierdzone skryptem `verify-shop.php`. Powtórny import utworzył 0 duplikatów. W Chrome sprawdzono katalog, filtr Kurzołaków (4 produkty), wyszukiwanie, stronę produktu, dodawanie do koszyka, zmianę ilości, przeliczenie i usuwanie. Sklep i koszyk bez poziomego przepełnienia na telefonie; główna nadal działa. Bezpośredni adres płatnego pliku zwraca HTTP 403. Płatności i dostawy wymagają osobnej konfiguracji.

## Odtworzenie importu

Po uruchomieniu obu środowisk i zainstalowaniu WooCommerce:

```powershell
.\deploy\shop\import-products.ps1
```

Skrypt wybiera widoczną ofertę, kopiuje media i chronione pliki, importuje dane, zapisuje mapowania i sprawdza zgodność. Nie nadpisuje istniejących produktów. Nie służy do synchronizacji zamówień ani późniejszych stanów magazynowych. Przed pierwszym importem wykonano kopię lokalnej bazy w ignorowanym `backups/shop/before-shop.sql`.

Dane robocze i media w `media/shop-import/` pozostają poza Git. Same źródła i mapowania w Git nie zastępują kopii bazy oraz plików.

Zewnętrzny WooCommerce działa w wolumenie `wtf-www-dev_woocommerce-dev`, aby ograniczyć koszt odczytu tysięcy plików z Windows. Własny motyw i wtyczki nadal są montowane z projektu. Start dev instaluje WooCommerce do pustego wolumenu; nie uruchamia importu. Aktywną kopię WooCommerce aktualizujemy przez WordPress/WP-CLI. [Opis środowiska WSL w Dockerze](https://docs.docker.com/desktop/features/wsl/).
