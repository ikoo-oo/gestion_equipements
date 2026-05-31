FROM php:8.2-apache

# Installe unzip et les dépendances système
RUN apt-get update && apt-get install -y unzip curl && rm -rf /var/lib/apt/lists/*

# Installe Node.js (pour compiler les assets)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

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

# Installe les dépendances npm
RUN npm install

# Compile les assets (Tailwind, Vue, etc.)
RUN npm run build

# Définit les permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
