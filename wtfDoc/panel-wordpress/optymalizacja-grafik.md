# Automatyczna optymalizacja grafik

W projekcie docelowym `wtf_www` aktywowano darmową wtyczkę **Modern Image Formats** (`webp-uploads`, wersja 2.7.1), rozwijaną przez WordPress Performance Team. Konwersji nie piszemy we własnej wtyczce.

## Obsługa

- Dodawaj PNG lub JPG normalnie przez **Media → Dodaj plik**.
- Wybieraj grafikę z biblioteki w edytorze bloku. Zapisywany jest identyfikator załącznika, który pozwala korzystać z jego wygenerowanych rozmiarów.
- Wtyczka przygotowuje WebP, zachowując oryginał. Jeśli WebP byłby większy, może pozostawić dotychczasowy format — nie wymuszamy cięższego pliku.
- Baner, karty gier, menu gier i promocja korzystają z funkcji obrazów WordPressa, wraz z `srcset` i `sizes`. Przeglądarka wybiera rozmiar odpowiedni do szerokości i gęstości pikseli ekranu.
- Konwersja nie zmienia wymagań dotyczących kompozycji i wymiarów. Banery: zalecane 1920 × 800 px; karty: 900 × 750 px. Wskazówki znajdują się przy polach grafik w edytorze.
- Ustawienia wtyczki znajdują się w **Ustawienia → Media**; wybrany format to WebP (`perflab_modern_image_format=webp`).

## Implementacja i odtwarzanie

`deploy/dev/init-dev.sh` instaluje wtyczkę, aktywuje ją i ustawia WebP. Kod zewnętrznej wtyczki nie jest kopiowany do Git ani modyfikowany. `wtf-core.php` renderuje załączniki przez `wp_get_attachment_image`; obrazy wskazane samym adresem są odszukiwane w lokalnej bibliotece. Przy braku załącznika pozostaje zwykły adres obrazu — taki plik nie ma automatycznej obsługi rozmiarów.

Slider otrzymuje przygotowane przez WordPress adresy WebP, `srcset` i `sizes` również dla kolejnych slajdów. Nie zmienia układu ani treści banera.

Dla już dodanych obrazów wykonano jednorazowo `wp media regenerate --yes --allow-root`. Konwersja dotyczy obrazów rastrowych; logo SVG pozostają plikami wektorowymi. Nowe uploady przetwarzają się automatycznie.

## Weryfikacja — 2026-10-11

Test `preview/verify-image-formats.php` w projekcie referencyjnym dodaje kontrolny PNG przez API Mediów, sprawdza WebP i responsywne renderowanie banera, kart oraz promocji, a następnie usuwa wyłącznie testowy załącznik. Testowy PNG 1920 × 800 px miał 4 616 298 bajtów, a wygenerowany WebP 9 730 bajtów. To prosty obraz kontrolny; wynik nie jest prognozą kompresji ilustracji użytkownika.

Test przeglądarkowy `preview/verify-responsive-images.cjs` sprawdził istniejący baner: przy ekranie 390 px pobierana była wersja WebP o szerokości 768 px, a przy ekranie 1280 px wersja 1536 px. Sprawdzono działanie zmiany slajdu, obecność srcset/sizes dla wszystkich slajdów i kart oraz załadowanie aktywnej grafiki. Strona zwraca HTTP 200. Regeneracja przetworzyła wszystkie osiem grafik rastrowych; trzy SVG zgłosiły brak obsługi regeneracji i nie wymagają WebP.

Źródło: [Modern Image Formats — WordPress.org](https://wordpress.org/plugins/webp-uploads/).
