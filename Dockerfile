# Imagen base
FROM php:8.2-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar dependencias necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libsodium-dev \
    libssl-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd mysqli pdo pdo_mysql sodium

# Configurar bloque <Directory> correctamente usando heredoc
RUN cat << 'EOF' > /etc/apache2/conf-available/override.conf
<Directory /var/www/public/>
    AllowOverride All
    Require all granted
</Directory>
EOF

RUN a2enconf override.conf

# Copiar proyecto
COPY . /var/www/

# Cambiar DocumentRoot a /var/www/public
RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf

# Asegurar permisos
RUN chown -R www-data:www-data /var/www/ \
    && chmod -R 755 /var/www/

# Instalar Composer (desde imagen oficial)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Ejecutar instalación de dependencias
RUN cd /var/www && composer install --no-dev --optimize-autoloader || true

EXPOSE 80

# Iniciar Apache
CMD ["apache2-foreground"]
