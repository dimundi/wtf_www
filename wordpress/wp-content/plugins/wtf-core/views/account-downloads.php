<?php
if (!defined('ABSPATH')) exit;
$downloads = WC()->customer->get_downloadable_products();
do_action('woocommerce_before_account_downloads', (bool)$downloads);
?>
<div class="wtf-download-library">
<h2>Twoje pliki</h2>
<?php if (!$downloads): ?>
<p>Nie masz jeszcze produktów cyfrowych do pobrania.</p>
<?php else: do_action('woocommerce_before_available_downloads'); ?>
<?php foreach (wtf_download_groups($downloads) as $items):
    $first = $items[0]; $count = count($items);
    $noun = $count === 1 ? 'plik' : (($count%10>=2 && $count%10<=4 && !in_array($count%100,[12,13,14],true)) ? 'pliki' : 'plików');
    $expirations = array_unique(array_column($items, 'access_expires'));
    $common_expiry = count($expirations) === 1;
?>
<section class="wtf-download-album" aria-label="<?php echo esc_attr($first['product_name']); ?>">
    <header class="wtf-download-album-heading">
        <div><h3><?php echo esc_html($first['product_name']); ?></h3><p><?php echo esc_html($count.' '.$noun.' do pobrania'); ?><?php if ($common_expiry): ?> · <?php echo $first['access_expires'] ? 'Dostęp do '.esc_html(date_i18n('d.m.Y', strtotime($first['access_expires']))) : 'Dostęp bezterminowy'; ?><?php endif; ?></p></div>
        <?php if (count($items)>1): ?><a class="button wtf-download-all" href="<?php echo esc_url(wtf_download_bundle_url($first['product_id'], $first['order_id'])); ?>">Pobierz wszystko <span>(ZIP)</span></a><?php endif; ?>
    </header>
    <ol class="wtf-download-tracks">
    <?php foreach ($items as $index=>$item): ?>
        <li>
            <span class="wtf-download-track-number" aria-hidden="true"><?php echo esc_html(sprintf('%02d', $index+1)); ?></span>
            <div class="wtf-download-track-info"><h4><?php echo esc_html($item['download_name']); ?></h4><?php if (!$common_expiry || $item['downloads_remaining'] !== ''): ?><p>
                <?php if (!$common_expiry): echo $item['access_expires'] ? 'Dostęp do '.esc_html(date_i18n('d.m.Y', strtotime($item['access_expires']))) : 'Dostęp bezterminowy'; endif; ?>
                <?php if ($item['downloads_remaining'] !== ''): if (!$common_expiry) echo ' · '; ?>Pozostało pobrań: <?php echo esc_html($item['downloads_remaining']); ?><?php endif; ?>
            </p><?php endif; ?>
            <?php if (wtf_download_is_mp3($item['product_id'], $item['download_id'])): ?>
                <audio class="wtf-download-audio" controls preload="none" aria-label="<?php echo esc_attr('Odtwórz: '.$item['download_name']); ?>" src="<?php echo esc_url(add_query_arg('wtf_audio', '1', $item['download_url'])); ?>"></audio>
                <p class="wtf-download-audio-error" role="status" hidden>Nie udało się odtworzyć nagrania. Spróbuj pobrać plik.</p>
                <?php if ($item['downloads_remaining'] !== ''): ?><p>Odtwarzanie może wykorzystać limit pobrań.</p><?php endif; ?>
            <?php endif; ?></div>
            <a class="wtf-download-track-link" href="<?php echo esc_url($item['download_url']); ?>" aria-label="<?php echo esc_attr('Pobierz: '.$item['download_name']); ?>">Pobierz</a>
        </li>
    <?php endforeach; ?>
    </ol>
</section>
<?php endforeach; do_action('woocommerce_after_available_downloads'); endif; ?>
</div>
<?php do_action('woocommerce_after_account_downloads', (bool)$downloads); ?>
