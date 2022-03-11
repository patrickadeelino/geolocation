FROM php:8.1-cli

WORKDIR /var/www

RUN apt-get update && \
    apt-get install -y librdkafka-dev zip && \
    apt-get install -y git

RUN pecl install rdkafka &&  \
    pecl install redis &&  \
    docker-php-ext-enable rdkafka && \
    docker-php-ext-enable redis

COPY composer.* ./

RUN php composer.phar install

COPY . .

RUN chown -R www-data:www-data .

ENTRYPOINT ["php", "src/index.php"]
