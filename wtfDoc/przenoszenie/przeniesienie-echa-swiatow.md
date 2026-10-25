# Echo Światów w wtf_www

- Adres jak na oryginale: `/echo-swiatow/`. PL ID 504, powiązany EN ID 505 w szkicu do oceny tłumaczenia.
- Układ prototypu: otwarcie, album, film, muzyka, artbook, twórcy. Dziesięć nagrań i dwa filmy, ciepła ciemna oprawa niezależna od globalnego motywu.
- Teksty, grafiki, linki i filmy edytowalne w Strony PL/EN. Blok „Nagranie Echo Światów” wybiera audio z Mediów i pozwala zmienić opis. Nagrania mają `preload="none"`; uruchomienie jednego zatrzymuje pozostałe.
- Skopiowano i zarejestrowano 27 plików albumu: 17 grafik i 10 nagrań. Grafika Wiedźm występuje też w otwarciu. Grafiki korzystają z WebP i srcset; nagrania pozostają w oryginalnych MP3/WAV.
- Przy grafikach opis rozmiarów/kadrowania. Grafika utworu jest prezentowana w 16:9; zalecane 1200 × 675 px. Otwarcie: zalecane 1920 × 1080 px, jeden obraz kadrowany także na telefonie.
- Motyw: `templates/echo.html`, `assets/echo.css`, `assets/echo.js`. WTF Core: `echo.php`, `echo-editor.js`, ręczny import `setup-echo.php`, początkowy eksport `echo-seed.json`. Treści w bazie; meta `_wtf_echo_key` i `_wtf_echo_imported` chronią ponowne uruchomienie.
- Sprawdzone: desktop/mobile, rozdziały, 10 odtwarzaczy, 2 filmy, brak pobierania audio przed odtworzeniem, jedno nagranie naraz, HTTP i obsługa zakresów dla audio, 148 poprawnych bloków oraz zapis/odczyt zmiany w edytorze. Powtórny import zachowuje edycje i media.

Hero PL 504: nowa grafika Echo_hero.png użytkownika, Media 3279, grupa Echo Światów. Oryginał zachowany, WebP i srcset przez Modern Image Formats. Usunięto powiększenie obrazu 140%; kadr pokrywa hero w naturalnej skali, tekst po lewej, muzyk po prawej. Na telefonie object-position 64% i dodatkowe przyciemnienie pod tekstem. Podmiana nadal edytowalna w bloku grafiki. EN bez zmian. Sprawdzono 1440 i 390 px bez przepełnienia. Import jednorazowy: local-scripts/one-off/echo/replace-hero.php.
