# Wszystkie gry w wtf_www

- `/katalog/`, PL ID 579 / EN ID 580 w szkicu, powiązane w Polylang.
- Układ prototypu: Elekt, Tsukuyumi, Kurzołaki i Klanarchia; gra po lewej, dodatki po prawej, na telefonie jeden blok pod drugim. Publikacje: Echo Światów z odsłuchem Wiedźm oraz komiks Tsukuyumi. Pusty panel dodatków Klanarchii zachowany.
- Boczne menu tytułów na desktopie, poziomy pasek na telefonie; wyróżnienie oglądanej sekcji. Działa globalny motyw jasny/ciemny.
- Treści i linki w blokach katalogu, grafiki i nagranie wybierane z Mediów. Dziewięć grafik i jeden odtwarzacz; `preload="none"`. Nowe grafiki dodatków Elekta i komiksu zarejestrowane, pozostałe media użyte ponownie.
- Linki do przeniesionych gier i dodatków lokalne; Klanarchia pozostaje na starej stronie. „Wszystkie gry”, baner głównej i powroty z Elekta/Kurzołaków prowadzą do katalogu.
- Motyw: `templates/catalog.html`, `assets/catalog.css`, `assets/catalog.js`. WTF Core: `catalog.php`, `catalog-editor.js`, ręczny import `setup-catalog.php`, początkowy eksport `catalog-seed.json`. Meta `_wtf_catalog_key` / `_wtf_catalog_imported`; ponowny import zachowuje edycje i media.
- Sprawdzone: desktop/mobile, układ i kotwice, aktywne menu, lokalne linki i pliki, odsłuch, 90 poprawnych bloków, zapis/odczyt zmiany w edytorze oraz import bez duplikatów.
