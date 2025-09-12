# Imagen base con PHP y Apache
FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar extensiones necesarias de PHP
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar errores para desarrollo
RUN echo "display_errors=On\nerror_reporting=E_ALL" > /usr/local/etc/php/conf.d/dev.ini

# Copiar proyecto (al principio estará vacío)
COPY . /var/www/html

# Cambiar permisos a www-data
RUN chown -R www-data:www-data /var/www/html
