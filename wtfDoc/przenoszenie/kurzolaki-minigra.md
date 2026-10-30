# Złap Landrynkę — prototyp lokalny

Adres: `/kurzolaki/zlap-landrynke/`. Tylko lokalnie, bez wdrożenia PROD i bez nagród.

Obecny prototyp to pościg na Canvas 2D: sterujesz Kichaczem, zbierasz cukierki i unikasz Pucy. Runda do 45 sekund; kontakt z Pucą kończy grę. Trzy cukierki odnawiają się po zebraniu. Puca przyspiesza wraz z wynikiem i czasem; gracz ma zryw ze schłodzeniem 2,5 s. Strzałki/WASD, spacja i sterowanie dotykowe przeciąganiem po planszy. Pauza ręczna oraz automatyczna po opuszczeniu karty; restart zeruje stan. Ilustracje Kichacza i Pucy z istniejących mediów.

Shortcode `[wtf_kurz_game]`, moduł `wtf-core/kurzolaki-game.php`; CSS/JS w motywie `assets/kurzolaki-game.*`, ładowane tylko na stronie shortcode. Treść strony w WordPressie. Skrypt utworzenia i kontrola przeglądarką w `local-scripts/one-off/kurzolaki-stories/`.

Sprawdzono w Chrome przy 390/1440 px: brak przepełnienia, ruch klawiaturą, zbieranie cukierków, zatrzymanie czasu podczas pauzy, schłodzenie zrywu, zakończenie po złapaniu i zerowanie wyniku przy restarcie. Rzeczywiste zdarzenia dotykowe CDP poruszają postacią. Brak wyjątków JS. Kontrola `check-chase.mjs`, podglądy `chase-390.png` i `chase-1440.png`.
