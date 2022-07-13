FROM bitnami/magento:2.4.4

RUN curl -O  https://files.magerun.net/n98-magerun2.phar  \
    && chmod +x ./n98-magerun2.phar \
    && cp ./n98-magerun2.phar /usr/local/bin/ \
    && rm ./n98-magerun2.phar

#====================================================
# OVERRIDE PARENT ENTRYPOINT
#=====================================================

ENV DIRPATH=/bitnami/magento

RUN echo "is_app_initialized() { false; }" >> /opt/bitnami/scripts/libpersistence.sh

COPY ./bin/docker/conf/development/auth.json /usr/sbin/.composer/auth.json
COPY ./bin/docker/conf/development/auth.json $DIRPATH/var/composer_home/auth.json
RUN chown -R daemon: /usr/sbin/.composer/ $DIRPATH/var/composer_home

COPY ./bin/docker/images/default/entrypoint.sh /usr/local/bin/
RUN  chmod u+x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD [ "/opt/bitnami/scripts/magento/run.sh" ]
