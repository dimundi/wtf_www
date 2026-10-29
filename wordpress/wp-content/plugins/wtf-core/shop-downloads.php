<?php
if (!defined('ABSPATH')) exit;

add_filter('woocommerce_locate_template', static function ($template, $name) {
    return $name === 'myaccount/downloads.php' ? __DIR__.'/views/account-downloads.php' : $template;
}, 20, 2);

function wtf_download_groups($downloads) {
    $groups = [];
    foreach ($downloads as $download) {
        $key = absint($download['product_id']).':'.absint($download['order_id']);
        $groups[$key][] = $download;
    }
    return $groups;
}

function wtf_download_is_mp3($product_id, $download_id) {
    $product = wc_get_product($product_id);
    $file = $product ? ($product->get_downloads()[$download_id] ?? null) : null;
    return $file && $file->get_enabled() && strtolower(pathinfo((string)wp_parse_url($file->get_file(), PHP_URL_PATH), PATHINFO_EXTENSION)) === 'mp3';
}

// Playback uses WooCommerce's signed download URL and its normal permission/limit checks.
// Force delivery supports byte ranges without exposing the underlying file URL.
function wtf_is_download_audio_request() {
    return isset($_GET['wtf_audio'], $_GET['download_file'], $_GET['key']) &&
        is_scalar($_GET['key']) && is_scalar($_GET['download_file']) &&
        wtf_download_is_mp3(absint($_GET['download_file']), sanitize_text_field(wp_unslash($_GET['key'])));
}
add_filter('option_woocommerce_downloads_deliver_inline', static function ($value) {
    return wtf_is_download_audio_request() ? 'yes' : $value;
});
add_filter('woocommerce_file_download_method', static function ($method) {
    return wtf_is_download_audio_request() ? 'force' : $method;
});

function wtf_download_bundle_url($product_id, $order_id) {
    return wp_nonce_url(add_query_arg([
        'action'=>'wtf_download_bundle', 'product_id'=>$product_id, 'order_id'=>$order_id,
    ], admin_url('admin-post.php')), 'wtf_download_bundle_'.$product_id.'_'.$order_id);
}

// Only local purchase files may enter an archive; never fetch a supplied URL.
function wtf_download_local_file($file) {
    $parsed = WC_Download_Handler::parse_file_path($file);
    $path = !$parsed['remote_file'] ? realpath($parsed['file_path']) : false;
    $uploads = wp_upload_dir();
    foreach ([$uploads['basedir'], ABSPATH.'download'] as $root) {
        $root = realpath($root);
        if ($path && $root && str_starts_with(wp_normalize_path($path), trailingslashit(wp_normalize_path($root))) && is_file($path) && is_readable($path)) return $path;
    }
    throw new RuntimeException('Nie można przygotować jednego z plików. Skontaktuj się ze sklepem.');
}

function wtf_download_bundle_permissions($user_id, $product_id, $order_id) {
    if (!$user_id || !$product_id || !$order_id) throw new RuntimeException('Nie masz dostępu do tych plików.');
    $order = wc_get_order($order_id);
    $product = wc_get_product($product_id);
    if (!$order || (int)$order->get_customer_id() !== (int)$user_id || !$order->is_download_permitted() || !$product) {
        throw new RuntimeException('Nie masz dostępu do tych plików.');
    }
    $permissions = WC_Data_Store::load('customer-download')->get_downloads([
        'user_id'=>$user_id, 'product_id'=>$product_id, 'order_id'=>$order_id,
    ]);
    $files = $product->get_downloads();
    $available = [];
    foreach ($permissions as $permission) {
        $id = $permission->get_download_id();
        $expires = $permission->get_access_expires();
        if (!isset($files[$id]) || !$files[$id]->get_enabled() || isset($available[$id])) continue;
        if ($expires && $expires->getTimestamp() < strtotime('midnight')) continue;
        if ($permission->get_downloads_remaining() !== '' && (int)$permission->get_downloads_remaining() <= 0) continue;
        $available[$id] = $permission;
    }
    if (!$available) throw new RuntimeException('Brak aktywnych plików do pobrania.');
    return array_values($available);
}

// Returns a private temporary file. The caller must delete it, including after interruption.
function wtf_create_download_bundle($product_id, $permissions) {
    if (!class_exists('ZipArchive')) throw new RuntimeException('Pobieranie paczki jest chwilowo niedostępne.');
    if (!$permissions || count($permissions) > 100) throw new RuntimeException('Nie można przygotować tej paczki.');
    $product = wc_get_product($product_id);
    $entries = []; $total = 0;
    foreach ($permissions as $permission) {
        $download_id = $permission->get_download_id();
        $file = $product->get_downloads()[$download_id] ?? null;
        if (!$file) throw new RuntimeException('Plik jest niedostępny.');
        $path = wtf_download_local_file($product->get_file_download_path($download_id));
        $total += filesize($path);
        if ($total > 1024 * 1024 * 1024) throw new RuntimeException('Ta paczka jest zbyt duża. Pobierz pliki pojedynczo.');
        $name = sanitize_file_name($file->get_name()) ?: 'plik';
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension && strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== strtolower($extension)) $name .= '.'.$extension;
        $entries[] = [$path, sprintf('%02d-', count($entries)+1).$name];
    }
    $temp = tempnam(sys_get_temp_dir(), 'wtf-download-');
    if (!$temp) throw new RuntimeException('Nie można przygotować paczki. Spróbuj ponownie.');
    chmod($temp, 0600);
    register_shutdown_function(static function () use ($temp) { if (is_file($temp)) unlink($temp); });
    $zip = new ZipArchive(); $opened = false;
    try {
        if ($zip->open($temp, ZipArchive::OVERWRITE) !== true) throw new RuntimeException('Nie można utworzyć paczki.');
        $opened = true;
        foreach ($entries as [$path, $name]) {
            if (!$zip->addFile($path, $name)) throw new RuntimeException('Nie można dodać pliku do paczki.');
            $zip->setCompressionName($name, ZipArchive::CM_STORE);
        }
        $closed = $zip->close(); $opened = false;
        if (!$closed) throw new RuntimeException('Nie można zapisać paczki.');
        return $temp;
    } catch (Throwable $error) {
        if ($opened) $zip->close();
        @unlink($temp);
        throw $error;
    }
}

add_action('admin_post_nopriv_wtf_download_bundle', static function () {
    wp_die('Zaloguj się, aby pobrać swoje pliki.', '', ['response'=>403]);
});

add_action('admin_post_wtf_download_bundle', static function () {
    $product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;
    $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
    check_admin_referer('wtf_download_bundle_'.$product_id.'_'.$order_id);
    if (isset($_SERVER['HTTP_RANGE'])) wp_die('Pobierz paczkę ponownie w całości.', '', ['response'=>416]);
    global $wpdb;
    $temp = null; $transaction = false;
    try {
        $user_id = get_current_user_id();
        // Lock the permissions until the complete archive is ready and counters have been recorded.
        $wpdb->query('START TRANSACTION'); $transaction = true;
        $table = $wpdb->prefix.'woocommerce_downloadable_product_permissions';
        if ($wpdb->query($wpdb->prepare("SELECT permission_id FROM $table WHERE user_id=%d AND product_id=%d AND order_id=%d ORDER BY permission_id FOR UPDATE", $user_id, $product_id, $order_id)) === false) throw new RuntimeException('Spróbuj ponownie za chwilę.');
        $permissions = wtf_download_bundle_permissions($user_id, $product_id, $order_id);
        wc_set_time_limit(120);
        $temp = wtf_create_download_bundle($product_id, $permissions);
        foreach ($permissions as $permission) {
            do_action('woocommerce_download_product', $permission->get_user_email(), $permission->get_order_key(), $product_id, $user_id, $permission->get_download_id(), $order_id);
            $permission->track_download($user_id, WC_Geolocation::get_ip_address());
        }
        if ($wpdb->query('COMMIT') === false) throw new RuntimeException('Nie zapisano pobrania. Spróbuj ponownie.');
        $transaction = false;
    } catch (Throwable $error) {
        if ($transaction) $wpdb->query('ROLLBACK');
        if ($temp && is_file($temp)) unlink($temp);
        wc_get_logger()->error('Nie przygotowano paczki pobrań.', ['source'=>'wtf-downloads', 'product_id'=>$product_id, 'order_id'=>$order_id]);
        wp_die(esc_html($error instanceof RuntimeException ? $error->getMessage() : 'Nie udało się przygotować paczki. Spróbuj ponownie.'), '', ['response'=>403]);
    }
    $filename = sanitize_file_name(get_the_title($product_id)) ?: 'pliki';
    while (ob_get_level()) ob_end_clean();
    wc_nocache_headers();
    header('X-Robots-Tag: noindex, nofollow');
    header('Content-Type: application/zip');
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: attachment; filename="'.$filename.'.zip"');
    header('Content-Length: '.filesize($temp));
    WC_Download_Handler::readfile_chunked($temp);
    unlink($temp);
    exit;
});
