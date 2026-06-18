FROM php:8.3-fpm-alpine

# System packages
RUN apk add --no-cache \
    nginx supervisor bash curl gettext \
    libpng-dev libjpeg-turbo-dev libzip-dev \
    zip unzip oniguruma-dev postgresql-dev \
    nodejs npm \
    autoconf g++ make linux-headers

# PHP extensions
RUN docker-php-ext-configure gd --with-jpeg \
 && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip gd pcntl bcmath
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies first (better layer caching)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

# Copy source
COPY . .

# Build frontend (VITE vars may be empty here — runtime window vars are used as fallback)
RUN npm run build && rm -rf node_modules

# Composer post-install scripts
RUN composer run-script post-autoload-dump || true

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Copy configs
COPY .docker/nginx.conf.template /etc/nginx/nginx.conf.template
COPY .docker/supervisord.conf    /etc/supervisord.conf
COPY .docker/entrypoint.sh       /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/entrypoint.sh"]
