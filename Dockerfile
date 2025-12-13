FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    libsodium-dev \
    libssl-dev \
    zip \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql sodium

# Instalar openssl (si falla, continuar)
RUN docker-php-ext-install openssl || true

# Configurar Apache para permitir .htaccess
RUN echo "<Directory /var/www/public/> \n\
    AllowOverride All \n\
</Directory>" > /etc/apache2/conf-available/override.conf \
    && a2enconf override.conf

# COPIAR PROYECTO ANTES DE COMPOSER
COPY . /var/www/

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/ \
    && chmod -R 755 /var/www/

# Ajustar DocumentRoot para Apache (usar /public)
RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf

# Instalar Composer en el contenedor
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecer el directorio correcto para Composer
WORKDIR /var/www

# Instalar dependencias PHP (DOMPDF + WebPush)
RUN composer install --no-dev --optimize-autoloader

EXPOSE 80

CMD ["apache2-foreground"]
