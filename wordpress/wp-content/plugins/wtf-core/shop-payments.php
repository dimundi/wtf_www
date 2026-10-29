<?php
if (!defined('ABSPATH')) exit;

// Paragon i odbiór w Paczkomacie nie wymagają adresu do faktury.
// Paynow wymaga pominięcia pustych pól opcjonalnych, zamiast przesyłania pustych adresów.
function wtf_paynow_clean_buyer($data) {
    foreach (['firstName', 'lastName'] as $key) if (isset($data['buyer'][$key]) && $data['buyer'][$key] === '') unset($data['buyer'][$key]);
    foreach (['billing', 'shipping'] as $key) {
        if (!isset($data['buyer']['address'][$key])) continue;
        $data['buyer']['address'][$key] = array_filter($data['buyer']['address'][$key], static fn($v) => $v !== '' && $v !== null);
        if (!$data['buyer']['address'][$key]) unset($data['buyer']['address'][$key]);
    }
    if (isset($data['buyer']['address']) && !$data['buyer']['address']) unset($data['buyer']['address']);
    return $data;
}

add_action('woocommerce_payment_gateways_initialized', static function ($gateways) {
    if (!class_exists('Paynow_Gateway')) return;
    foreach ($gateways->payment_gateways() as $gateway) {
        if (!$gateway instanceof WC_Gateway_Pay_By_Paynow_PL) continue;
        $client = (new ReflectionProperty(Paynow_Gateway::class, 'client'))->getValue($gateway->gateway);
        if (!$client) continue;
        $client->setHttpClient(new class($client->getHttpClient()) implements \Paynow\HttpClient\HttpClientInterface {
            private $http;
            public function __construct($http) { $this->http = $http; }
            public function post(string $url, array $data, ?string $idempotencyKey = null): \Paynow\HttpClient\ApiResponse {
                return $this->http->post($url, $url === '/v3/payments' ? wtf_paynow_clean_buyer($data) : $data, $idempotencyKey);
            }
            public function patch(string $url, array $data, ?string $idempotencyKey = null): \Paynow\HttpClient\ApiResponse { return $this->http->patch($url, $data, $idempotencyKey); }
            public function get(string $url, ?string $query = null, ?string $idempotencyKey = null): \Paynow\HttpClient\ApiResponse { return $this->http->get($url, $query, $idempotencyKey); }
            public function delete(string $url, string $idempotencyKey, ?string $query = null): \Paynow\HttpClient\ApiResponse { return $this->http->delete($url, $idempotencyKey, $query); }
        });
    }
});

// Po migracji identyfikator starego zamówienia może należeć do innego nowego zamówienia.
function wtf_paynow_notification_order($external_id, $payment_id) {
    if (!is_int($external_id) && !is_string($external_id)) return null;
    if (!ctype_digit((string)$external_id) || !is_string($payment_id) || $payment_id === '') return null;
    $map = get_option('wtf_customer_migration_map', []);
    $mapped = (int)($map['orders'][(int)$external_id] ?? 0);
    if ($mapped) {
        $old = wc_get_order($mapped);
        if ($old && $old->get_payment_method() === 'paynow' &&
            (int)$old->get_meta('_wtf_source_order_id') === (int)$external_id &&
            $old->get_transaction_id() !== '' && hash_equals($old->get_transaction_id(), $payment_id)) {
            return ['order' => $old, 'legacy' => true];
        }
    }
    $order = wc_get_order((int)$external_id);
    if ($order && str_starts_with($order->get_payment_method(), 'pay_by_paynow_pl_') &&
        $order->get_transaction_id() !== '' && hash_equals($order->get_transaction_id(), $payment_id)) {
        return ['order' => $order, 'legacy' => false];
    }
    return null;
}

function wtf_paynow_check_notification($legacy_endpoint = false) {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') wp_send_json(['error' => 'method_not_allowed'], 405);
    $body = trim(file_get_contents('php://input'));
    $settings = get_option('woocommerce_pay_by_paynow_pl_settings', []);
    $key = $settings[($settings['sandbox'] ?? 'no') === 'yes' ? 'sandbox_signature_key' : 'production_signature_key'] ?? '';
    $signature = $_SERVER['HTTP_SIGNATURE'] ?? '';
    if ($key === '' || !is_string($signature) || !hash_equals(base64_encode(hash_hmac('sha256', $body, $key, true)), $signature)) {
        wp_send_json(['error' => 'invalid_signature'], 400);
    }
    $data = json_decode($body, true);
    if (!is_array($data) || !is_string($data['status'] ?? null)) wp_send_json(['error' => 'invalid_notification'], 400);
    $target = wtf_paynow_notification_order($data['externalId'] ?? '', $data['paymentId'] ?? '');
    if (!$target) wp_send_json(['error' => 'payment_order_mismatch'], 400);
    if ($target['legacy']) {
        $order = $target['order'];
        global $wpdb;
        $lock_name = 'wtf-paynow-' . $order->get_id();
        if ((int)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock_name)) !== 1) wp_send_json(['error' => 'retry'], 400);
        try {
            $order = wc_get_order($order->get_id());
            if ($data['status'] === 'CONFIRMED' && !$order->is_paid() && $order->get_status() !== 'refunded') {
                $order->payment_complete($data['paymentId']);
                $order->add_order_note('Paynow: potwierdzono historyczną płatność po migracji.');
            }
        } finally {
            $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock_name));
        }
        wp_send_json(['received' => true], 202);
    }
    if ($legacy_endpoint) {
        try {
            (new WC_Gateway_Pay_By_Paynow_PL_Notification_Handler())->process_notification($data['paymentId'], $data['status'], $target['order']->get_id(), $data['modifiedAt'] ?? '');
        } catch (WC_Pay_By_Paynow_Pl_Notification_Stop_Processing_Exception $e) {
            wp_send_json(['received' => true], 200);
        } catch (Throwable $e) {
            wp_send_json(['error' => 'retry'], 400);
        }
        wp_send_json(['received' => true], 202);
    }
    // Nowe zamówienia obsługuje dalej oficjalna wtyczka, po ponownej weryfikacji podpisu.
}
add_action('woocommerce_api_wc_gateway_pay_by_paynow_pl', 'wtf_paynow_check_notification', -100);
add_action('woocommerce_api_wc_gateway_paynow', static function () { wtf_paynow_check_notification(true); }, -100);
add_action('woocommerce_api_wc_gateway_paynow_notification_handler', static function () { wtf_paynow_check_notification(true); }, -100);
