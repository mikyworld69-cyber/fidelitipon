# PHP 8.2 con CLI
FROM php:8.2-cli

# Configuración del working directory
WORKDIR /app

# Instalar dependencias básicas del sistema
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev \
    && docker-php-ext-install zip mysqli pdo pdo_mysql \
    && apt-get clean

# Composer oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- CAPA QUE SE CACHEA (importante) ---
# Copiar composer.json y composer.lock antes que el código
COPY composer.json composer.lock ./

# Instalar dependencias (queda en caché si composer.json no cambia)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# --- COPIAR EL RESTO DEL PROYECTO ---
COPY . .

# Exponer puerto (Render detecta 10000 automáticamente)
EXPOSE 10000

# Comando de inicio
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
