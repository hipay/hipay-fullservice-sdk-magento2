FROM markoshust/magento-php:8.3-fpm-5

ARG ENVIRONMENT=development
ENV ENVIRONMENT=$ENVIRONMENT

USER root

ADD https://files.magerun.net/n98-magerun2.phar /usr/local/bin/n98-magerun2.phar

RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y msmtp gosu cron && \
    docker-php-ext-install ftp && \
    chmod +x /usr/local/bin/n98-magerun2.phar && \
    echo "sendmail_path = /usr/bin/msmtp -t" > /usr/local/etc/php/conf.d/sendmail.ini && \
    printf "host mailcatcher\nport 1025\ntls off\nfrom pi-ecommerce@hipay.com" > /etc/msmtprc && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /var/tmp/*

# Do not clean folder /tmp in production environment
RUN if [ "$ENVIRONMENT" = "development" ]; then \
    rm -rf /tmp/*; \
    fi

RUN sed -i '/^\[www\]/,$d' /usr/local/etc/php-fpm.conf && \
    echo "include=/usr/local/etc/php-fpm.d/*.conf" >> /usr/local/etc/php-fpm.conf && \
    rm -f /usr/local/etc/php-fpm.d/*.conf

RUN if [ "$ENVIRONMENT" = "development" ]; then \
    curl -s https://ngrok-agent.s3.amazonaws.com/ngrok.asc | tee /etc/apt/trusted.gpg.d/ngrok.asc >/dev/null \
    && echo "deb https://ngrok-agent.s3.amazonaws.com buster main" | tee /etc/apt/sources.list.d/ngrok.list \
    && apt-get update && apt-get install -y ngrok; \
    fi

COPY ./bin/docker/images/default/conf/php/php-fpm-www.conf /usr/local/etc/php-fpm.d/www.conf

COPY ./bin/docker/images/default/conf/php/${ENVIRONMENT}/php-override.ini /usr/local/etc/php/conf.d/php-override.ini
COPY ./bin/docker/conf/development/auth.json /var/www/.composer/auth.json

ENV COMPOSER_HOME=/var/www/.composer
RUN install -d -m 775 -o www-data -g www-data /var/www/.composer /var/www/.composer/cache \
    && chown www-data:www-data /var/www/.composer/auth.json \
    && chmod 600 /var/www/.composer/auth.json

WORKDIR /var/www/html

RUN mkdir -p /var/www/html-magento \
    && chown -R www-data:www-data /var/www/html-magento

COPY ./bin/docker/images/default/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chown www-data:www-data /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
