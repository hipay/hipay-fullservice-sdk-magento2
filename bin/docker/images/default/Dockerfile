FROM hipay/hipay-magento2:2.3.1

MAINTAINER Support Fullservice <support.tpp@hipay.com>

#====================================================
# OVERRIDE PARENT ENTRYPOINT
#=====================================================
COPY ./bin/docker/images/default/entrypoint.sh /usr/local/bin/
RUN  gosu magento2 sed -i -e"s/\"minimum-stability\": \"alpha\"/\"minimum-stability\": \"dev\"/g" composer.json \
     && chmod u+x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
