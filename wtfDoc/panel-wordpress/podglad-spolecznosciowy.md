# Podgląd linków społecznościowych

31.10.2026 wdrożono na PROD. Stała grafika: ciemny kolaż z logo (DEV 4217 / PROD 4134). **Aktywna tymczasowa: AleGramy, stoisko A64, Mucha** (DEV 4218 / PROD 4135). Poprzednie grafiki zachowane w Mediach.

W **Ustawienia → Ogólne → Grafiki społecznościowe wydawnictwa** są dwa osobne pliki: stała grafika i grafika tymczasowa oraz przełącznik **Używaj grafiki tymczasowej**. Zapis ustawień przełącza wariant; wyłączenie przywraca zapamiętaną stałą grafikę. Własna grafika strony i obrazek wyróżniający mają pierwszeństwo. Brak/usunięcie pliku tymczasowego przywraca stałą grafikę. Facebook może wymagać odświeżenia swojej kopii podglądu.

Stała grafika to kolaż Elekta, Kurzołaków i Klanarchii z oryginalnym logo WTF, bez Tsukuyumi, 1200 × 630. Opcje: `wtf_social_default_image`, `wtf_social_temporary_image`, `wtf_social_use_temporary`. Sprawdzono przełączanie, powrót, brak/usunięcie pliku i pierwszeństwo własnych grafik.

W edycji każdej strony i produktu: **Podgląd w mediach społecznościowych** — tytuł, opis, wybór grafiki z Mediów oraz karta aktualizowana podczas wpisywania. Zapis przyciskiem Aktualizuj/Zapisz strony. Podgląd jest orientacyjny; Facebook może zmienić kadrowanie oraz przechowuje własną kopię metadanych.

Puste pola: tytuł SEO → tytuł strony; opis SEO → zajawka; grafika społecznościowa → obrazek wyróżniający → aktywna grafika wydawnictwa. Wybór w **Ustawienia → Ogólne → Grafiki społecznościowe wydawnictwa**. Zalecane 1200 × 630 px, JPG/PNG/WebP. Bez ustawionej grafiki tag `og:image` jest pomijany.

Kod: `wtf-core/social-preview.php`, `.js`, `.css`, wywołanie w `seo.php`. Metadane `_wtf_social_title`, `_wtf_social_description`, `_wtf_social_image`; opcja `wtf_social_default_image`. Dotychczasowe ograniczenia publicznych stron SEO pozostają aktywne.

Sprawdzone lokalnie: zapis i czyszczenie pól, sanitacja, wartości zastępcze, pojedyncze tagi OG oraz aktualizacja karty w Chrome. PROD: sześć podglądów stron i produktów, obrazy HTTP 200 i 1200 × 630, aktywna grafika targowa na głównej, własne grafiki gier zachowane. [Kopie i zakres wdrożenia](../srodowiska/promocja-release-20261031.md).
