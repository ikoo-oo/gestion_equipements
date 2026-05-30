FROM php:8.3-apache

# Install system deps (faster with --no-install-recommends)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    unzip git curl \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql zip gd bcmath opcache \
    && a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install && npm run build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 777 storage \
    && chmod -R 775 bootstrap/cache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80

CMD sh -c "php artisan config:cache && \
           php artisan route:cache && \
           php artisan view:cache && \
           php artisan storage:link && \
           php artisan migrate --force && \
           apache2-foreground"