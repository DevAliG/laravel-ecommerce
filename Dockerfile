# ---------- Stage 1: install PHP deps (vendor) without running scripts ----------
FROM composer:2 AS vendor
WORKDIR /app

# فقط فایل‌های composer برای کش بهتر
COPY composer.json composer.lock ./

# مهم: --no-scripts تا artisan صدا نشه
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts

# بقیه سورس (برای autoload کلاس‌ها)
COPY . .

# بهینه‌سازی اتولود (بدون اجرای اسکریپت‌ها)
RUN composer dump-autoload -o --no-scripts


# ---------- Stage 2: runtime with Apache ----------
FROM php:8.2-apache

# پکیج‌ها و اکستنشن‌های لازم
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev default-mysql-client \
 && docker-php-ext-install pdo pdo_mysql mbstring zip gd

# Apache rewrite و داکیومنت‌روت
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Render روی پورت 10000
ENV PORT=10000
RUN sed -ri 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
 && sed -ri 's!<VirtualHost \*:80>!<VirtualHost \*:10000>!' /etc/apache2/sites-available/000-default.conf

# (اختیاری) باینری composer داخل ایمیج نهایی (برای مواقع لازم)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# کپیِ خروجی استیج vendor (شامل vendor/ و کل سورس)
COPY --from=vendor /app /var/www/html

# بهینه‌سازی لاراول (بدون key generate؛ APP_KEY را از env می‌گیریم)
# storage:link ممکنه بار اول خطای تکراری بده، مشکلی نیست
RUN php artisan storage:link || true \
 && php artisan config:cache && php artisan route:cache && php artisan view:cache

# دسترسی‌ها
RUN chown -R www-data:www-data /var/www/html \
 && find storage bootstrap/cache -type d -exec chmod 775 {} \;

EXPOSE 10000
CMD ["apache2-foreground"]
