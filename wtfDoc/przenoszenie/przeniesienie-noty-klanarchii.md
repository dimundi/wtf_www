# Nota redaktorska Klanarchii

- Adres zachowany: `/klanarchia-nota/`, PL **3264**, źródłowa strona 8082: **12510**. EN nie tworzono, przekierowanie nie jest potrzebne.
- Dziewięć akapitów tekstu i podpis Krzysztofa Głośnickiego, redaktora naczelnego, zachowane bez korekty autorskiej. Nagłówek prezentacyjny: „Nota redaktorska”. Źródłowy tytuł: „Nota edytorska odnośnie drugiego wydania Klanarchii”.
- Ilustracja ze źródła zarejestrowana w Mediach jako **3263**, grupa Klanarchia. Zalecana wymiana pionową grafiką w proporcjach źródła, około 1:2; obraz wyświetla się w całości, bez kadrowania.
- Natywne bloki WordPressa: teksty, podpis, ilustracja i logo z powrotem do gry. Wspólne menu, gradientowe tło i stały ciemny motyw Klanarchii. Style w `assets/klanarchia-note.css`.
- Na głównej sekcja „Historia drugiej edycji” ma zajawkę „Jak powstawała druga edycja Klanarchii? Przeczytaj notę redaktorską o pracy nad nowym wydaniem gry.” i prowadzi lokalnie do noty.
- Import ręczny poza Git: `local-scripts/one-off/klanarchia/export-note.php`, `klanarchia-note.json`, `import-note.php`. Ponowienie nie nadpisuje treści istniejącej noty. Identyfikacja: `_wtf_klan_note`, `_wtf_klan_source_id`; szablon `klanarchia`.
