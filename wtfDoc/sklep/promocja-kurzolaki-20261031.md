# Promocja Kurzołaków — PROD 31.10.2026

Podstawka (806): 180 → **150 zł**. Zestaw (PROD 4130, lokalnie 4205): **Kurzołaki + Sweet Revenge + Plamy**, 285 → **220 zł**. Plamy są częścią zestawu, bez oznaczenia gratis; ich grafika widnieje obok pudełek ze znakiem „+”. Ceny bez daty zakończenia.

Kupon **KURZOLAKI** (PROD 4140) zapewnia darmową dostawę w Polsce przy zakupie podstawki lub zestawu, również z innymi produktami w koszyku. Standardowy kupon WooCommerce: rabat 0 zł, free_shipping, produkty 806/4130, dozwolone ceny promocyjne, bez terminu ważności i limitów. Sam dodatek lub plamy nie kwalifikują.

Strefa Polska: `free_shipping:3` Paczkomat i `free_shipping:4` Kurier, wymagany kupon. Mapa `wtf_checkout_shipping_modes` zachowuje formularze dostawy. `woocommerce_shipping_hide_rates_when_free=yes` ukrywa płatne opcje po użyciu kuponu; bez niego pozostają zwykłe stawki.

Ramka z kodem na stronie Kurzołaków (566), przy obu przyciskach zakupu. Na produktach podstawki i zestawu ramka jest **bezpośrednio pod ceną, przed dodaniem do koszyka**, bez powielenia w opisie. Edycja: Dane produktu → Ogólne → Informacja pod ceną (`_wtf_product_purchase_notice`).

Na wyraźne polecenie użytkownika ręcznie ustawiono kwotę odniesienia zestawu 285 zł (`manual_reference=true`). Poprzednia kwota na PROD wynosiła 250 zł; zachowano stan seed oraz wcześniejszą historię, bez przepisywania zdarzeń cenowych. Kopia umożliwia odtworzenie poprzedniego stanu.

Sprawdzono na PROD: ceny, nazwa i grafika zestawu bez gratisu, ramki przy cenach na telefonie, dwa komunikaty na stronie gry. Test koszyka bez zamówień: kupon odrzucony dla samego dodatku/plam, przyjęty dla podstawki, zestawu i koszyka mieszanego; obie dostawy 0 zł, usunięcie kuponu przywraca koszt. Minigra nadal 404.

Wdrożenie punktowe: `shop.php`, `shop-product.php` i same nowe reguły CSS w `product.css`/`kurzolaki.css`, bez zastępowania bazy. Kopie i potwierdzenie: `backups/shop-promo-release/20261031/`; skrypty poza Gitem: `local-scripts/one-off/meta-pixel/`. Minigra wykluczona z paczki.