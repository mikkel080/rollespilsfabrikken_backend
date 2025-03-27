FROM php:7.4-cli

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    default-mysql-client \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libzip-dev \
    libpng-dev \
    libmagickwand-dev \
    && pecl install imagick \
    && docker-php-source extract \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install zip pdo pdo_mysql gd \
    && docker-php-ext-enable imagick \
	&& docker-php-source delete

COPY . /var/www/html

WORKDIR /var/www/html

RUN composer install
RUN php artisan key:generate

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
