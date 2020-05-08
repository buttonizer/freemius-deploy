FROM php:7.4-cli

# install git
RUN apt-get update
RUN apt-get install -y git


ARG file_name
ARG version
ARG sandbox
ARG release_mode

COPY deploy.php /deploy.php
RUN git clone https://github.com/Freemius/freemius-php-sdk.git /freemius-php-api

EXPOSE 80/tcp
EXPOSE 80/udp

CMD php /deploy.php ${file_name} ${version} ${sandbox} ${release_mode}