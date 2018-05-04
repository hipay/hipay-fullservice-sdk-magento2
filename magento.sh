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
pathPreFile=${header}000*/*.js
pathLibHipay=${header}000*/*/*/*.js
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
    if [ -f ./bin/conf/development/auth.env ];then
        docker-compose stop
        docker-compose rm -fv
        rm -Rf data/ log/ web/
        docker-compose -f docker-compose.dev.yml build --no-cache
        docker-compose -f docker-compose.dev.yml up -d
        docker cp magento2-hipay-fullservice:/var/www/html/magento2 web/
        docker-compose logs -f
    else
        echo "Put your credentials in auth.env and hipay.env before start update the docker-compose.dev to link this files"
    fi
elif [ "$1" = 'restart' ];then
    docker-compose stop
    docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
elif [ "$1" = 'static' ];then
    docker exec magento2-hipay-fullservice rm -Rf /var/www/html/magento2/pub/static/frontend/Magento/luma/en_US/HiPay_FullserviceMagento/
    docker exec magento2-hipay-fullservice gosu magento2 php bin/magento setup:static-content:deploy -t Magento/luma
    docker exec magento2-hipay-fullservice gosu magento2 php bin/magento c:c
elif [ "$1" = 'di' ];then
    docker exec magento2-hipay-fullservice rm -Rf /var/www/html/magento2/var/cache /var/www/html/magento2/var/di /var/www/html/magento2/var/generation /var/www/html/magento2/var/page_cache
    docker exec magento2-hipay-fullservice gosu magento2 php bin/magento setup:di:compile
    docker exec magento2-hipay-fullservice gosu magento2 php bin/magento c:c
elif [ "$1" = 'command' ];then
    docker exec magento2-hipay-fullservice gosu magento2 php bin/magento $2
elif [ "$1" = 'l' ];then
    docker-compose logs -f
elif [ "$1" = 'install' ];then
    docker exec magento2-hipay-fullservice gosu magento2 bin/magento module:enable --clear-static-content HiPay_FullServiceMagento
    docker exec magento2-hipay-fullservice gosu magento2 bin/magento setup:upgrade
    docker exec magento2-hipay-fullservice gosu magento2 bin/magento c:c
elif [ "$1" = 'test' ]; then

    cd bin/tests/000_lib
    bower install hipay-casperjs-lib#develop --allow-root
    cd ../../../;

    if [ "$(ls -A ~/.local/share/Ofi\ Labs/PhantomJS/)" ]; then
        rm -rf ~/.local/share/Ofi\ Labs/PhantomJS/*
        printf "Cache cleared !\n\n"
    else
        printf "Pas de cache à effacer !\n\n"
    fi

    casperjs test $pathLibHipay $pathPreFile ${pathDir}/[0-1]*/[0-9][0-9][0-9][0-9]-*.js --url=$BASE_URL --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ignore-ssl-errors=true --ssl-protocol=any

else
    docker exec magento2-hipay-fullservice gosu magento2 php bin/magento $1
fi
