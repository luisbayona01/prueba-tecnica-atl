FROM php:8.2-apache

# Extension de MySQL (mbstring ya viene habilitada en la imagen base).
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# Composer se usa unicamente para regenerar el autoload PSR-4 optimizado.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Primero las dependencias para aprovechar la cache de capas.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader

# Codigo de la aplicacion.
# Nota: el spec OpenAPI (public/openapi.json) es un artefacto que se
# genera en desarrollo con `composer docs:generate` y se commitea, para
# no incluir las dependencias de desarrollo (swagger-php) en la imagen.
COPY . .

# Env local (sin secretos). Los valores reales llegan por variables de entorno
# de docker-compose, que Env::load() respeta por encima del archivo.
COPY .env.example .env

# DocumentRoot apunta a public/ y permite las reescrituras del front controller.
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default

EXPOSE 80