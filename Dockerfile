FROM php:8.2-apache

# Installe les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Configure Apache
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Installe Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copie le projet
WORKDIR /var/www/html
COPY . .

# Installe les dépendances
RUN composer install --no-dev

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
