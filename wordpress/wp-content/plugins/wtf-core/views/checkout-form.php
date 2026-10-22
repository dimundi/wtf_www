<?php
// WooCommerce form-checkout.php 9.4.0: same processing, grouped customer interface.
defined('ABSPATH') || exit;
do_action('woocommerce_before_checkout_form',$checkout);
$physical=WC()->cart->needs_shipping();
$invoice=wtf_checkout_invoice();
$billing=$checkout->get_checkout_fields('billing');
$shipping=$checkout->get_checkout_fields('shipping');
$field=function ($key,$fields) use ($checkout) {
    if (isset($fields[$key])) woocommerce_form_field($key,$fields[$key],$checkout->get_value($key));
};
?>
<p class="wtf-checkout-lead">Jeszcze chwila i gotowe.</p>
<?php if (!is_user_logged_in()): ?>
<details class="wtf-checkout-login"><summary><span>Masz konto?</span><span class="wtf-checkout-login-action">Zaloguj się</span></summary>
<?php woocommerce_login_form(['message'=>'Zaloguj się, aby skorzystać z zapisanych danych.','redirect'=>wc_get_checkout_url(),'hidden'=>false]); ?>
</details>
<?php endif; ?>
<form name="checkout" method="post" class="checkout woocommerce-checkout wtf-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="Zamówienie" data-physical="<?php echo $physical ? '1' : '0'; ?>">
<div class="wtf-checkout-main" id="customer_details">
<?php do_action('woocommerce_checkout_before_customer_details'); ?>
<section class="wtf-checkout-section" aria-labelledby="wtf-contact-title">
    <header><span class="wtf-checkout-number">01</span><h2 id="wtf-contact-title">Dane kontaktowe</h2></header>
    <p class="wtf-checkout-hint">Na ten adres wyślemy potwierdzenie<?php echo $physical ? ' zamówienia.' : ' i dostęp do zakupionych plików.'; ?></p>
    <div class="wtf-checkout-fields wtf-checkout-contact-fields"><?php $field('billing_email',$billing); $field('billing_phone',$billing); ?></div>
    <?php if (!is_user_logged_in()): ?>
    <div class="wtf-checkout-account">
        <label class="wtf-create-account-label" for="createaccount"><input type="checkbox" name="createaccount" id="createaccount" value="1" aria-controls="wtf-account-passwords" aria-expanded="false"> <span>Zamów i utwórz konto</span></label>
        <?php do_action('woocommerce_before_checkout_registration_form',$checkout); ?>
        <div class="wtf-checkout-fields" id="wtf-account-passwords" hidden>
            <?php foreach ($checkout->get_checkout_fields('account') as $key=>$definition) woocommerce_form_field($key,$definition,''); ?>
        </div>
        <?php do_action('woocommerce_after_checkout_registration_form',$checkout); ?>
    </div>
    <?php endif; ?>
</section>
<section class="wtf-checkout-section" aria-labelledby="wtf-document-title">
    <header><span class="wtf-checkout-number">02</span><h2 id="wtf-document-title">Dokument zakupu</h2></header>
    <fieldset class="wtf-document-choices"><legend class="screen-reader-text">Wybierz dokument zakupu</legend>
        <label><input type="radio" name="wtf_document" value="receipt" <?php checked(!$invoice); ?>><span>Paragon</span></label>
        <label><input type="radio" name="wtf_document" value="invoice" <?php checked($invoice); ?>><span>Faktura VAT</span></label>
    </fieldset>
    <div class="wtf-checkout-invoice" <?php if (!$invoice) echo 'hidden'; ?>>
    <?php do_action('woocommerce_before_checkout_billing_form',$checkout); ?>
    <div class="wtf-checkout-fields woocommerce-billing-fields__field-wrapper">
    <?php foreach ($billing as $key=>$definition) {
        if (in_array($key,['billing_email','billing_phone','billing_first_name','billing_last_name','billing_vat_invoice'],true)) continue;
        $value=$checkout->get_value($key);
        if ($key==='billing_nip' && !$value && is_user_logged_in()) $value=wtf_account_billing_nip(get_current_user_id());
        woocommerce_form_field($key,$definition,$value);
    } ?>
    </div>
    <?php do_action('woocommerce_after_checkout_billing_form',$checkout); ?>
    </div>
</section>
<?php if ($physical): ?>
<section class="wtf-checkout-section" aria-labelledby="wtf-delivery-title">
    <header><span class="wtf-checkout-number">03</span><h2 id="wtf-delivery-title">Dostawa</h2></header>
    <?php wtf_checkout_delivery_options(); ?>
    <div id="ship-to-different-address" hidden><input type="checkbox" id="ship-to-different-address-checkbox" name="ship_to_different_address" value="1" checked></div>
    <div class="wtf-checkout-locker" <?php if (wtf_checkout_shipping_mode()!=='locker') echo 'hidden'; ?>>
        <?php $field('wtf_locker_point',$checkout->get_checkout_fields('order')); $field('wtf_locker_address',$checkout->get_checkout_fields('order')); ?>
        <div class="wtf-locker-selection-row">
            <p class="wtf-selected-locker" id="wtf-selected-locker" role="status" hidden></p>
            <button type="button" class="wtf-locker-map-button" id="wtf-open-locker-map" aria-haspopup="dialog">Wybierz Paczkomat na mapie</button>
        </div>
        <dialog id="wtf-locker-dialog" aria-labelledby="wtf-locker-map-title" data-token="<?php echo esc_attr(get_option(get_option('easypack_api_environment')==='sandbox' ? 'easypack_geowidget_sandbox_token' : 'easypack_geowidget_production_token','')); ?>">
            <header><h2 id="wtf-locker-map-title">Wybierz Paczkomat</h2><button type="button" id="wtf-close-locker-map">Zamknij</button></header>
            <p id="wtf-locker-map-status" role="status">Ładowanie mapy…</p><div id="wtf-locker-map"></div>
        </dialog>
    </div>
    <button type="button" class="wtf-checkout-copy" hidden>Skopiuj z danych rozliczeniowych</button>
    <?php do_action('woocommerce_before_checkout_shipping_form',$checkout); ?>
    <div class="wtf-checkout-fields woocommerce-shipping-fields__field-wrapper">
    <?php foreach ($shipping as $key=>$definition) if (!in_array($key,['shipping_phone','shipping_email'],true)) $field($key,$shipping); ?>
    </div>
    <div class="wtf-delivery-contact-fields">
    <?php foreach (['shipping_phone'=>'billing_phone','shipping_email'=>'billing_email'] as $key=>$source): ?>
        <div class="wtf-delivery-contact-field"><?php $field($key,$shipping); ?><button type="button" class="wtf-copy-contact" data-copy-source="<?php echo esc_attr($source); ?>" data-copy-target="<?php echo esc_attr($key); ?>" aria-label="<?php echo $key==='shipping_phone' ? 'Kopiuj numer telefonu z danych kontaktowych' : 'Kopiuj adres e-mail z danych kontaktowych'; ?>">Kopiuj z kontaktu</button></div>
    <?php endforeach; ?>
    </div>
    <?php do_action('woocommerce_after_checkout_shipping_form',$checkout); ?>
    <details class="wtf-checkout-notes"><summary>Dodaj uwagi do zamówienia</summary>
    <?php do_action('woocommerce_before_order_notes',$checkout); ?>
    <?php foreach ($checkout->get_checkout_fields('order') as $key=>$definition) if (!in_array($key,['wtf_locker_point','wtf_locker_address'],true)) $field($key,$checkout->get_checkout_fields('order')); ?>
    <?php do_action('woocommerce_after_order_notes',$checkout); ?>
    </details>
</section>
<?php endif; ?>
<?php do_action('woocommerce_checkout_after_customer_details'); ?>
<section class="wtf-checkout-section" aria-labelledby="wtf-payment-title">
    <header><span class="wtf-checkout-number"><?php echo $physical ? '04' : '03'; ?></span><h2 id="wtf-payment-title">Płatność</h2></header>
    <?php woocommerce_checkout_payment(); ?>
</section>
</div>
<aside class="wtf-checkout-summary" aria-labelledby="order_review_heading">
    <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
    <div class="wtf-checkout-summary-heading"><h2 id="order_review_heading">Twoje zamówienie</h2><a href="<?php echo esc_url(wc_get_cart_url()); ?>">Edytuj koszyk</a></div>
    <?php do_action('woocommerce_checkout_before_order_review'); ?>
    <div id="order_review" class="woocommerce-checkout-review-order"><?php woocommerce_order_review(); ?></div>
    <?php if (wc_coupons_enabled()): ?>
    <details class="wtf-checkout-coupon"><summary>Mam kod rabatowy</summary><label for="wtf-coupon">Kod rabatowy</label><div><input type="text" id="wtf-coupon" autocomplete="off"><button type="button" class="button" id="wtf-apply-coupon">Zastosuj</button></div><p id="wtf-coupon-result" role="status"></p></details>
    <?php endif; ?>
    <?php wtf_checkout_place_order(); ?>
    <?php do_action('woocommerce_checkout_after_order_review'); ?>
</aside>
</form>
<?php do_action('woocommerce_after_checkout_form',$checkout); ?>
