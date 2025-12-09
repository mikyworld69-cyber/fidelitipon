FROM php:8.2-cli

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    libssl-dev \
    && docker-php-ext-install mysqli

# Copiar archivos del proyecto
WORKDIR /app
COPY . /app

# Descomprimir vendor.zip si existe
RUN if [ -f /app/vendor.zip ]; then unzip -o /app/vendor.zip -d /app; fi

# Exponer el puerto
EXPOSE 10000

# Ejecutar el servidor PHP usando /public como raíz
CMD ["php", "-S", "0.0.0.0:10000", "-t", "/app/public"]
