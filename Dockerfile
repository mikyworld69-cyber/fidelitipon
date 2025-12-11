# Imagen base recomendada para aplicaciones web PHP
FROM php:8.2-apache

# Habilitar mod_rewrite (importante para rutas amigables y paneles)
RUN a2enmod rewrite

# Instalar dependencias del sistema para PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libsodium-dev \
    libssl-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql sodium

# Activar soporte SSL (openssl)
RUN docker-php-ext-install openssl || true

# Configuración de Apache: permitir .htaccess en /var/www/html
RUN echo "<Directory /var/www/html/> \n\
    AllowOverride All \n\
</Directory>" > /etc/apache2/conf-available/override.conf \
    && a2enconf override.conf

# Copiar el código al contenedor
COPY . /var/www/html/

# Permisos correctos para sesiones y archivos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Puerto expuesto
EXPOSE 80

# Comando por defecto (Apache en foreground)
CMD ["apache2-foreground"]
