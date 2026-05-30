FROM php:8.2-apache

# Installe les extensions PHP
RUN docker-php-ext-install pdo pdo_mysql

# Active le module rewrite d'Apache
RUN a2enmod rewrite

# Configure Apache pour Laravel (pointe vers public/)
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Installe Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Définit le répertoire de travail
WORKDIR /var/www/html

# Copie tous les fichiers du projet
COPY . .

# Installe les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Définit les permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
