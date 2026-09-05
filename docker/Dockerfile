FROM php:8.4-fpm

# System deps + PHP extensions Laravel/Filament need
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
        libonig-dev libxml2-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer (needed inside the container for artisan/composer maintenance commands)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN groupadd -g 1000 www || true \
    && useradd -u 1000 -ms /bin/bash -g www www || true

EXPOSE 9000
CMD ["php-fpm"]
