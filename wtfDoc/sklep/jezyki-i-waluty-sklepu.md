# Języki i waluty sklepu

Ustalenia z rozmowy 2026-10-11. Dokument opisuje plan, nie wdrożoną funkcjonalność. Nie zakupiono ani nie zatwierdzono płatnych wtyczek.

Aktualizacja po wznowieniu implementacji: w `wtf_www` zainstalowano darmowy Polylang dla stron głównych PL/EN. Sklep, waluty i integracja języka WooCommerce nadal nie są wdrożone. Wyniki pierwszego etapu: [[przenoszenie/implementacja-etap-1|implementacja-etap-1]].

## Strony i treści

- Omawiany mechanizm wersji językowych stron to Polylang: połączone strony PL/EN, z możliwością dodania ES. Treści przygotowujemy i zatwierdzamy osobno dla każdego języka; nie zakładamy automatycznego tłumaczenia u odwiedzającego.
- Treści kampanii, reklam i bloków edytujemy w WordPressie. Przygotowany fragment HTML reklamy można wkleić do bloku „Własny HTML” na odpowiedniej wersji strony, osobno dla PL i EN, bez rejestrowania tekstów w kodzie. Fragmenty wymagające JavaScript lub dodatkowego CSS wymagają odpowiedniego sposobu osadzania.
- Stałe napisy motywu można rejestrować w kodzie w Polylang, a ich tłumaczenia wpisywać w panelu. Nie stosujemy tego jako obowiązkowego mechanizmu dla wszystkich dynamicznych treści i aplikacji.
- Moduły React mogą mieć własne tłumaczenia i otrzymywać aktualny język strony z WordPressa; szczegóły w [[ustalenia/wersja-docelowa|wersja-docelowa]].

## Prezentacja produktów

- Strona sklepu należy do naszej witryny i korzysta z naszego motywu, nagłówka i stopki. Dane produktów, ceny i dostępność pochodzą z WooCommerce.
- Wersje sklepu PL i EN mogą wyświetlać różne zestawy produktów. Dobór produktów realizujemy w naszym kodzie; samo wyświetlanie takich zestawów nie wymaga Polylang for WooCommerce.
- W sklepie EN można opcjonalnie dodać „Pokaż również gry w języku polskim”, rozszerzające listę o polskie wydania.
- Język wydania gry jest osobną cechą od języka strony i opisu produktu. Polskie i angielskie pudełko mogą być osobnymi produktami lub wariantami. Szczegółowy model danych i filtrowania pozostaje do implementacji.
- Ustalenie sposobu prezentacji nie rozwiązuje samo w sobie języka koszyka, zamówienia ani maili.

## Waluty niezależne od języka

- Przełącznik PLN/EUR działa niezależnie od PL/EN. Wszystkie cztery kombinacje są dopuszczone.
- Wybrana waluta obowiązuje w cenach produktów, koszyku, zamówieniu i faktycznej płatności; nie jest tylko przelicznikiem do wyświetlania.
- Jeden produkt może mieć cenę bazową PLN oraz cenę EUR wyliczaną z kursu albo wpisaną ręcznie. Nie trzeba tworzyć drugiego produktu wyłącznie dla innej waluty.
- Kurs można ustawiać ręcznie lub pobierać automatycznie, z określoną częstotliwością aktualizacji i zaokrąglaniem cen. Wybór trybu, źródła kursów i reguł cen pozostaje otwarty.
- Rozważana wtyczka: [WPML Multilingual & Multicurrency for WooCommerce](https://wordpress.org/plugins/woocommerce-multilingual/). Jej bezpłatny tryb wielowalutowy może działać bez WPML. To kandydat do sprawdzenia, nie zatwierdzona zależność; należy zweryfikować zgodność z wybranym mechanizmem języków i całym koszykiem.
- Użytkownik oddzielił dyskusję walut od Paynow. Odpowiednią bramkę skonfigurujemy po ustaleniu obsługi walut; teraz nie zmieniamy ani nie wybieramy bramki.
- VAT nie wynika z języka ani wybranej waluty. Konfigurację podatków ustalamy oddzielnie według danych kupującego i właściwych zasad podatkowych.

## Język WooCommerce — nadal do rozwiązania

WooCommerce potrzebuje aktualnego języka m.in. przy komunikacie po dodaniu produktu do koszyka, etykietach koszyka i formularza zamówienia oraz wysyłaniu potwierdzeń. Gotowe pakiety tłumaczeń interfejsu nie zastępują integracji przełączania języka i zachowania języka zamówienia.

Rozważane rozwiązania:

- **Polylang for WooCommerce** to dodatek producenta Polylang łączący Polylang z WooCommerce, a nie wtyczka wydawana przez WooCommerce. Wymaga WooCommerce oraz Polylang (darmowego lub Pro). Obsługuje m.in. powiązania tłumaczeń produktów, synchronizację stanów i język maili. Nie zdecydowano o zakupie.
- **Webis Multilingual for WooCommerce** to rozszerzenie zewnętrznego producenta Web Industrial Solutions, sprzedawane na WooCommerce Marketplace i wskazane w [dokumentacji WooCommerce](https://woocommerce.com/document/woocommerce-localization/). Nie ustalono jego współpracy z Polylang ani nie potwierdzono pełnego zakresu obsługi języka zamówień i maili dla naszej architektury. Nie traktować go jako zatwierdzonego dodatku do Polylang.
- **Loco Translate** służy do edycji tłumaczeń napisów; samo nie rozwiązuje przełączania języka sklepu ani zachowania języka zamówienia.

## Cena i odnowienia Webis

Według [oferty WooCommerce Marketplace](https://woocommerce.com/products/webis-multilingual-for-woocommerce/) sprawdzonej 2026-10-11: 49 USD za rok lub 78,40 USD za dwa lata (oferta z rabatem 20%). Abonament obejmuje aktualizacje i wsparcie; ceny i rabaty mogą się zmienić.

Według [zasad WooCommerce Marketplace](https://woocommerce.com/document/frequently-asked-questions/) wygaśnięcie abonamentu nie wyłącza zainstalowanego rozszerzenia. Kończy dostęp do aktualizacji i wsparcia. Dalsze działanie starej wersji nie gwarantuje zgodności z przyszłymi aktualizacjami WordPressa i WooCommerce.

## Kolejny krok

Sprawdzić integrację języka WooCommerce z ustalonym modelem strony i doborem produktów oraz zgodność wtyczki walutowej. Nie kupować dodatków ani nie przedstawiać rozważanych kandydatów jako wdrożonych rozwiązań.
