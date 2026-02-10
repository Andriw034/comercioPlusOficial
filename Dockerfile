# ---- Base image ----
FROM php:8.3-cli-bookworm

# ---- System deps + PHP extensions required by Laravel ----
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    bash \
  && docker-php-ext-install \
    bcmath \
    intl \
    pdo_mysql \
    pdo_pgsql \
    zip \
  && rm -rf /var/lib/apt/lists/*

# ---- Composer ----
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---- App ----
WORKDIR /app
COPY . .

# ---- Install PHP deps (prod) ----
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# ---- Laravel writable dirs ----
RUN mkdir -p storage/logs bootstrap/cache \
  && chmod -R ug+rwx storage bootstrap/cache

# Railway will provide $PORT; keep 8080 as default
EXPOSE 8080

# ---- Start command (robust for Railway) ----
CMD ["bash","-lc","./docker/railway-start.sh"]
