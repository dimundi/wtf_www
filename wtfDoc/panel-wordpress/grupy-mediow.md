# Grupy Mediów

- WTF Core w `wtf_www`, taksonomia załączników `wtf_media_group`. Grupy początkowe: Ogólne, Elekt, Kurzołaki, Tsukuyumi, Klanarchia, Echo Światów, Banery, Sklep.
- Jeden plik może mieć wiele grup. Zaznacz je w szczegółach pliku lub podczas wyboru grafiki. Odznaczenie wszystkich usuwa przypisania.
- Filtr „Wszystkie grupy” / wybrana grupa / „Bez grupy” działa w siatce, liście i oknie wyboru grafiki. Lista ma kolumnę Grupy z linkami do filtrowania.
- Media → Grupy mediów: dodawanie, zmiana nazw i usuwanie grup. Uprawnienie zarządzania kategoriami; przypisywanie wymaga możliwości edycji pliku i wgrywania mediów.
- Początkowo przypisano 189 plików według nazw i użycia w treściach. Nierozpoznane pliki trafiły do Ogólnych. Nowym plikom grupy zaznaczamy w panelu. Plik i jego adres pozostają te same.
- Kod: `media-groups.php`, `media-groups.js`, `media-groups.css`. Ręczne początkowe przypisanie: `wp eval-file wp-content/plugins/wtf-core/setup-media-groups.php --allow-root`; ponowienie zachowuje istniejące przypisania. Grupy startowe inicjalizowane raz, nie odtwarzają usuniętych grup.
- Sprawdzone: wiele grup i zapis checkboxów w panelu, siatka/lista/wybór grafiki, klasyczna edycja, usuwanie przypisań, kontrola uprawnień, powtórny import i zachowanie plików/URL. Przypisania testowe przywrócone.
