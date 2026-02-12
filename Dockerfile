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
    bash \
  && docker-php-ext-install \
    bcmath \
    intl \
    pdo_mysql \
    zip \
  && rm -rf /var/lib/apt/lists/*

# ---- Composer ----
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# ---- App ----
WORKDIR /app
COPY . .

# ---- Install PHP deps (prod) ----
# Use --no-scripts to avoid artisan script failures during image build.
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# ---- Laravel writable dirs (best-effort) ----
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
  && chmod -R 775 storage bootstrap/cache || true \
  && chown -R www-data:www-data storage bootstrap/cache || true

# Railway will provide $PORT; keep 8080 as default
EXPOSE 8080

# ---- Start command (keep it simple for Railway) ----
# Do NOT run migrate/cache clear on boot. Run those in CI/deploy when needed.
CMD ["sh", "-lc", "php -S 0.0.0.0:${PORT:-8080} -t public public/index.php"]
