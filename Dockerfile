FROM php:8.2-apache

# Instala dependencias y extensiones
RUN apt-get update && apt-get install -y libzip-dev unzip git \
    && docker-php-ext-install pdo pdo_mysql

# Copiar código al webroot
COPY . /var/www/html/

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html

# Habilitar mod_rewrite
RUN a2enmod rewrite

EXPOSE 80
CMD ["apache2-foreground"]
