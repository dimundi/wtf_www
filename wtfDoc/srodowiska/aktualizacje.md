# Aktualizacje strony i bazy danych

- Produkcyjny WordPress będzie zmieniał się niezależnie od Gita: będą powstawały nowe treści, media i dane użytkowników.
- Git przechowuje własny motyw, własne wtyczki, konfigurację środowisk, dokumentację oraz skrypty lub instrukcje zmian w bazie.
- Produkcja jest źródłem aktualnych danych działającej strony. Lokalna baza jest środowiskiem roboczym i również będzie modyfikowana podczas przebudowy.
- Zmiany wyglądu, stron i ustawień zapisane w bazie wymagają osobnego przeniesienia; samo wdrożenie kodu z Gita ich nie obejmuje.

## Pierwsze wdrożenie

- Paczka przygotowana przez wtyczkę migracyjną posłuży do pierwszego przeniesienia przygotowanej strony na VPS.
- Przed publikacją trzeba uwzględnić zmiany, które w czasie przebudowy powstały na dotychczasowej produkcji, zachowując lokalne zmiany przebudowy.
- Na czas końcowej synchronizacji i przeniesienia wstrzymujemy edycję strony. Szczegółowy sposób synchronizacji ustalimy po poznaniu danych i używanego edytora.

## Kolejne aktualizacje

- Nie nadpisujemy całej produkcyjnej bazy lokalną kopią, ponieważ mogłoby to usunąć nowe treści i dane produkcyjne.
- Przed wdrożeniem wykonujemy kopię zapasową produkcji.
- Zmiany sprawdzamy na świeżej kopii produkcji w środowisku lokalnym.
- Wdrażamy zmieniony kod oraz wybrane zmiany w bazie: konkretne strony, szablony lub ustawienia, albo skrypt migracyjny.
- Sposób wprowadzenia zmian w bazie zapisujemy w Git jako skrypt lub dokładną instrukcję.
- Własny kod zmieniamy lokalnie i zapisujemy w Git; bieżące treści redagujemy na produkcji.
- Aktualizacje WordPressa i zewnętrznych wtyczek najpierw sprawdzamy lokalnie na kopii produkcji, a następnie wykonujemy na serwerze.
- Wtyczka migracyjna służy również do pobierania pełnych kopii strony. Sposób przenoszenia wybranych zmian zależy od używanego edytora i narzędzi; nie został jeszcze ustalony.

## Dostęp do produkcyjnej bazy przez tunel SSH

- Możemy połączyć lokalnego klienta bazy z bazą na VPS przez tunel SSH, bez publicznego wystawiania portu bazy.
- Tunel wykorzystujemy do administracji, sprawdzania danych i kontrolowanego wdrażania konkretnych zmian.
- Do przebudowy lokalny WordPress korzysta z osobnej lokalnej bazy.
- Podłączenie lokalnego WordPressa do produkcyjnej bazy jest technicznie możliwe, ale lokalne operacje od razu zmieniają produkcję. Różnice kodu, wtyczek i adresów mogą wpływać na działającą stronę.
- Tunel zabezpiecza połączenie, ale nie izoluje zmian od produkcji.
