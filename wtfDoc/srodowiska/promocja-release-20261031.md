# Promocja — wdrożenie 31.10.2026

Kolejna aktualizacja CSS na polecenie użytkownika: plansza bez białej kartki i obramowania, zajawka ukryta do 650 px, marginesy bloku PL/EN po 12 px, mobilna kompozycja pudełka poszerzona z 84% do 100% (max 430 px), padding 12 px. PROD sprawdzony na 320×568, 390×844, 430×932 oraz desktopie: właściwa widoczność tekstu, oba marginesy 12 px, przezroczyste tło planszy, większa ilustracja, logo nadal 12 px nad dołem, bez przepełnienia. Minigra 404. Kopie: `.wtf-staging/kurz-mobile-release-20261031/`, lokalnie `backups/promo-release/20261031/kurz-mobile-before.css`.

Drugie wdrożenie tego dnia: punktowa zmiana treści Kurzołaków (566), zajawka „Nie daj się zwieść słodkim pyszczkom…”, trzy wyróżniki, pogrubiona asymetria w akapicie fabularnym. CSS: cieniowanie kolorowych powierzchni i plama ograniczona do nagłówka. JS/CSS: logo przy dolnej krawędzi pierwszego ekranu mobilnego, z przywróceniem układu desktopowego. Zachowano reguły obecne tylko na PROD. Kopie `.wtf-staging/kurz-copy-release-20261031/` (`content-before.html`, `before-code/`, `published.json`), lokalnie `backups/promo-release/20261031/kurz-copy-before.tar.gz`. Test produkcyjny przeszedł na 320×568, 390×844, 430×932 i 1440 px: logo 12 px nad dolną krawędzią, trzy wyróżniki, gradienty, odstęp nagłówka od opisu, brak przepełnienia, minigra 404. Ruch zewnętrzny zablokowany w teście.

Na polecenie użytkownika wdrożono na `https://whatthefrog.pl`:

- Edytowalne podglądy społecznościowe stron i produktów, grafiki Kurzołaków, Sweet Revenge i historyjek oraz zatwierdzone teksty. SEO historyjek uzupełnione osobno.
- Stałą grafikę wydawnictwa (ciemny kolaż Elekta, Kurzołaków i Klanarchii z logo) i **włączoną tymczasową grafikę AleGramy, stoisko A64, z Muchą**. Powrót po targach: Ustawienia → Ogólne → wyłączyć „Używaj grafiki tymczasowej” → zapisać. Własne grafiki stron mają pierwszeństwo.
- Meta Pixel `2354007262067045`, osobną zgodę marketingową i wersję zgód 3; `PageView` i `ViewContent`, bez CAPI i pomiaru zakupów. Polityki uzupełniono przez dopisanie sekcji, z zachowaniem aktualnej treści PROD.
- Link Facebook do strony What The Frog w stopce.

Paczka ograniczona do siedmiu plików kodu i pięciu grafik. Minigra i jej odnośniki wykluczone; `/kurzolaki/zlap-landrynke/` nadal 404. Bez nadpisywania bazy i treści stron gry/produktów. Aktualne pliki PROD porównano sumami SHA256 przed instalacją.

Kopie: serwer `.wtf-staging/promo-release-20261031/` (`before.json`, `before-code/`, `published.json`); lokalnie `backups/promo-release/20261031/before.tar.gz`. Skrypty i paczka poza Git: `local-scripts/one-off/meta-pixel/`. Mapowanie mediów lokalne → PROD: 4213→4131, 4214→4132, 4215→4133, 4217→4134, 4218→4135. Strony: Kurzołaki 566, Sweet Revenge 4124, historyjki 4108, podstawka 806, produkt Sweet Revenge 805.

Testy przeszły: lokalne regresje cookies/GA4/Meta; na PROD sześć podglądów, obrazy 1200 × 630 i HTTP 200, SEO historyjek, Facebook w stopce, polityki, zgoda/odmowa/cofnięcie i czyszczenie cookies, pojedyncze zdarzenia strony/produktu, wyłączenie prywatnych adresów, brak przepełnienia mobilnego i minigra 404. SDK Meta symulowano, przechwytując cały ruch zewnętrzny. Odbiór rzeczywistych zdarzeń w Menedżerze zdarzeń Meta pozostaje do potwierdzenia; automatyczne dopasowanie i automatyczne zdarzenia w panelu Meta wymagają osobnego sprawdzenia.
