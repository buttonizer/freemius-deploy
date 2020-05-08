FROM php:7.4-cli

ARG file_name
ARG version
ARG sandbox
ARG release_mode

COPY entrypoint.sh /entrypoint.sh
COPY freemius-php-api /freemius-php-api

RUN php ./deploy.php ${file_name} ${version} ${sandbox} ${release_mode}