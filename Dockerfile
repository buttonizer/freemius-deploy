FROM php:8.4.7-cli-alpine

RUN apk add --no-cache git && \
    git clone https://github.com/Freemius/freemius-php-sdk.git /freemius-php-api && \
    rm -rf /freemius-php-api/.git

COPY deploy.php /deploy.php
COPY ${file_name} /${file_name}

CMD php /deploy.php