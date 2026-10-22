# wtf_quiz

Samodzielna wtyczka quizów osobowościowych. Nazwa i katalog małymi literami, jak `wtf_faq`; menu panelu: **WTF Quiz**. Nie wymaga HD Quiz ani płatnych dodatków.

## Obsługa

- **WTF Quiz → Quizy**: lista quizów, liczba pytań, shortcode i dodawanie pytania w kontekście quizu.
- Edycja quizu ma zakładki **Pytania**, **Wyniki / archetypy**, **Ustawienia**, podobnie do przebiegu pracy w HD Quiz. W pierwszej otwierasz pytania; w wynikach edytujesz nazwy, opisy, grafiki i adresy podstron; w ustawieniach zmieniasz zaproszenie.
- **Pytania**: filtrowanie według quizu, tytuł pytania, opcjonalna ilustracja z Mediów, kolejność, dowolna liczba odpowiedzi. Przy odpowiedzi zaznaczasz archetypy otrzymujące po +1 punkcie. Wymagane minimum dwie odpowiedzi, każda z przypisaniem.
- Wyniki mają trwałe identyfikatory: zmiana nazwy nie zmienia przypisań. Po usunięciu wyniku sprawdź powiązane pytania. Lista sygnalizuje niekompletne odpowiedzi; niekompletny quiz nie jest udostępniany odwiedzającym.
- Publikacja quizu i pytań standardowym przyciskiem WordPressa. Edycja tylko dla użytkowników z `manage_options`, z kontrolą nonce i sanitacją danych.
- Osadzanie: `[wtf_quiz id="3144"]`. Dane przechowywane w typach wpisów `wtfq_quiz`, `wtfq_question` i ich metadanych.

## Działanie

Jedno pytanie naraz, jedna odpowiedź na pytanie, przyciski poprzednie/dalej, wymagane wszystkie odpowiedzi. Cofanie zachowuje wybór. Suma punktów wyznacza wynik; przy remisie **losujemy wyłącznie spośród wyników z najwyższą sumą**.

Po zakończeniu quiz otwiera skonfigurowaną stronę archetypu z parametrem `?wynik=quiz`. To ten sam adres i pełny opis, co przy wejściu z głównej gry. Parametr włącza podpis „Twój archetyp”, przycisk „Udostępnij swój wynik” (Facebook) i „Rozwiąż ponownie”. Zwykłe wejście nie pokazuje tych elementów. Link udostępnienia zawiera czysty adres strony, bez parametru.

Strony archetypów używają `[wtf_quiz_outcome quiz="3144" outcome="rzecznik" quiz_url="/quiz-klanarchia/"]`. Opis i ilustracja mają **jedno źródło** w WTF Quiz → Wyniki / archetypy; zmiana tam aktualizuje także podstronę. Pierwsza ilustracja z opisu służy jako osobna ilustracja wyniku; pełny tekst zachowany, stare linki udostępnienia zastąpione warunkowym przyciskiem. W Klanarchii CSS pokazuje portret z lewej części źródłowej karty, bez zmiany pliku grafiki. Quizy bez lokalnego adresu wyniku zachowują osadzony widok wyniku.

Wszystkie dziewięć opisów po korekcie językowej: literówki, interpunkcja, składnia, zapis zaimków i niezręczne sformułowania poprawione bez zmiany nazw świata i sensu. Korekta pozostałych ośmiu opisów nie zmienia wcześniej poprawionego Inkwizytora. Ilustracje, linki i identyfikatory zachowane. Kopie sprzed korekty w `local-scripts/one-off/klanarchia/before-inkwizytor-proofread.json` oraz `before-other-archetypes-proofread.json`.

Quiz i wszystkie strony archetypów mają wspólny nagłówek podstron, renderowany w szablonie `klanarchia` przez blok `wtf/klan-subpage-header` (WTF Core `includes/klanarchia-subpage-header.php`, osobny CSS motywu). Ramka mieści powrót z logo i materiały, kontekst strony znajduje się pod nią po prawej. Na telefonie powrót pokazuje samo logo. Strona główna gry nie pokazuje powrotu do samej siebie. Nowe podstrony z tym szablonem automatycznie otrzymują ten sam komponent; nie dodawaj osobnych linków powrotu w ich treści.

Punktacja działa w przeglądarce. Bez konta, zapisu odpowiedzi, statystyk, wysyłki e-mail i śledzenia uczestników. JavaScript wymagany; bez niego widoczny komunikat. Wtyczka obsługuje wiele instancji shortcode bez wspólnych identyfikatorów formularzy. Style i skrypty ładowane przy quizie; style również na oznaczonych stronach archetypów. Wygląd Klanarchii w trybie jasnym i ciemnym.

## Przeniesiona Klanarchia

- Quiz ID **3144**, strona PL ID **3155**, `/quiz-klanarchia/`.
- 10 pytań, 40 odpowiedzi i 9 wyników z HD Quiz ID 123. Treści i przypisania zachowane; kolejność pytań jak w źródle. Zmienione wyłącznie rozstrzyganie remisów.
- Dziewięć stron PL pod dotychczasowymi adresami `/archetyp-egzorcysta/`, `/archetyp-inkwizytor/`, `/archetyp-kronikarz/`, `/archetyp-rzecznik/`, `/archetyp-siepacz/`, `/archetyp-szabrownik/`, `/archetyp-wiedzmiarz/`, `/archetyp-zarmistrz/`, `/archetyp-zerca/`. Opisy i grafiki pochodzą z wyników źródłowego quizu. Linki udostępnienia prowadzą do odpowiadających stron na nowym środowisku.
- 19 ilustracji zarejestrowanych w Mediach, grupa Klanarchia. Pytania korzystają z `srcset`; pliki źródłowe zachowane.
- Link w sekcji quizu na `/klanarchia/` prowadzi już na 8081. EN nie publikowano ani nie tworzono. Stary quiz na 8082 bez zmian.
- Eksport/import tylko ręcznie: `local-scripts/one-off/klanarchia/export-hd-quiz.php`, `import-quiz.php` i `hd-quiz-export.json` — poza Gitem. Mapowanie w opcji `wtfq_klanarchia_import_map`; ponowny import zachowuje istniejące edycje. Nie usuwać mapowania bez odpowiadających danych. Start dev tylko aktywuje wtyczkę, bez importu.

## Weryfikacja

- `node wordpress/wp-content/plugins/wtf_quiz/tests/scoring.cjs`: sumowanie, kilka archetypów na odpowiedź, wybór zwycięzcy, remis i brak odpowiedzi.
- Przejście pełnego quizu zestawami odpowiedzi dającymi każdy z dziewięciu archetypów, porównanie z punktacją źródła.
- Wymagane odpowiedzi, cofanie i restart; ilustracje oraz brak poziomego przepełnienia przy 1440 i 390 px, oba motywy.
- Wszystkie dziewięć stron archetypów: działające ilustracje, lokalne linki i powrót do quizu. Panel: odczyt/zapis i przywrócenie zaproszenia, zakładki, cztery odpowiedzi i dziewięć wyborów punktacji przy każdym pytaniu.
- Panel: zapis/odczyt/przywrócenie treści odpowiedzi i przypisania archetypu. Ponowny import: te same ID, brak duplikatów, zachowane treści i edycje. Porównano wszystkie 40 odpowiedzi oraz przypisania ze źródłowym HD Quiz.
- Identyczny HTML przycisku powrotu na quizie oraz dziewięciu stronach archetypów; brak przycisku na głównej gry.
- Po połączeniu widoków: pełne rozwiązanie quizu przekierowuje na archetyp z `?wynik=quiz`; bezpośrednie wejście pokazuje identyczny opis bez przycisków wyniku. Udostępniany URL bez parametru; restart wraca do pytania 1. Sprawdzone oba motywy przy 1440 i 390 px oraz mapowanie dziewięciu adresów.
