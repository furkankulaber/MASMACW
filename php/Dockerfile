FROM php:8.1-fpm


RUN apt update && apt install -y zlib1g-dev libpq-dev g++ git libicu-dev zip libzip-dev zip supervisor librabbitmq-dev \
    && docker-php-ext-install intl opcache pdo pdo_mysql pdo_pgsql \
    && pecl install apcu amqp \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-enable amqp
   

WORKDIR /var/www/symfony

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV TZ="Europe/Istanbul"
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "Europe/Istanbul"\n' > /usr/local/etc/php/conf.d/tzone.ini

COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY  ./entrypoint.sh /var/www/config/entrypoint.sh
ENTRYPOINT ["sh", "/var/www/config/entrypoint.sh"]

