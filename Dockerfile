FROM php:8.2-apache

# --- Extensiones necesarias ---
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libxml2-dev \
    zip \
    unzip \
    libsodium-dev \
    libssl-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql sodium

RUN docker-php-ext-install openssl || true

# Activar mod_rewrite
RUN a2enmod rewrite

# Configurar Apache para permitir .htaccess
RUN echo "<Directory /var/www/public/> \
    AllowOverride All \
</Directory>" > /etc/apache2/conf-available/override.conf \
    && a2enconf override.conf

# Copiar proyecto
COPY . /var/www/

# Ajustar DocumentRoot
RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf

# Crear carpetas necesarias (no afecta a symlinks)
RUN mkdir -p /var/www/public/uploads

# Instalar Composer dentro del contenedor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependencias si composer.json existe
RUN if [ -f "/var/www/composer.json" ]; then \
        cd /var/www && composer install --no-dev --optimize-autoloader; \
    else \
        echo "No composer.json encontrado, saltando instalación"; \
    fi

EXPOSE 80

CMD ["apache2-foreground"]
