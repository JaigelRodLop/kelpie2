FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

COPY public/ /var/www/html/

# Copiar src y sql dentro de /var/www/html
COPY src/ /var/www/html/src/
COPY sql/ /var/www/html/sql/

EXPOSE 80