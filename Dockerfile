FROM php:7.4-cli

ARG file_name
ARG version
ARG sandbox
ARG release_mode

COPY deploy.php /deploy.php
COPY freemius-php-api /freemius-php-api

EXPOSE 80/tcp
EXPOSE 80/udp

CMD php /deploy.php ${file_name} ${version} ${sandbox} ${release_mode}