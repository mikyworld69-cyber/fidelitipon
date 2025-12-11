FROM php:8.2-apache

RUN a2enmod rewrite

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

RUN docker-php-ext-install openssl || true

RUN echo "<Directory /var/www/public/> \n\
    AllowOverride All \n\
</Directory>" > /etc/apache2/conf-available/override.conf \
    && a2enconf override.conf

# Copiar proyecto
COPY . /var/www/

# Cambiar DocumentRoot a /public
RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/ \
    && chmod -R 755 /var/www/

EXPOSE 80

CMD ["apache2-foreground"]
