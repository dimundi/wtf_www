# Pobrania produktów cyfrowych

29.10.2026: wdrożone na PROD na polecenie użytkownika. Kopia poprzednich plików: `.wtf-staging/launch-20261028/downloads-release-20261029/before/`. Wdrożenie porównało sumy plików bazowych, dodało moduł do aktualnego pliku wtyczki i podmieniło wyłącznie widok pobrań oraz account.css/js.

Konto → Pobrania: osobny panel dla produktu z danego zamówienia, nazwa i wspólny termin dostępu nad listą plików. Pojedyncze pobrania zachowują oryginalne linki WooCommerce. Dla wielu aktywnych plików przycisk „Pobierz wszystko (ZIP)”. Obsługuje również e-booki; pliki nie są ograniczone do MP3.

Przy MP3 jest natywny odtwarzacz z kontrolkami, bez autoplay i z `preload=none`. Uruchomienie innego utworu zatrzymuje poprzedni. Odtwarzanie korzysta z chronionego linku WooCommerce, dostarczanego przez PHP z obsługą Range i nagłówkiem inline; nie ujawnia bezpośredniego URL pliku. Zachowuje natywne uprawnienia, terminy i liczenie pobrań WooCommerce, w tym odroczone liczenie żądań częściowych. Przy skończonym limicie widoczna informacja, że odtwarzanie może go wykorzystać. Dla PDF i innych formatów odtwarzacz nie jest pokazywany.

Test odtwarzacza na syntetycznym MP3: rzeczywiste dekodowanie i odtwarzanie w Chrome, zatrzymanie poprzedniego utworu, brak żądań przed odtworzeniem, Range → 206/audio-mpeg/inline i właściwa liczba bajtów; błędny klucz odrzucany. Widoki 390/1440 px, jasny/ciemny. Konta i pliki testowe są izolowane od danych klientów.

ZIP wymaga zalogowania, prawidłowego nonce, własnego zamówienia dopuszczającego pobrania oraz aktualnych uprawnień. Uwzględnia terminy, limity i wyłączone pliki. Każdy plik zużywa jedno pobranie, zapisane przez natywny mechanizm WooCommerce dopiero po przygotowaniu archiwum. Blokada wierszy uprawnień zabezpiecza równoległe żądania paczki.

Archiwum powstaje w prywatnym katalogu tymczasowym, jest wysyłane przez PHP i usuwane po obsłudze żądania. Nie powstaje publiczny URL do ZIP-a. Obsługiwane są lokalne pliki w uploads i `download/`, bez pobierania zewnętrznych adresów. Limit: 100 plików i łącznie 1 GiB. Brakujący plik przerywa przygotowanie całej paczki, bez zużycia limitów. Wymagane rozszerzenie PHP ZIP.

Kod: `wtf-core/shop-downloads.php`, `views/account-downloads.php`, style `themes/wtf/assets/account.css`. Ładowanie modułu przez `wtf-core.php`.

Testy lokalne na sztucznych kontach i plikach: właściciel/inny klient, wygaśnięcie, wyczerpanie limitu, odrzucenie konfiguracji serwera i zewnętrznego URL, zawartość archiwum. Przeglądarka: 390/1440 px, jasny/ciemny wygląd, brak przepełnienia; HTTP odrzuca niezalogowanego klienta i błędny nonce, zwraca prawdziwy ZIP oraz zmniejsza limit. Nie pobierano plików rzeczywistego klienta ani nie zmieniano jego uprawnień. Narzędzia jednorazowe i zrzuty w `local-scripts/one-off/launch-20261028/` oraz `backups/launch-20261028/`.

Kontrola PROD: PHP ZIP dostępne, wszystkie 14 rzeczywistych MP3 obecne. Wygenerowano i usunięto prywatny ZIP 14 plików, 179,4 MiB. Renderowanie widoku z aktualnymi uprawnieniami potwierdziło 14 odtwarzaczy i przycisk paczki; liczniki pobrań klienta porównano przed/po — bez zmian. Publiczna główna, konto i nowe CSS/JS: 200; ZIP bez logowania: 403. Nie wykonywano przeglądarkowego odtwarzania z konta rzeczywistego klienta. Ta zmiana nie implementuje odłożonego mechanizmu zgód na wcześniejsze dostarczenie treści cyfrowych ani ręcznego przyjęcia zamówienia.
