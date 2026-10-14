FROM wordpress:cli-php8.4 AS cli
FROM wordpress:php8.4-apache
COPY --from=cli /usr/local/bin/wp /usr/local/bin/wp

# Blokada listowania katalogów kopii przez Apache.
RUN printf '<Directory /var/www/html>\nOptions -Indexes +FollowSymLinks\n</Directory>\n' > /etc/apache2/conf-available/local-copy.conf \
    && a2enconf local-copy
