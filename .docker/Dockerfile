FROM php:8.1-cli

WORKDIR /var/www

RUN apt-get update && \
    apt-get install -y librdkafka-dev zip && \
    apt-get install -y git

RUN pecl install rdkafka &&  \
    docker-php-ext-enable rdkafka

RUN chown -R www-data:www-data /var/www
