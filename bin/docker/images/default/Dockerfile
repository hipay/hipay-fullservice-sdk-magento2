FROM bitnami/magento:2.4.7

ADD https://files.magerun.net/n98-magerun2.phar /usr/local/bin/n98-magerun2.phar

RUN apt update && apt upgrade -y && apt install curl php-dev gosu git msmtp unzip -y && apt clean \
    && chmod +x /usr/local/bin/n98-magerun2.phar \
    && pecl install xdebug \
    && echo 'Mutex posixsem' >>/opt/bitnami/apache2/conf/httpd.conf \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY ./bin/docker/images/default/php-override.ini /opt/bitnami/php/etc/conf.d/php-override.ini
RUN echo "sendmail_path = /usr/bin/msmtp -t" > /opt/bitnami/php/etc/conf.d/sendmail.ini \
    && printf "host smtp\nport 1025\ntls off\nfrom pi-ecommerce@hipay.com" > /etc/msmtprc

#====================================================
# OVERRIDE PARENT ENTRYPOINT
#=====================================================

ENV DIRPATH=/bitnami/magento

RUN echo "is_app_initialized() { false; }" >> /opt/bitnami/scripts/libpersistence.sh

COPY ./bin/docker/conf/development/auth.json /usr/sbin/.composer/auth.json
COPY ./bin/docker/conf/development/auth.json $DIRPATH/var/composer_home/auth.json
RUN chown -R daemon: /usr/sbin/.composer/ ${DIRPATH}/var/composer_home

COPY ./bin/docker/images/default/entrypoint.sh /usr/local/bin/
RUN  chmod u+x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "/opt/bitnami/scripts/magento/run.sh" ]
