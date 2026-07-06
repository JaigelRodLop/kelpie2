FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copiar solo la carpeta public como raíz
COPY public/ /var/www/html/

# Copiar src y sql dentro de /var/www/html
COPY src/ /var/www/html/src/
COPY sql/ /var/www/html/sql/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
