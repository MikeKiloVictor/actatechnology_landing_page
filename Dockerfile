FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers \
    && printf "ServerName localhost\n" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf
COPY scripts/docker/app-entrypoint.sh /usr/local/bin/app-entrypoint.sh

RUN chmod +x /usr/local/bin/app-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/app-entrypoint.sh"]
