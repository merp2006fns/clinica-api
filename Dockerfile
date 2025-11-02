FROM php:8.2-apache

# Instala dependencias y extensiones (ajusta según necesites)
RUN apt-get update && apt-get install -y libzip-dev unzip git \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar código al webroot
COPY . /var/www/html/

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html

# Habilitar módulos necesarios
RUN a2enmod rewrite headers

# Evitar warning de ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80
CMD ["apache2-foreground"]