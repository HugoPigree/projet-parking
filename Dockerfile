# Dockerfile pour Parking Partagé - PHP 8.3
FROM php:8.3-apache

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurer Apache
RUN a2enmod rewrite

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier composer.json et composer.lock en premier (pour le cache Docker)
COPY composer.json composer.lock /var/www/html/

# Installer les dépendances PHP
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Copier le reste des fichiers du projet
COPY . /var/www/html

# Regénérer l'autoload avec tous les fichiers
RUN composer dump-autoload --optimize

# Créer les répertoires nécessaires avec les bonnes permissions
RUN mkdir -p storage && \
    chmod -R 777 storage && \
    chown -R www-data:www-data /var/www/html

# Configurer Apache pour pointer vers /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Exposer le port 80
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]
