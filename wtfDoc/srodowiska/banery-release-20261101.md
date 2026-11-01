# Banery i poprawki sklepu — PROD 1.11.2026

Wdrożono na wyraźne polecenie użytkownika. Nowa wtyczka `wtf_banner`, punktowo `wtf-core/shop.php`, `shop-product.php`, `assets/shop.css`. Porównanie z aktualnym PROD wykazało wyłącznie zamierzone różnice. Bez zastępowania bazy, bez plików DEV i minigry.

Banery PROD: **4145** ALEgramy na głównej przed Gamefound; **4146** wspólny kupon KURZOLAKI w sklepie i przy cenach produktów 806/4130; **4147** graficzne ALEgramy pod kuponem. AleGramy kończy emisję 8.11.2026 o 17:00 czasu witryny. Kupon bez terminu, powiązany z istniejącym kodem WooCommerce (4140), jego konfiguracja bez zmian. Mobilny sklep pokazuje oba banery obok siebie.

Media PROD: 4142 zaproszenie ALEgramy, 4143 wskazany kwadrat Elekta, 4144 wspólna grafika zestawu z plamami. Elekt 802 ma nową ilustrację wiodącą; zestaw 4130 ma jedną grafikę bez osobnej ilustracji plam i plusa. Skład, ceny, stany magazynowe i galerie bez zmian. Zestaw jest dostępny również w filtrze Wszystkie.

Główna 13: dodano tylko blok miejsca emisji, przed istniejącym Gamefound, z tą samą szerokością i odstępem 32 px. Materiały Klanarchii 3213: usunięto tylko wygasły blok Elekt w niższej cenie; rekord kuponu KLANARCHIA zachowany. Ręczne komunikaty kuponu przy 806/4130 zastąpiono wspólnym banerem.

Testy PROD przeszły: główna 390/1440/2560 px; sklep 390/1440 px (kolejność, liczba i układ banerów, linki kanoniczne, brak przepełnienia); oba produkty (jeden baner, pełna szerokość przy cenie, ceny 150/220 zł); pojedyncza nowa grafika zestawu w Wszystkich; nowy kwadrat Elekta; brak starej promocji Klanarchii z zachowaniem Gamefound. Minigra `/kurzolaki/zlap-landrynke/` nadal 404.

Kopie przed wdrożeniem i potwierdzenie: `backups/banner-release/20261101/release-20261101/`; serwer `.wtf-staging/release-20261101/`. Skrypt ma kontrolę sum i stanu danych oraz odtworzenie poprzednich plików/stron/produktów przy błędzie. Narzędzia: `local-scripts/one-off/banner/`. W tej paczce nie ma danych sesji testowej administratora.
