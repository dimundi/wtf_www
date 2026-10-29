# Kupony — migracja na PROD

29.10.2026 przeniesiono na PROD zakres wskazany przez użytkownika:

- 701 kuponów przypisanych do albumu MP3, produkt źródłowy 7196 → 825.
- `KLANARCHIA`: rabat 17 zł, podstawka Elekta 13255 → 802.
- `kurzolaki-50`: najnowszy kupon na Kurzołaki, rabat 50 zł, produkt 13027 → 806.

Łącznie 703 z 719 kuponów źródłowych. Pozostałe 16 pominięto zgodnie z ograniczonym zakresem. W nowym sklepie przed importem nie było kuponów; narzędzie odrzuca kolizje kodów i nie nadpisuje istniejących. Nie przenoszono dodatkowych metod dostawy ani archiwalnych produktów.

Zachowano kody, statusy, typy i kwoty rabatów, daty, minima/maksima koszyka, ograniczenia produktów i e-maili, zasady łączenia/promocji, limity wykorzystania oraz 145 wpisów historii użyć. Występujące w nich identyfikatory klientów dopasowano do nowego sklepu. Dawnych ID produktów nie użyto bez mapowania. KLANARCHIA i kurzolaki-50 pozostają typem fixed_cart, tak jak w źródle, z wymaganym właściwym produktem w koszyku.

Kontrola przed zatwierdzeniem: porównanie wszystkich właściwości 703 kuponów i 23 199 natywnych sprawdzeń kwalifikacji wobec 33 produktów, zgodnych ze starym sklepem. Próba lokalna wycofana transakcją; następnie selektywna transakcja na PROD z kontrolą przed COMMIT. W rzeczywistym checkoutcie na PROD sprawdzono rabaty 17 zł, 50 zł i jeden aktywny MP3 w oddzielnych koszykach gości. Nie składano zamówień, nie zużywano kuponów ani nie wysyłano maili.

Narzędzia jednorazowe: `local-scripts/one-off/coupons/` oraz eksportery w `local-scripts/one-off/launch-20261028/`. Prywatny eksport i porównanie źródła: `backups/coupons-20261029/`, również `.wtf-staging/coupons-20261029/` na hostingu. Tam znajdują się kopia celu `target-before-apply.json` i wynik `migration-result-private.json`. Pliki zawierają kody i dane wykorzystania — nie publikować.

Mapowanie: opcja `wtf_coupon_migration_map`, metadane `_wtf_source_coupon_id`. Nie usuwać mapy bez odpowiadających jej kuponów. Ponowne apply jest blokowane, aby nie zresetować wykorzystania po starcie. Plik eksportu jest punktem odniesienia; późniejsze użycia na PROD są prawidłowymi zmianami.
