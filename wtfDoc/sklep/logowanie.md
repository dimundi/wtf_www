# Logowanie

29.10.2026: na polecenie użytkownika wdrożono na PROD przekierowanie 302 zwykłego wejścia GET/HEAD na `/wp-login.php` (także `?action=login`) do strony „Moje konto”. Nie wyłączono panelu administratora. Logowanie kontem administratora przez WooCommerce nadal umożliwia dostęp do `/wp-admin/`.

WordPress zachowuje obsługę POST, wylogowania, resetowania hasła, komunikatu o wysłaniu instrukcji oraz parametru `interim-login`. Parametr `reauth` pozostawia natywny formularz tylko użytkownikowi już zalogowanemu; niezalogowane wejście przez `/wp-admin/`, które dodaje `reauth=1`, prowadzi do „Moje konto”. Docelowy adres z `redirect_to` przechodzi do ukrytego pola formularza WooCommerce po walidacji; adresy innych hostów są odrzucane. Nie jest to dodatkowe zabezpieczenie przed próbami logowania.

Kod: `wtf-core/shop-account.php`. Prywatna kopia PROD: `.wtf-staging/launch-20261028/login-page-20261029/shop-account-before.php`. Wdrożenie sprawdziło sumę bazowego pliku przed podmianą. Test HTTP lokalny obejmuje przekierowanie, pozostawienie działań natywnych, przesyłanie docelowego adresu, odrzucenie obcego hosta oraz POST bez danych. Nie resetowano haseł ani nie testowano cudzych danych logowania.

Po zgłoszeniu użytkownika poprawiono wyjątek `reauth`; pełny test HTTP lokalny i PROD obejmuje również przejście od `/wp-admin/` aż do „Moje konto”, bez pętli, z zachowaniem docelowego panelu. Kopia sprzed poprawki: `shop-account-before-admin-entry.php` w tym samym prywatnym katalogu.
