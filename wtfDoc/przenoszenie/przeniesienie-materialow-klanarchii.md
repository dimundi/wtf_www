# Materiały do Klanarchii

Podstrona WordPressa `/klanarchia/download/`, PL 3213, dziecko 3119, w bieżącym layoucie Klanarchii. Standardowe bloki dokumentów, tapet, kontaktu i promocji Elekta. EN nie tworzono. Układ i interakcje: motyw `assets/klanarchia-downloads.css/js`.

Komunikat zbiórki: [wtf_gamefound](../wtyczki/wtf_gamefound.md), kampania 3218. Osadzanie na dowolnej stronie przez blok Krótki kod: `[wtf_gamefound id="3218"]`. Wspólne ustawienia i treści w menu WTF Gamefound. Style we wtyczce.

Statycznie pozostają tylko PDF-y i dwie tapety, z oryginalnymi URL pod `/download/Klanarchia/`, w tym `assets/` dla tapet. Podglądy w Mediach, grupa Klanarchia. Nie przenosimy starej strony HTML ani `kpostaci`. Błędnie skopiowane pliki odłożono poza publiczny katalog do `local-scripts/one-off/klanarchia/excluded-static/`. Źródło 8082 bez zmian.

Przekierowanie starej strony oraz obsługa wariantu `/download/klanarchia/` pozostają do wdrożenia w proxy: [lista przekierowań](../srodowiska/lista-przekierowan.md). Nie przekierowywać prefiksu plików. Pliki poza Git dostarczyć osobno, nie nadpisując nowszych produkcyjnych.

Narzędzia jednorazowe: `local-scripts/one-off/klanarchia/`. Nie uruchamiać ponownie starego `copy-downloads.ps1` — kopiował za szeroki zakres. Mapowanie strony i podglądów: `wtf_klan_downloads_import_map`. Setup Gamefound zachowuje edycje kampanii.

Sprawdzono składnię PHP/JS, osadzenie, oba stany kampanii i nieprawidłowe ID; test przywrócił termin. Wcześniejsza walidacja układu: 1440/768/390/320 px w obu motywach, bez przepełnienia. PDF-y: SHA-256 zgodne ze źródłem, pobieranie fragmentu 206.
Tapety są pierwszą sekcją materiałów. Promocja Elekta odtwarza układ oryginału: opis obok pudełka, wspólny kupon z kwotą 17 zł i kopiowaniem oraz dwa przyciski. Usunięto nagłówek aktywnej kampanii nad treścią Gamefound. Sprawdzono 1440/390/320 px bez przepełnienia, kopiowanie kuponu i poprawność bloków w edytorze.
