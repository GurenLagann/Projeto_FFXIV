FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    procps \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
        gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# www-data's passwd entry points $HOME at /var/www (the bind-mounted repo);
# without this, composer/git write dotfiles (.composer, .gitconfig) straight
# into the working tree.
ENV HOME=/tmp
ENV COMPOSER_HOME=/tmp/composer

WORKDIR /var/www

USER www-data
