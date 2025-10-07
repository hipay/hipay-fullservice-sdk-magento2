FROM markoshust/magento-php:8.3-fpm-5

USER root

ADD https://files.magerun.net/n98-magerun2.phar /usr/local/bin/n98-magerun2.phar

RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y msmtp gosu && \
    docker-php-ext-install ftp && \
    chmod +x /usr/local/bin/n98-magerun2.phar && \
    echo "sendmail_path = /usr/bin/msmtp -t" > /usr/local/etc/php/conf.d/sendmail.ini && \
    printf "host smtp\nport 1025\ntls off\nfrom pi-ecommerce@hipay.com" > /etc/msmtprc && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN sed -i '/^\[www\]/,$d' /usr/local/etc/php-fpm.conf && \
    echo "include=/usr/local/etc/php-fpm.d/*.conf" >> /usr/local/etc/php-fpm.conf && \
    rm -f /usr/local/etc/php-fpm.d/*.conf

RUN echo "[www] \n\
user = www-data \n\
group = www-data \n\
listen = 9000 \n\
listen.owner = www-data \n\
listen.group = www-data \n\
pm = dynamic \n\
pm.max_children = 5 \n\
pm.start_servers = 2 \n\
pm.min_spare_servers = 1 \n\
pm.max_spare_servers = 3 \n\
access.log = /proc/self/fd/2 \n\
catch_workers_output = yes \n\
decorate_workers_output = no \n\
clear_env = no \n\
" > /usr/local/etc/php-fpm.d/www.conf

COPY ./bin/docker/images/default/php-override.ini /usr/local/etc/php/conf.d/php-override.ini
COPY ./bin/docker/images/default/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY ./bin/docker/conf/development/auth.json /var/www/.composer/auth.json

ENV COMPOSER_HOME=/var/www/.composer
RUN install -d -m 775 -o www-data -g www-data /var/www/.composer /var/www/.composer/cache \
 && chown www-data:www-data /var/www/.composer/auth.json \
 && chmod 600 /var/www/.composer/auth.json


RUN chown www-data:www-data /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
