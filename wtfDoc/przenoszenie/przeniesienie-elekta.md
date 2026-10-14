# Elekt w wtf_www

- Gra: `/elekt/`, PL ID 415, EN ID 416 (szkic).
- Cztery podstrony: `/elekt/powolanie2/` (417/418), `/elekt/rubiez/` (419/420), `/elekt/wiedzmy/` (421/422), `/elekt/wiedzmy2/` (423/424). Adresy jak na starej stronie; każda ma powrót do odpowiedniego dodatku na Elekcie.
- Strona gry zachowuje prototyp: 10 dodatków z opisami rozwijanymi na miejscu, 7 combosów, 19 recenzji, Pobierz, Pierwsze wydanie, Autorzy i zajawkę FAQ. Przy czterech dodatkach dodatkowy link „Strona dodatku”.
- Podstrony mają pełne opisy bez akordeonu; Wiedźmy 2 na razie samą dostępną zajawkę. FAQ Rubieży zachowane z prototypu. Na stronie gry FAQ z wtyczki `wtf_faq`: 23 pytania w 4 grupach, głosy 37/2. Zachowana także osobna podstrona `/elekt-faq/`; szczegóły w `wtyczki/wtf_faq.md`.
- Edycja: Strony PL/EN, bloki Układ/Tekst/Grafika/Film Elekt. Treść w bazie WP. Przy grafikach wskazówki rozmiarów i kadrowania; media zarejestrowane, WebP i srcset z istniejącej konfiguracji.
- EN powiązane w Polylang, pozostają szkicami: teksty interfejsu z prototypu, pełne opisy i materiały do tłumaczenia.
- Motyw: `templates/elekt.html`, `assets/elekt.css`, `assets/elekt.js`. Ciemna złota oprawa także przy globalnym jasnym motywie; menu sekcji zachowane.
- WTF Core: `elekt.php`, `elekt-editor.js`; `elekt-seed.json` wyłącznie do ręcznego importu `setup-elekt.php`. Meta `_wtf_ele_key` i `_wtf_ele_imported`; powtórzenie importu zachowuje edycje i nie powiela mediów.
- Sprawdzone: pięć stron PL na desktopie/telefonie, kolory dodatków, kotwice, menu i powrót na górę, zero błędów JS, 597 poprawnych bloków strony gry z shortcode FAQ i zapis/odczyt zmiany w edytorze. Dziesięć stron gry/dodatków tworzy pięć par PL/EN; FAQ ma osobną parę 564/565.
