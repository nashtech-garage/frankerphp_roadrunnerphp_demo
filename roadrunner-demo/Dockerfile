FROM php:8.2-cli

RUN docker-php-ext-install sockets
WORKDIR /app

RUN apt update && apt install -y unzip git curl

COPY . /app

RUN curl -Ls https://github.com/roadrunner-server/roadrunner/releases/download/v2024.1.4/roadrunner-2024.1.4-linux-amd64.tar.gz | tar -xz \
    && mv roadrunner* rr \
    && chmod +x rr

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

RUN composer config --no-plugins allow-plugins.spiral/composer-publish-plugin true

RUN composer install

EXPOSE 8080

CMD ["./rr", "serve", "-c", ".rr.yaml"]
