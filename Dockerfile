FROM php:8.2-apache
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Copiar carpeta public como raíz del servidor
COPY public/ /var/www/html/

# Copiar src y sql al mismo nivel que html
COPY src/ /var/www/src/
COPY sql/ /var/www/sql/

EXPOSE 80
