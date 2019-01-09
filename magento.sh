#!/bin/bash

#==========================================
#  Use this script for one first starting
#
#  WARNING : Put your credentials in auth.env
#  and hipay.env before start and update
#  the docker-compose.dev to link this files
#==========================================

BASE_URL="http://127.0.0.1:8096/"
URL_MAILCATCHER="http://localhost:1096/"
header="bin/tests/"
pathPreFile=${header}000*/0_init/*.js
pathDir=${header}0*

if [ "$1" = '' ] || [ "$1" = '--help' ];then
    printf "\n                                                      "
    printf "\n ==================================================== "
    printf "\n                  HIPAY'S HELPER                      "
    printf "\n ==================================================== "
    printf "\n                                                      "
    printf "\n      - init      : Build images and run containers   "
    printf "\n      - restart   : Run containers if they exist yet   "
    printf "\n      - static    : Delete static files from Hipay's module and deploy them "
    printf "\n      - command   : Send command to bin/magento      "
fi

if [ "$1" = 'init' ];then
    if [ -f ./bin/docker/conf/development/auth.env ];then
        docker-compose -f docker-compose.dev.yml stop
        docker-compose -f docker-compose.dev.yml rm -fv
        rm -Rf data/ log/ web/
        docker-compose -f docker-compose.dev.yml build --no-cache
        docker-compose -f docker-compose.dev.yml up -d
        docker cp hipayfullservicesdkmagento2_web_1:/var/www/html/magento2 web/
        docker-compose -f docker-compose.dev.yml logs -f
    else
        echo "Put your credentials in auth.env and hipay.env before start update the docker-compose.dev to link this files"
    fi
elif [ "$1" = 'restart' ];then
    docker-compose -f docker-compose.dev.yml stop
    docker-compose -f docker-compose.dev.yml up -d
elif [ "$1" = 'static' ];then
    docker exec hipayfullservicesdkmagento2_web_1 rm -Rf /var/www/html/magento2/pub/static/frontend/Magento/luma/en_US/HiPay_FullserviceMagento/
    docker exec hipayfullservicesdkmagento2_web_1 gosu magento2 php bin/magento setup:static-content:deploy -t Magento/luma
    docker exec hipayfullservicesdkmagento2_web_1 gosu magento2 php bin/magento c:c
elif [ "$1" = 'di' ];then
    docker exec hipayfullservicesdkmagento2_web_1 rm -Rf /var/www/html/magento2/var/cache /var/www/html/magento2/var/di /var/www/html/magento2/var/generation /var/www/html/magento2/var/page_cache
    docker exec hipayfullservicesdkmagento2_web_1 gosu magento2 php bin/magento setup:di:compile
    docker exec hipayfullservicesdkmagento2_web_1 gosu magento2 php bin/magento c:c
elif [ "$1" = 'command' ];then
    docker exec hipayfullservicesdkmagento2_web_1 gosu magento2 php bin/magento $2
elif [ "$1" = 'l' ];then
    docker-compose -f docker-compose.dev.yml logs -f
elif [ "$1" = 'install' ];then
    docker exec hipayfullservicesdkmagento2_web_1 gosu magento2 bin/magento module:enable --clear-static-content HiPay_FullServiceMagento
    docker exec hipayfullservicesdkmagento2_web_1 gosu magento2 bin/magento setup:upgrade
    docker exec hipayfullservicesdkmagento2_web_1 gosu magento2 bin/magento c:c
elif [ "$1" = 'test' ]; then

else
    docker exec magento2-hipay-fullservice gosu magento2 php bin/magento $1
fi
