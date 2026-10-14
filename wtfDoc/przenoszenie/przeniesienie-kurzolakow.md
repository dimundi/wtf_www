# Kurzołaki w wtf_www

- Adres jak na oryginale: `/kurzolaki/`. PL ID 566, EN ID 567 w szkicu, powiązane w Polylang. Cytaty recenzentów pozostają po polsku także w szkicu EN.
- Układ prototypu: otwarcie, opis, Flejtuchy/Robale/Puca, Sweet Revenge, jak grać, dwa filmy, opinie i autorzy. Jasna papierowa oprawa z malinowym akcentem, niezależna od globalnego motywu.
- Treści w blokach Układ/Tekst/Grafika/Film Kurzołaków, edytowane przez Strony PL/EN. Opisy grafik z rozmiarami i kadrowaniem; cztery grafiki w Mediach, WebP i srcset z istniejącej konfiguracji. Plansza nadal oznaczona jako prototyp.
- Motyw: `templates/kurzolaki.html`, `assets/kurzolaki.css`. WTF Core: `kurzolaki.php`, `kurzolaki-editor.js`, ręczny import `setup-kurzolaki.php`, początkowy eksport `kurzolaki-seed.json`. Meta `_wtf_kurz_key` i `_wtf_kurz_imported`; ponowny import zachowuje edycje i media.
- Linki z głównej i menu prowadzą lokalnie. Sweet Revenge nadal prowadzi do starego sklepu. Końcowy powrót prowadzi do sekcji gier na głównej.
- Sprawdzone: desktop/mobile, wszystkie kotwice, menu, układ frakcji, grafiki i dwa filmy, 96 poprawnych bloków, zapis/odczyt zmiany w edytorze i import bez duplikatów. Brak błędów JS i poziomego przepełnienia strony.
