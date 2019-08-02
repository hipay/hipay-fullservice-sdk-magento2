FROM hipay/hipay-magento2:2.3.1

MAINTAINER Support Fullservice <support.tpp@hipay.com>

#====================================================
# OVERRIDE PARENT ENTRYPOINT
#=====================================================
COPY ./bin/docker/images/default/entrypoint.sh /usr/local/bin/
RUN chmod u+x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
