# Gamefound — obsługa zbiórek

Stan: 5.11.2026, wtyczka `wtf_gamefound` 1.0.0. Prace wyłącznie lokalnie. PROD wymaga wyraźnego polecenia użytkownika. Dostęp do wszystkich danych i operacji wyłącznie dla administratora (`manage_options`), z kontrolą uprawnień i nonce na serwerze.

## Baza

Własne tabele w tej samej bazie MySQL co WordPress; nazwy poniżej poprzedza prefiks instalacji. Dane nie są zamówieniami, produktami ani kontami WooCommerce.

| Tabela | Zawartość i klucz |
| --- | --- |
| `wtfg_customers` | Klienci: zbiórka + UserID Gamefound, imię/nazwisko, znacznik firmy, dane klienta. Bez zakładania kont WordPressa. |
| `wtfg_products` | Produkty: zbiórka + ID Gamefound, nazwa. |
| `wtfg_variants` | Osobne warianty produktu, także podstawowy o pustej nazwie. Klucz produktu + SHA-256 dokładnej nazwy wariantu. |
| `wtfg_orders` | Zbiórka + wewnętrzny OrderID, publiczny numer, UserID klienta, status zamówienia/płatności, waluta, kwoty DECIMAL(20,4), oryginalny opis. |
| `wtfg_order_items` | Osobny wpis dla każdego wiersza Order items; źródłowe ID zamówienia, klienta i produktu, wariant, ilość, źródłowa nazwa, zatwierdzone powiązania oraz stan błędu. |
| `wtfg_imports` | Jeden wspólny dziennik: administrator, zbiórka, czas, skrót plików, wynik, liczniki, podpis stanu i szyfrowany tymczasowy podgląd. |
| `wtfg_changes` | Zastosowane automatyczne dodania i zatwierdzone aktualizacje: import, administrator, czas, rodzaj/ID rekordu, operacja, nazwy zmienionych pól. |

Schemat wersja `3`, opcja `wtfg_schema`. Unikalne klucze zapobiegają duplikatom. Powiązanie klienta wykorzystuje **UserID Gamefound oraz zbiórkę**; dane osobowe nie są kopiowane do zamówienia. Adres wysyłki będzie należał do zamówienia — dodamy go przy obsłudze wysyłek. Warianty nie są przechowywane jako JSON przy produkcie. Statusy, numery i kwoty zamówień mają zwykłe kolumny. Dane kontaktowe klienta pozostają prywatnym obiektem JSON, z osobnymi kolumnami nazwy i firmy do wyświetlania/filtrowania.

Zbiórki i konfiguracja banerów pozostają rekordami WordPressa `wtfg_campaign` z metadanymi. Nie są kasowane przy przebudowie tabel. Istniejący shortcode i identyfikatory kampanii zachowano.

## Import z wp-admin

Menu: **Zbiórki**, **Produkty**, **Wspierający**, **Zamówienia**, **Baner**. Zbiórkę wybiera się raz radiobuttonem; wybór jest zapamiętywany osobno dla administratora. Konfiguracja obok nazwy pozwala wpisać ProjectID. Kolejne zbiórki nie tworzą nowych menu.

Link **Importuj dane** prowadzi do wspólnego importu na ekranie Zamówienia. Nie ma dodatkowego podmenu. Przyjmujemy CSV **rozdzielany przecinkiem**, UTF-8 lub UTF-16 LE/BE z BOM, do 8 MB na plik. Informacja o przecinku jest obok przycisku importu. Pliki nie trafiają do publicznych uploads i nie są zachowywane po żądaniu.

Można przesłać dwa pliki razem albo tylko jeden:

1. **Orders**: zamówienia i dostępne dane klientów. Wymagane `ProjectID`, `OrderID`, `UserID`, **`ProductsOrdered`**. Jeśli występują `CustomerFullName` oraz `CustomerEmail`, odczytujemy także klientów. Dopuszczamy puste dane kontaktowe. Konflikt niepustych danych tego samego klienta w jednym pliku pomija klienta i jest raportowany.
2. **Order items**: katalog produktów/wariantów oraz elementy zamówień. Wymagane `ProjectID`, `OrderID`, `UserID`, **`ProductName`**, `Quantity`. ProjectID musi już być skonfigurowany albo wynikać z dołączonego Orders.

Linki na ekranach **Wspierający** i **Produkty** otwierają ten sam importer w trybie ograniczonym: tylko klienci albo tylko katalog z **Order items**. Katalog wymaga jedynie ProductName; inne kolumny są dopuszczalne. Jeśli występuje ProjectID, filtruje dane do aktywnej zbiórki.

Orders może ustalić pusty ProjectID automatycznie tylko przy jednej jednoznacznej zbiórce. Skonfigurowany ProjectID ogranicza rekordy; inne projekty są pomijane z raportem. Ten sam ProjectID nie może wskazywać dwóch lokalnych zbiórek. Po zapisaniu danych zwykły formularz nie pozwala zmienić ProjectID.

Limity parserów: 50 000 wierszy, 5000 zamówień/osób/produktów na plik. Błędne nagłówki i sprzeczne nazwy tego samego ID produktu odrzucają plik. Nierozpoznane nazwy produktów są pomijane przy tworzeniu katalogu; odpowiadające im elementy mogą pozostać błędnymi pozycjami do uzupełnienia. `Bonus amount` oznacza produkt **1**. `Free Gift:` nie tworzy osobnego produktu. Wariant odczytujemy z nawiasu po `[ID]`.

## Reimport i potwierdzanie każdej zmiany

- Po uruchomieniu importu **nowe, poprawne i jednoznaczne rekordy zapisują się automatycznie**. Klienci, produkty i zamówienia są zapisywane przed nowymi elementami, aby ustalić ich powiązania.
- Istniejące rekordy bez zmian pozostają nietknięte. **Żadna zmiana istniejących danych nie zapisuje się automatycznie**: dotyczy statusów, danych klienta, opisu, kwot, nazw, wariantów, ilości, powiązań i usuwania elementów.
- Podgląd pokazuje wartości przed i po. Każdy zmieniany rekord ma domyślnie niezaznaczony checkbox: administrator potwierdza wszystkie pokazane zmiany tego rekordu. Checkbox „Zaznacz wszystkie zmiany na tej stronie” zaznacza lub odznacza wyłącznie widoczne rekordy; zapis nadal wymaga osobnego kliknięcia przycisku zastosowania zmian. Można zatwierdzić część rekordów; pozostałe nadal oczekują. Zmiany nie są domyślnie zaznaczone i nie ma automatycznej zgody.
- Zmiana listy elementów istniejącego zamówienia jest wspólną propozycją z pełną listą przed/po. Zmiana produktu lub wariantu nie może ominąć potwierdzenia przez dodanie nowego klucza wiersza. Pierwsza lista elementów, gdy zamówienie nie ma jeszcze żadnych, jest nowymi rekordami.
- Nowy produkt lub zmiana zamówienia nie nadpisują zapisanych powiązań elementów. Import wykrywa propozycje ich aktualizacji. Jeśli zatwierdzenie produktu/zamówienia zmienia wcześniej pokazaną propozycję powiązania, wymaga ona kolejnego podglądu i potwierdzenia.
- Zatwierdzenie wymaga tego samego administratora, ważnego podglądu i identycznego stanu danych/ProjectID. Nowe dane lub równoległa aktualizacja unieważniają stary podgląd; trzeba wczytać plik ponownie.
- Puste komórki częściowego importu klienta lub zamówienia zachowują istniejące niepuste wartości. Nie służą do kasowania danych. Nieobecne produkty i warianty są zachowywane; nowe warianty istniejącego produktu wymagają potwierdzenia.
- **Brak zamówienia w pliku nie anuluje go ani nie usuwa.** Anulowanie wynika wyłącznie ze statusu, a zmiana statusu również wymaga potwierdzenia.
- Order items wymaga potwierdzenia, że CSV zawiera **komplet elementów zamówień w nim wymienionych**. Dopiero zatwierdzenie pokazanej zmiany pozwala usunąć ich nieobecne elementy. Zamówienia niewymienione w pliku zachowują zawartość.

Cały import/aktualizacja oraz dziennik korzystają ze wspólnej blokady zapisu i transakcji InnoDB. Błąd zapisu cofa dane operacji. Ponowny import tych samych danych nie tworzy duplikatów.

Podgląd zmian wyróżnia dodania na zielono i usunięcia na czerwono. Opis Orders porównujemy wizualnie wierszami z wyróżnieniem zmienionych słów, bez zmiany źródłowego zapisu; pełne oryginały są pod rozwijanym linkiem. Kwoty pokazujemy z przecinkiem dziesiętnym i bez zbędnych końcowych zer. Dla elementów domyślnie pokazujemy tylko dodane, usunięte i zmienione pozycje oraz konkretne zmienione pola; pełne listy przed/po można rozwinąć. Zasady zatwierdzania pozostają bez zmian.

## Zamówienia i statystyki

`OrderID` to wewnętrzny identyfikator Gamefound używany do łączenia eksportów, np. `15486801`. `OrderPublicID` to numer dla człowieka, np. `1539204V`. To różne pola. `OrderCode` nie jest zapisywany.

Z Orders zachowujemy jedynie: ProjectID poprzez zbiórkę, OrderID, OrderPublicID, UserID, ProductsOrdered, OrderStage, OrderPaymentStatus, `Order value with shipping (transaction currency)`, TotalPaid i CurrencyShortName. Pola podatkowe, faktury, uwagi i adresy wysyłkowe są obecnie ignorowane. Dane klienta trafiają do klientów, nie do zamówień.

**ProductsOrdered pozostaje tekstem 1:1**, także ze spacjami i nowymi liniami. Wyświetlamy go bezpiecznie z zachowaniem formatowania do wzrokowej weryfikacji. Nie rozbijamy go na pakiety i nie tworzymy relacji do katalogu. Usunięto mechanizm i przycisk „Powiąż produkty (Orders)” oraz tabelę `wtfg_order_products`.

Lista zamówień pokazuje numer publiczny, status, płatność, kwotę, nazwę klienta i UserID. Znacznik **Firma** pojawia się wyłącznie dla firm. Wyszukiwanie po OrderPublicID (także fragmencie), filtr statusu i typu klienta łączą się oraz zachowują przy stronicowaniu. Brak danych firmy jest odróżniany od osoby prywatnej. `Pledged` oznacza zadeklarowane, nie potwierdza zapłaty; `Canceled`/`Cancelled` oznacza anulowane.

Order items jest **jedynym źródłem elementów do realizacji**. Każdy wiersz pozostaje osobną pozycją, również powtarzający produkt. Klucz pozycji: zbiórka + wewnętrzny OrderID + skrót produktu/wariantu i kolejności jego wystąpienia w zamówieniu. Oryginalną nazwę i ilość zachowujemy. Nie sumujemy pozycji podczas zapisu.

Brak zamówienia, UserID, produktu, wariantu, niezgodność klienta oraz błędny format nazwy/ilości są jawnymi stanami błędu. Nie tworzymy fikcyjnych zamówień ani produktów z nierozpoznanej nazwy. Błędne elementy są widoczne także bez znalezionego zamówienia.

Na ekranie Produkty statystyki sumują ilości tylko poprawnie powiązanych elementów, osobno dla wariantów i łącznie dla produktu. **Pomijają zamówienia anulowane**, błędne pozycje i niezatwierdzone propozycje powiązań. Opis Orders nie jest liczony. Przy brakach pokazujemy ostrzeżenie; produkty bez sprzedaży mają zero. Nie filtrujemy po zapłacie, ponieważ wymaganiem jest wykluczenie anulowanych zamówień.

## Logowanie, prywatność i wydajność

Jeden **Dziennik importów** dostępny przez link, bez dodatkowego menu. Rejestruje wyniki, liczniki, błędy oraz automatyczne dodania i zatwierdzone aktualizacje. Osobny wpis zmiany zawiera wyłącznie rodzaj/ID rekordu, administratora, czas, źródłowy import i nazwy pól — bez adresów, imion, e-maili, telefonów i opisu zamówienia. Nie logujemy publicznych odsłon.

Podgląd jest szyfrowany AES-256-GCM kluczem zależnym od soli WordPressa, przypisany do administratora i ważny dobę. Po zastosowaniu wszystkich zmian payload jest czyszczony; wygasłe podglądy są czyszczone przy kolejnej wizycie na ekranach Gamefound. Dziennik nie przechowuje historycznych danych osobowych. Retencja zakończonych dzienników pozostaje do ustalenia.

Moduł administracyjny ładuje się tylko w panelu. Publiczny baner zachowuje dotychczasowe działanie i nie wykonuje zapytań do tabel zamówień. Listy są stronicowane, powiązania listy pobierane zbiorczo, statystyki liczone agregacjami SQL. Nie tworzymy maili, płatności, przesyłek, automatycznych zadań ani połączeń z API Gamefound.

W panelu nie ma przycisku ani ustawienia kasowania danych. Wyłączenie i zwykłe odinstalowanie zachowują dane. Techniczne czyszczenie przez `uninstall.php` wymaga jawnej stałej `WTF_GAMEFOUND_DELETE_DATA === true` poza panelem; obejmuje własne tabele, zbiórki i ustawienia, bez mediów i WooCommerce. Multisite wymaga osobnej obsługi.

## Wykonana przebudowa lokalna

Na polecenie użytkownika 5.11.2026 usunięto dziewięć starych tabel Gamefound i utworzono siedem nowych. **Nie wykonano importu plików użytkownika. Wszystkie nowe tabele pozostawiono puste.** Zbiórki, identyfikatory, wybór administratora i pełne metadane banerów zachowano oraz porównano przed/po. Innych tabel WordPressa i WooCommerce nie zmieniano.

Prywatna kopia starych tabel i ustawień: `backups/gamefound-rebuild/20261105/`, poza Git. Kod przed przebudową i jednorazowe narzędzia: `local-scripts/one-off/gamefound/rebuild-20261105/`, poza Git. Lokalny reset wykonano uwierzytelnionym żądaniem wp-admin, ograniczonym do środowiska local; tymczasowy pomocnik runtime został usunięty. Reset nie jest funkcją panelu ani automatyczną migracją instalacji produkcyjnej.

Weryfikacja: test parsera CSV/planowania zmian przez PHP CLI przeszedł. Testy na sztucznych danych w transakcji zakończonej ROLLBACK potwierdziły nowe rekordy bez potwierdzenia, zachowanie wszystkich istniejących danych do zatwierdzenia, idempotencję, osobne wiersze produktów, oryginał opisu, pomijanie anulowanych i błędnych pozycji w statystykach, warianty, propozycje naprawy powiązań, zachowanie danych częściowych, szyfrowanie i wykrywanie nieaktualnego podglądu. Testowe dane nie pozostały w bazie. Wszystkie ekrany panelu i publiczna strona materiałów zwróciły HTTP 200 bez błędów PHP; końcowa kontrola potwierdziła siedem pustych tabel. Test rzeczywistego importu CSV pozostaje do wykonania przez użytkownika.

## Dalsze etapy

Import użytkownika i ocena podglądu zmian, następnie Podsumowanie/Pomoc z instrukcją. Później adresy przy zamówieniach, listy pakowania, przesyłki, maile i uprawnienia cyfrowe. Dane naszej realizacji będą oddzielone od danych Gamefound, żeby reimport ich nie nadpisywał; zmiana zawartości już spakowanego zamówienia wymaga ostrzeżenia. Nie dodajemy tych funkcji ani ich tabel na zapas.
