# Przeniesienie sklepu — tematy do omówienia

Próba klientów/zamówień/pobrań 22.10.2026: [procedura i wyniki](probna-migracja-klientow-zamowien.md). Pozostałe etapy poniżej nie są przez to uznane za wykonane.

1. Konta klientów: przenosimy tylko klientów, zachowując hasła i powiązania z zamówieniami. Nadajemy rolę „Klient” (`customer`). Kont administratorów i innych kont nieklienckich nie przenosimy.
2. Historia zamówień: notatki, zwroty, dane płatności, przesyłek i faktur.
3. Nowe produkty tworzymy od nowa; szczegóły oferty, SKU i wariantów ustalamy podczas implementacji sklepu.
4. Produkty fizyczne w starych zamówieniach: dane historyczne bez powiązania z nową ofertą.
5. Produkty cyfrowe: powiązania plików, uprawnienia, limity i ważność pobrań.
6. Sposób chronionego udostępniania plików cyfrowych.
7. Hurtowe generowanie unikalnych kuponów dla pojedynczego produktu.
8. MailPoet: przenosimy możliwie całość — subskrybentów, listy, zgody, wypisania, formularze, wiadomości, szablony, historię, statystyki, automatyzacje i ustawienia.
9. Na start zachowujemy obecne metody płatności, dostawy i integracje. Funkcjonalności rozszerzamy po uruchomieniu sklepu.
10. Przekierowania zmienionych adresów: 301 w proxy nginx, osobna mapa adresów w Git. Ścieżki gier i frakcji zachowujemy; linki pobierania rozpatrujemy osobno.
11. Wszystkie kupony, bez kasowania: zachowujemy ciągłość, statusy, zasady, limity i wykorzystanie. Przenosimy pod koniec, po utworzeniu nowej oferty, i wiążemy z odpowiednimi produktami, wariantami oraz kategoriami.
12. Blokada ponownej wysyłki maili i wykonania starych zadań podczas migracji i testów.
13. Testy zakupu, płatności, wysyłki, kuponów, konta i pobierania plików.
14. Końcowy backup, synchronizacja nowych danych, przełączenie i możliwość wycofania wdrożenia.

Szczegółowe ustalenia: [migracja danych sklepu](migracja-danych-sklepu.md). Lista zawiera również tematy do ustalenia, nie zatwierdza całego zakresu migracji.
