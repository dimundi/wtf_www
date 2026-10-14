# Strony PL/EN

- Lista w `wtf_www` ma rozwijane grupy: Strony ogólne, Elekt, Kurzołaki, Tsukuyumi, Klanarchia, Publikacje. Jeden wiersz nadal łączy PL/EN; główna strona gry jest pierwsza w grupie.
- Filtr grupy i wyszukiwanie po tytułach obu języków. Wyniki wyszukiwania/filtra rozwijają odpowiednie grupy.
- Grupa automatyczna z metadanych gry, rodzica lub slugu. W edytorze pole „Grupa w Strony PL/EN” pozwala wybrać ją ręcznie; zmiana synchronizuje wersje, do których użytkownik ma dostęp. „Automatycznie” usuwa ręczne przypisanie.
- „Dodaj stronę” wewnątrz grupy wstępnie wybiera tę grupę. Przypisanie nie zmienia adresu ani strony nadrzędnej. Standardowe menu Strony pozostaje dostępne.
- Kod we własnej wtyczce WTF Core: `admin.php`, `pages-admin.php`, `pages-admin.css`. Ręczny wybór w meta `_wtf_page_family`; bez zmian w WordPressie i Polylang.
- Sprawdzone: 29 par/stron, poprawne przypisania, filtry, szukanie po EN, linki edycji PL/EN, wstępny wybór grupy, synchronizacja pary, dziedziczenie i kontrola nonce/uprawnień. Szkice testowe usunięte.
