<?php
// Te same blokady podczas przygotowania kopii przez WP-CLI.
WP_CLI::add_hook('before_wp_load', static function () {
    if (!defined('WTF_STAGING_SITE')) define('WTF_STAGING_SITE', true);
    require_once ABSPATH . 'wp-includes/plugin.php';
    require_once '/usr/local/bin/wtf-staging-guard.php';
});
