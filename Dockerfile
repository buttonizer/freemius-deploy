FROM php:7.4-cli

COPY entrypoint.sh /entrypoint.sh
COPY freemius-php-api /freemius-php-api

ENTRYPOINT [ "/entrypoint.sh" ]