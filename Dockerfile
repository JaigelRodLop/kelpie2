FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copiar todo el proyecto directamente
COPY . /var/www/html/

EXPOSE 80
