FROM php:7.4-cli

ARG file_name
ARG version
ARG sandbox
ARG release_mode

COPY deploy.php /deploy.php
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y git
RUN git clone git@github.com:Freemius/php-sdk.git /freemius-php-api

EXPOSE 80/tcp
EXPOSE 80/udp

CMD php /deploy.php ${file_name} ${version} ${sandbox} ${release_mode}