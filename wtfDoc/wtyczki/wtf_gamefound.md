# WTF Gamefound

Samodzielna wtyczka `wtf_gamefound`. Menu **WTF Gamefound**: kampanie z adresem HTTPS, datą zakończenia oraz osobnymi nagłówkami, treściami i podpisami przycisków podczas zbiórki i po niej. Data w strefie czasowej witryny wskazanej nad formularzem. Pusty podpis ukrywa przycisk.

Osadzanie w standardowym bloku **Krótki kod**: `[wtf_gamefound id="ID_KAMPANII"]`. Wszystkie wystąpienia korzystają z tych samych ustawień. Można utworzyć kilka kampanii. Komunikat jest renderowany przez PHP; JS przełącza treść także na otwartej lub zapisanej w pamięci podręcznej stronie i pokazuje odliczanie. Bez JS stan odpowiada chwili wygenerowania strony. Po edycji ustawień należy wyczyścić ewentualny cache stron.

Style znajdują się we wtyczce: `assets/campaign.css`, ograniczone do `.wtf-gamefound`. Zielona kolorystyka Gamefound i ciemnozielone tło w obu trybach strony; oryginalne logo ze starych materiałów w `assets/gamefound-logo.webp`, bez zmiany proporcji i kolorów. Bez zewnętrznej integracji, śledzenia i połączeń z API Gamefound. Edycja wymaga uprawnień administratora. Usunięcie wtyczki nie usuwa danych kampanii; przed jej wyłączeniem należy usunąć shortcode ze stron.

Lokalne osadzenie na `/klanarchia/download/` i dane pierwszej kampanii tworzy jednorazowy skrypt `local-scripts/one-off/klanarchia/setup-gamefound.php`. Ponowienie zachowuje edycje kampanii. Proponowane treści po zakończeniu można zmienić w panelu. Termin przeniesiony ze starej strony: 30.10.2026, 00:00 czasu polskiego.

Główna: ta sama kampania 3218 pod slideshow, osadzona w grupie home-gamefound-mobile. Lokalnie na desktopie od 701 px ma poziomy pasek na szerokość kontenera, z odstępem 32 px od slideshow: logo, tekst z odliczaniem i przycisk. Mobilny box pozostaje bez zmian. Sprawdzone w Chrome przy 320/390/700/768/1024/1440/1920 px. Zmiana desktopowa wdrożona na whathefrog.pl na wyraźne polecenie użytkownika; zgodność opening.css potwierdzona SHA-256.
Desktopowy pasek głównej ma lokalnie gradient pionowy w kolorach oryginalnego boxu oraz delikatne rozświetlenie przy lewym górnym rogu; cieniowanie pozostaje widoczne przy szerokim, niskim układzie. Mobilny box i Klanarchia/Pobierz bez zmian. Wdrożone na whathefrog.pl na polecenie użytkownika.
