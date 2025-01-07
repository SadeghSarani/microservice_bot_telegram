FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    libmemcached-dev \
    zlib1g-dev \
    && docker-php-ext-install zip




RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/
RUN a2enmod rewrite

EXPOSE 80

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN bash -c 'echo -e run script _________________________________'

RUN mkdir -p /scripts
COPY run.sh /scripts
WORKDIR /scripts
RUN chmod +x run.sh
RUN ./run.sh

CMD ["apache2-foreground"]

