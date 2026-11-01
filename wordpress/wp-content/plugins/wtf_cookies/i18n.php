<?php
if(!defined('ABSPATH'))exit;
function wtf_cookies_text($text){
    if(!function_exists('pll_current_language')||pll_current_language('slug')!=='en')return $text;
    $strings=[
        'Twoja prywatność'=>'Your privacy',
        'Niezbędne cookies obsługują koszyk i logowanie. Ty decydujesz, czy zezwolić na statystyki Google Analytics, reklamy Meta i filmy YouTube. Odmowa nie blokuje zakupów.'=>'Essential cookies support the cart and login. You choose whether to allow Google Analytics, Meta advertising and YouTube videos. Declining does not prevent purchases.',
        'Polityka cookies'=>'Cookie policy (PL)',
        'Akceptuję wszystkie'=>'Accept all',
        'Tylko niezbędne'=>'Essential only',
        'Ustawienia'=>'Settings',
        'Ustawienia cookies'=>'Cookie settings',
        'Nie udało się zapamiętać wyboru. Ustawienia obowiązują do opuszczenia strony.'=>'Your choice could not be saved. These settings apply until you leave this page.',
        'Zgody są dobrowolne. Możesz je zmienić w każdej chwili przez „Ustawienia cookies” w stopce.'=>'Consent is optional. You can change it at any time using “Cookie settings” in the footer.',
        'Niezbędne — zawsze aktywne'=>'Essential — always active',
        'Koszyk, logowanie, bezpieczeństwo i zapamiętanie tego wyboru.'=>'Cart, login, security and remembering this choice.',
        'Filmy YouTube'=>'YouTube videos',
        'Ten film pochodzi z YouTube. Odtwarzacz wczytamy dopiero po Twojej zgodzie na połączenie z Google/YouTube i jego technologie zapisu informacji.'=>'This video is hosted on YouTube. The player will load only after you allow a connection to Google/YouTube and its storage technologies.',
        'Zezwól na YouTube'=>'Allow YouTube',
        'Otwórz film w YouTube'=>'Watch the video on YouTube',
        'Zgoda na wczytanie odtwarzacza Google/YouTube, który otrzyma dane o urządzeniu i może stosować własne technologie zapisu informacji.'=>'Allow the Google/YouTube player to load. It receives device information and may use its own storage technologies.',
        'Statystyki Google Analytics'=>'Google Analytics statistics',
        'Pomiar odwiedzin, źródeł ruchu i korzystania z quizu. Pomaga nam rozwijać stronę. Bez reklam i remarketingu.'=>'Measure visits, traffic sources and quiz use to help improve this website. No advertising or remarketing.',
        'Reklamy Meta'=>'Meta advertising',
        'Pomiar skuteczności reklam na Facebooku i Instagramie oraz ponowne docieranie z reklamą do odwiedzających. Meta otrzymuje informacje o odwiedzanych stronach, oglądanych produktach i urządzeniu.'=>'Measure Facebook and Instagram ad performance and show ads to previous visitors. Meta receives information about visited pages, viewed products and your device.',
        'Zapisz wybór'=>'Save choices',
        'Zamknij'=>'Close',
        'Po wycofaniu zgody zatrzymamy odpowiedni pomiar lub wczytywanie materiałów. Informacje wcześniej zapisane przez zewnętrznego dostawcę możesz usunąć w ustawieniach przeglądarki.'=>'Withdrawing consent stops the relevant tracking or media loading. You can remove information already stored by external providers in your browser settings.',
        'Opcjonalne materiały są wyłączone bez JavaScript. Koszyk i logowanie korzystają z niezbędnych cookies.'=>'Optional media are disabled without JavaScript. The cart and login use essential cookies.',
    ];
    return $strings[$text]??$text;
}
function wtf_cookies_translate_markup($html){
    // Translate text nodes only; attributes, links and consent controls stay intact.
    return preg_replace_callback('~>([^<>]+)<~u',function($match){$text=trim($match[1]);$translated=wtf_cookies_text($text);return $translated===$text?$match[0]:'>'.esc_html($translated).'<';},$html);
}
