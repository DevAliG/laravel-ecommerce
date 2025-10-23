FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --prefer-dist
COPY . .

FROM php:8.2-apache
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git \
 && docker-php-ext-install pdo pdo_mysql zip

RUN a2enmod rewrite

COPY --from=vendor /app /var/www/html
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
