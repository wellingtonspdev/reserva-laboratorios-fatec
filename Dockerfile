FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        default-mysql-client \
        libldap2-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        nodejs \
        npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install gd intl ldap mysqli pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build:css

RUN mkdir -p local/logs local/cache local/sessions uploads \
    && chown -R www-data:www-data local uploads

COPY docker/app/entrypoint.sh /usr/local/bin/classroombookings-entrypoint
COPY docker/app/bootstrap-db.sh /usr/local/bin/classroombookings-bootstrap-db
RUN chmod +x /usr/local/bin/classroombookings-entrypoint /usr/local/bin/classroombookings-bootstrap-db

EXPOSE 8000

ENTRYPOINT ["classroombookings-entrypoint"]
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8000}"]
