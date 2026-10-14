# WTF — wersja docelowa

Lokalny WordPress z motywem blokowym `wtf` oraz wtyczką `wtf-core`. Referencja: `../wtf_web_page`.

## Start

Docker Desktop musi korzystać z kontenerów Linux. Uruchom `deploy/dev/start-dev.bat`. Skrypt tworzy lokalny `.env` z `.env.example`, uruchamia kontenery i inicjalizuje stronę tylko przy pierwszym starcie.

- Witryna: http://localhost:8081/
- Panel: http://localhost:8081/wp-admin/
- Domyślne konto wyłącznie lokalne: `admin` / `admin`.
- Dev nasłuchuje tylko na 127.0.0.1. Nie używać przykładowych haseł na serwerze.

## Edycja

Strony → Strona główna → Edytuj. Sześć sekcji jest osobnymi blokami WTF. Wybranie bloku pokazuje formularz jego tekstów, grafik i linków. Karty i slajdy można dodawać, usuwać i przestawiać. Biblioteka mediów obsługuje grafiki.

Nagłówek i stopka są edytowalnymi częściami szablonu w Wygląd → Edytor. Linki do podstron i sklepu na tym etapie prowadzą do dotychczasowej strony; nie przenosimy jeszcze tych podstron.

Polylang (darmowy) obsługuje połączone strony główne PL/EN. W panelu Strony wybierz odpowiednią wersję; napisy wspólnego nagłówka i stopki tłumacz w Języki → Tłumaczenia ciągów tekstowych. Tłumaczenia początkowe pochodzą z prototypu. Dodatek do WooCommerce nie jest zainstalowany.

Konfiguracje produkcji, sklep i migracja nie są wykonane. Promocja ELEKT10 jest oznaczoną propozycją, nie rzeczywistym kuponem.

## Git i backup

Git obejmuje własny motyw `wtf`, wtyczki `wtf-core` i `wtf_faq`, konfigurację środowisk, skrypty zmian oraz dokumentację. W `media/` wersjonowane są tylko logotypy SVG i faktura heksów wymagane przez motyw.

`.gitignore` pomija rdzeń WordPressa, zewnętrzne wtyczki i motywy, bibliotekę mediów, `.env`, klucze, backupy, zależności i pliki diagnostyczne. `.env.example` pozostaje w Git i może zawierać wyłącznie przykładowe dane.

Commit nie zapisuje aktualnej bazy ani mediów. Do pełnego punktu powrotu potrzebny jest osobny backup bazy, `media/` i `wordpress/wp-content/uploads/` wykonany w tym samym czasie.
