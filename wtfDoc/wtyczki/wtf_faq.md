# wtf_faq

- FAQ Elekta po korekcie językowej wszystkich 23 wpisów: literówki, interpunkcja, powtórzenia, akapity i nazewnictwo. Sens zasad, nazwy kart, grupy i głosy zachowane. Dotyczy obu miejsc wyświetlania tego samego shortcode.

- FAQ Elekta w `wtf_www`: 23 pytania, 4 grupy, 37 głosów góra / 2 dół z [oryginału](https://whatthefrog.pl/elekt-faq/). Na [stronie gry](http://localhost:8081/elekt/#faq) i pod zachowanym [adresem FAQ](http://localhost:8081/elekt-faq/). Standalone PL ID 564, powiązany EN ID 565 w szkicu; pytania na razie PL.
- Shortcode Elekta: `[wtf_faq game="elekt" groups="zasady-ogolne,podstawka-karty,dodatek-rubiez,dodatek-wiedzmy-2"]`. Grupa Zasady ogólne wspólna z Tsukuyumi; parametr gry rozdziela pytania.
- Import Elekta: `tools/import-elekt.php /plik-z-html`, osadzenie w istniejącej sekcji i utworzenie adresu FAQ: `wtf-core/setup-elekt-faq.php` — oba przez `wp eval-file ... --allow-root`. Ponowne uruchomienie zachowuje edycje i głosy. Bez pobierania źródła podczas odwiedzin.
- Sprawdzone dla Elekta: wszystkie pytania i liczniki zgodne ze źródłem, brak duplikatów po imporcie, zachowana pozostała treść gry i FAQ Tsukuyumi, dziedziczone złoto, desktop/mobile, głosowanie i edytowalny shortcode.

- Działa w `wtf_web_page` i `wtf_www`.
- Cały kod w `wordpress/wp-content/plugins/wtf_faq/`. Samodzielna wtyczka WP, bez zależności od motywu i WTF Core.
- Panel **WTF FAQ**: pytania, grupy, gry i generator shortcode. Odpowiedzi edytujemy standardowym edytorem WP; obsługują formatowanie, linki, listy i obrazy.
- Lista pytań: rozwijane gry → grupy → pytania, z filtrami gry i grupy. Kliknięcie pytania otwiera edycję.
- „Dodaj pytanie” wewnątrz grupy otwiera formularz z wybraną grupą i grą.
- Jedno pytanie ma jedną grupę i jedną grę. Pole „Kolejność” ustawia kolejność pytań.
- Shortcode wybiera całe grupy, np. `[wtf_faq game="tsukuyumi" groups="tsukuyumi,zasady-ogolne"]`. Kolejność grup wynika z shortcode.
- Kolory i fonty dziedziczone z rodzica; akcent przez `--accent`, obramowanie przez `--line`. Bez kolorów poszczególnych gier w kodzie wtyczki.
- Odpowiedzi domyślnie zwinięte; na próbę można otworzyć kilka naraz.
- Łapki góra/dół i liczniki przy odpowiedzi oraz w panelu. Głosowanie bez logowania; `localStorage` zapamiętuje głos. Serwer nie nalicza drugi raz głosu z tego samego identyfikatora przeglądarki.
- Dane: typ wpisu `wtf_faq`, taksonomie `wtf_faq_group` / `wtf_faq_game`, głosy w tabeli `{prefix}wtf_faq_votes`. Liczniki źródłowe w meta `_wtf_faq_import_up` / `_wtf_faq_import_down`. Przy migracji przenosimy także te dane i media.
- Próbne [FAQ Tsukuyumi](http://localhost:8080/?page_id=188#tsu-faq): 32 pytania, 12 grup, 66 głosów góra i 3 dół z [oryginału](https://whatthefrog.pl/tsukuyumi/faq/). Pięć ilustracji w lokalnych Mediach.
- Przeniesione także na [Tsukuyumi w wtf_www](http://localhost:8081/tsukuyumi/#tsu-faq). Shortcode w edytowalnym bloku strony; FAQ w menu sekcji. EN pozostaje do tłumaczenia.
- Podstrony frakcji PL: FAQ tylko z grupą danej frakcji, przed powrotem do Tsukuyumi. Obecnie 9 z 16 frakcji ma pytania.
- Import ręczny: `wp eval-file wp-content/plugins/wtf_faq/tools/import-tsukuyumi.php /ścieżka/do/faq.html --allow-root`. Powtórzenie zachowuje pytania i głosy; nie pobiera strony podczas odwiedzin.
- Przenoszenie aktualnych danych: `tools/export.php /plik.json`, następnie kopia mediów wskazanych w JSON i `tools/import.php /plik.json` przez WP-CLI. Głosy przenoszone jako liczniki; import nie nadpisuje istniejących pytań. Start dev `wtf_www` aktywuje wtyczkę.
- Sprawdzone w obu projektach: dane i liczniki, panel, filtry, desktop/mobile, kolory i głosowanie. W `wtf_www` także poprawność bloku shortcode w edytorze i ponowny import bez nadpisywania danych. Testy w `preview/verify-faq*` i `preview/verify-target-faq*`.
