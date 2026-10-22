<?php
if (!defined('ABSPATH') || !is_user_logged_in()) exit;
$user=wp_get_current_user();
$name=$user->first_name ?: $user->display_name;
$orders=wc_get_orders(['customer_id'=>$user->ID,'limit'=>1,'orderby'=>'date','order'=>'DESC','status'=>array_keys(wc_get_order_statuses())]);
$order=$orders ? reset($orders) : null;
$shortcuts=[
    ['orders','Zamówienia','Sprawdź status i szczegóły zakupów.','<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2M9 10h6M9 15h6"/>'],
    ['downloads','Pliki do pobrania','Pobierz zakupione materiały cyfrowe.','<path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/>'],
    ['edit-account','Dane konta','Zmień swoje dane i hasło.','<circle cx="12" cy="7" r="4"/><path d="M4 22v-3a8 8 0 0 1 16 0v3"/>'],
];
?>
<div class="wtf-account-dashboard">
    <header class="wtf-account-welcome"><h2>Cześć, <?php echo esc_html($name); ?>!</h2><p>Dobrze Cię widzieć. Tutaj znajdziesz wszystko, co dotyczy Twoich zakupów.</p></header>
    <div class="wtf-account-shortcuts">
        <?php foreach ($shortcuts as [$endpoint,$title,$description,$icon]): ?>
        <a class="wtf-account-shortcut" href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><?php echo $icon; ?></svg><h3><?php echo esc_html($title); ?></h3><p><?php echo esc_html($description); ?></p></a>
        <?php endforeach; ?>
    </div>
    <section class="wtf-account-order" aria-labelledby="wtf-account-order-title">
        <h2 id="wtf-account-order-title"><?php echo $order?'Ostatnie zamówienie':'Czas na pierwszą grę?'; ?></h2>
        <?php if ($order): ?>
        <div class="wtf-account-order-summary"><div><a class="wtf-account-order-number" href="<?php echo esc_url($order->get_view_order_url()); ?>">Zamówienie #<?php echo esc_html($order->get_order_number()); ?></a><?php if ($order->get_date_created()): ?><p><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></p><?php endif; ?></div><span class="wtf-account-status"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span><strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong><a class="wtf-account-button" href="<?php echo esc_url($order->get_view_order_url()); ?>">Zobacz zamówienie</a></div>
        <?php else: ?>
        <p>Nie masz jeszcze zamówień. Wybierz grę dla siebie.</p><a class="wtf-account-button" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Przejdź do sklepu</a>
        <?php endif; ?>
    </section>
</div>
<?php
do_action('woocommerce_account_dashboard');
do_action('woocommerce_before_my_account');
do_action('woocommerce_after_my_account');
