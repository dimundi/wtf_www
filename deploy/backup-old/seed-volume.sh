#!/bin/sh
set -eu
if [ "${1:-}" != '--overlay-only' ]; then
    tar -xzf /backup/site-files.tar.gz -C /var/www/html
fi
cp /source/wp-config.php /source/.htaccess /source/.user.ini /var/www/html/
mkdir -p /var/www/html/wp-content/mu-plugins
cp /source/wp-content/mu-plugins/local-copy-guard.php /var/www/html/wp-content/mu-plugins/
while IFS= read -r relative; do
    relative=$(printf '%s' "$relative" | tr -d '\r')
    [ -n "$relative" ] || continue
    cp "/source/$relative" "/var/www/html/$relative"
done < /tools/local-overlays.txt
chown -R www-data:www-data /var/www/html
echo VOLUME_READY
