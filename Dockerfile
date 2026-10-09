FROM php:8.3-cli

# install git
RUN apt-get update \
    && apt-get install -y --no-install-recommends git ca-certificates \
    && rm -rf /var/lib/apt/lists/*


ARG file_name
ARG version
ARG sandbox
ARG release_mode

COPY deploy.php /deploy.php
COPY ${file_name} /${file_name}
RUN git clone https://github.com/Freemius/freemius-php-sdk.git /freemius-php-api

EXPOSE 80/tcp
EXPOSE 80/udp

CMD php /deploy.php