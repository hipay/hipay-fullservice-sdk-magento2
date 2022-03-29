FROM hipay/hipay-magento2:2.4.3

LABEL maintainer="Support Enterprise"
LABEL maintainer_email="support.tpp@hipay.com"

COPY ./bin/docker/conf/development/msmtprc /etc/
#====================================================
# OVERRIDE PARENT ENTRYPOINT
#=====================================================
COPY ./bin/docker/images/default/entrypoint.sh /usr/local/bin/
RUN  gosu magento2 sed -i -e"s/\"minimum-stability\": \"alpha\"/\"minimum-stability\": \"dev\"/g" composer.json \
     && chmod u+x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
