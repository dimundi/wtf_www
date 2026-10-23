#!/bin/sh
set -eu
cd /var/www/html
if ! wp core is-installed --allow-root; then
  wp core install --allow-root --url="$WORDPRESS_URL" --title="What The Frog" --admin_user="$WORDPRESS_ADMIN_USER" --admin_password="$WORDPRESS_ADMIN_PASSWORD" --admin_email="$WORDPRESS_ADMIN_EMAIL" --skip-email
  wp option update blog_public 0 --allow-root
  wp language core install pl_PL --allow-root
  wp site switch-language pl_PL --allow-root
fi
mkdir -p wp-content/upgrade wp-content/uploads wp-content/languages
chown -R www-data:www-data wp-content/upgrade wp-content/uploads wp-content/languages
if ! wp plugin is-installed polylang --allow-root; then
  wp plugin install polylang --allow-root
fi
if ! wp plugin is-installed webp-uploads --allow-root; then
  wp plugin install webp-uploads --allow-root
fi
wp plugin activate polylang webp-uploads wtf-core --allow-root
wp plugin activate wtf_faq --allow-root
wp plugin activate wtf_quiz --allow-root
wp plugin activate wtf_gamefound --allow-root
if ! wp plugin is-installed woocommerce --allow-root; then
  wp plugin install woocommerce --version=10.3.8 --allow-root
fi
wp plugin activate woocommerce --allow-root
wp plugin activate wtf_omnibus --allow-root
wp option update woocommerce_coming_soon no --allow-root
if [ ! -f wp-content/languages/plugins/woocommerce-pl_PL.mo ]; then
  wp language plugin install woocommerce pl_PL --allow-root
fi
wp eval-file wp-content/plugins/wtf-core/setup-shop.php --allow-root
wp option update perflab_modern_image_format webp --allow-root
wp theme activate wtf --allow-root
wp eval-file wp-content/plugins/wtf-core/setup-home.php --allow-root
wp eval-file wp-content/plugins/wtf-core/setup-languages.php --allow-root
# Reguły odświeżamy w nowym procesie, po zapisaniu języków i opcji Polylang.
wp rewrite flush --allow-root
