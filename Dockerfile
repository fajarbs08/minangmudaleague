FROM php:8.4-cli AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        chromium \
        fonts-liberation \
        git \
        imagemagick \
        unzip \
        nodejs \
        npm \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libmagickwand-dev \
        libpng-dev \
        libasound2 \
        libatk-bridge2.0-0 \
        libatk1.0-0 \
        libatspi2.0-0 \
        libdrm2 \
        libgbm1 \
        libgtk-3-0 \
        libnspr4 \
        libnss3 \
        libu2f-udev \
        libx11-xcb1 \
        libxcomposite1 \
        libxdamage1 \
        libxfixes3 \
        libxkbcommon0 \
        libxrandr2 \
        libzip-dev \
        pkg-config \
        xdg-utils \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd intl pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV ID_CARDS_CHROME_PATH=/usr/bin/chromium
ENV ID_CARDS_NODE_BINARY=/usr/bin/node
ENV ID_CARDS_NODE_MODULES_PATH=/app/storage/app/id-card-node/node_modules
ENV ID_CARDS_NO_SANDBOX=true
ENV PUPPETEER_SKIP_DOWNLOAD=true

WORKDIR /app

FROM php-base AS vendor

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --no-scripts

FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./

RUN npm run build

FROM php-base AS app

WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p storage/app/id-card-node \
    && cd storage/app/id-card-node \
    && npm init -y >/dev/null 2>&1 \
    && npm install puppeteer --omit=dev --no-fund --no-audit

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

RUN chmod +x docker/entrypoint.sh

EXPOSE 8080

CMD ["./docker/entrypoint.sh"]
