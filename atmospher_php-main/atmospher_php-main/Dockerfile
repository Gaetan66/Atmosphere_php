# Utiliser l'image PHP officielle avec Apache
FROM php:8.0-apache

# Mettre à jour les dépôts et installer les dépendances pour XSL
RUN apt-get update && apt-get install -y libxslt-dev && \
    docker-php-ext-install xsl

# Copie des fichiers de l'application dans le container
COPY . /var/www/html/

# Activer le module Apache mod_rewrite
RUN a2enmod rewrite

# Exposer le port 80
EXPOSE 80
