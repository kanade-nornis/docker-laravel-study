FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev zlib1g-dev libpng-dev libonig-dev unzip git \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# Composer をコピー（build 時に composer イメージから取り出す）
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]