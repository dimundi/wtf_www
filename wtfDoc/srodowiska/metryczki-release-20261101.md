# Metryczki produktów — PROD 1.11.2026

Na polecenie użytkownika wdrożono metryczki wszystkich 34 produktów: Kurzołaki (5), Elekt z publikacjami (16), Tsukuyumi (13). Pola edytowane w Dane produktu → Metryczka; puste informacje pomijane, zakresy walidowane. Dymki obsługują mysz, klawiaturę i dotyk. Metryczka wyświetla się również przy niedostępnym produkcie.

Chronomasters PL/EN/DE; Sunrise Kingdom i planszetki nr 4 PL* z dopiskiem o angielskich nazwach jednostek i frakcji. Plastikowe podstawki bez języka. Dwujęzyczne produkty Kurzołaków opisują obie wersje w jednym produkcie. Podstawka 2–3*, zestaw 2–4 bez gwiazdki.

Panel podstawki Kurzołaków: usunięta zajawka i skróty pod zakupem. Kupon ma zwarty układ, nagłówek „Wykorzystaj kupon” oraz ikonę kopiowania kodu z potwierdzeniem. Etykieta „Cena” przesunięta 8 px niżej. Style metryczki ograniczone do bloku z role=group, aby nie zmieniać starszych list w opisach dodatków.

Paczka zawiera dziewięć plików. Loader przygotowany z bieżącej wersji PROD, dodano tylko moduł metryczek; pozostałych lokalnych modułów nie przeniesiono. Zmiany bazy ograniczono do wskazanych metadanych produktów i nagłówka banera 4146. Zestaw lokalny 4205 mapowany na PROD 4130. Bez zmian cen, zapasów, kuponów WooCommerce, zamówień i pozostałych treści.

Kopie i potwierdzenie: `backups/product-facts-release/20261101/facts-release-20261101/`. Serwer: `.wtf-staging/facts-release-20261101/`. Skrypt sprawdza sumy plików i poprzednie wartości pól, przy błędzie przywraca stan sprzed operacji. Narzędzia: `local-scripts/one-off/banner/`.

Testy PROD przeszły: wszystkie 34 strony przy 1440 px i osiem reprezentatywnych przy 390 px; dokładne wartości i jeden blok metryczki, dopiski językowe, brak przepełnienia, pionowe listy czterech dodatków Elekta, uproszczony panel Kurzołaków i rzeczywiste kopiowanie KURZOLAKI do schowka. Minigra nadal zwraca 404. Składnia czterech plików PHP sprawdzona na serwerze przed publikacją.

Kolejne wdrożenie na polecenie użytkownika: `facts-mobile-release-20261101`, trzy pliki CSS/JS, bez zmian bazy i PHP. Separatory ukryte na początku zawiniętych wierszy; dymki ograniczone do szerokości ekranu z 8 px zapasu; panel ceny nowego szablonu do 700 px rozciągnięty do krawędzi ekranu. Testy dotykowe na PROD dla podstawki Kurzołaków, Elekta, Sunrise Kingdom i Chronomasters przy 320/390/430/700/1440 px przeszły: poprawne separatory, widoczne dymki w granicach ekranu, mobilne panele na pełną szerokość, brak poziomego przewijania. Kopie: `backups/product-facts-release/20261101/facts-mobile-release-20261101/`.
