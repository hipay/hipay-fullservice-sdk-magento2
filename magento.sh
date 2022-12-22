#!/bin/bash

set -e

#==========================================
#  Use this script for one first starting
#
#  WARNING : Put your credentials in auth.env
#  and hipay.env before start and update
#  the docker-compose-bitnami to link this files
#==========================================

BASE_URL="http://127.0.0.1:8096/"
URL_MAILCATCHER="http://localhost:1096/"
header="bin/tests/"
pathPreFile=${header}000*/0_init/*.js
pathDir=${header}0*
containerMG2=hipay-fullservice-sdk-magento2-magento-1

if [ "$1" = '' ] || [ "$1" = '--help' ]; then
    printf "\n                                                      "
    printf "\n ==================================================== "
    printf "\n                  HIPAY'S HELPER                      "
    printf "\n ==================================================== "
    printf "\n                                                      "
    printf "\n      - init      : Build images and run containers   "
    printf "\n      - init      : Start containers on HTTPS mode    "
    printf "\n      - restart   : Run containers if they exist yet  "
    printf "\n      - static    : Delete static files from Hipay's module and deploy them "
    printf "\n      - command   : Send command to bin/magento       "
fi

if [ "$1" = 'init' ]; then
    if [ -f ./bin/docker/conf/development/auth.env ]; then
        docker compose -f docker-compose.yml stop
        docker compose -f docker-compose.yml rm -fv
        sudo rm -Rf log/ web/
        docker compose -f docker-compose.yml build
        COMPOSE_HTTP_TIMEOUT=200 docker compose -f docker-compose.yml up -d
        # docker cp $containerMG2:/var/www/html/magento2 web/
    else
        echo "Put your credentials in auth.env and hipay.env before start update the docker-compose-bitnami to link this files"
    fi
elif [ "$1" = 'kill' ]; then
    docker compose -f docker-compose.yml stop
    docker compose -f docker-compose.yml rm -fv
    sudo rm -Rf log/ web/
elif [ "$1" = 'start_https' ]; then
    docker compose -f docker-compose-bitnami-https.yml up -d --build
elif [ "$1" = 'restart' ]; then
    docker compose -f docker-compose.yml stop
    docker compose -f docker-compose.yml up -d
elif [ "$1" = 'static' ]; then
    docker exec $containerMG2 rm -Rf /var/www/html/magento2/pub/static/frontend/Magento/luma/en_US/HiPay_FullserviceMagento/
    docker exec $containerMG2 gosu magento2 php bin/magento setup:static-content:deploy -t Magento/luma
    docker exec $containerMG2 gosu magento2 php bin/magento c:c
elif [ "$1" = 'di' ]; then
    docker exec $containerMG2 rm -Rf /var/www/html/magento2/var/cache /var/www/html/magento2/var/di /var/www/html/magento2/var/generation /var/www/html/magento2/var/page_cache
    docker exec $containerMG2 gosu magento2 php bin/magento setup:di:compile
    docker exec $containerMG2 gosu magento2 php bin/magento c:c
elif [ "$1" = 'command' ]; then
    docker exec $containerMG2 gosu magento2 php bin/magento $2
elif [ "$1" = 'l' ]; then
    docker compose -f docker-compose.yml logs -f
elif [ "$1" = 'install' ]; then
    docker exec $containerMG2 gosu magento2 bin/magento module:enable --clear-static-content HiPay_FullServiceMagento
    docker exec $containerMG2 gosu magento2 bin/magento setup:upgrade
    docker exec $containerMG2 gosu magento2 bin/magento c:c
elif [ "$1" = 'test' ]; then

    rm -rf bin/tests/errors/*
    cd bin/tests/000_lib
    npm install
    cd ../../../

    if [ "$(ls -A ~/.local/share/Ofi\ Labs/PhantomJS/)" ]; then
        rm -rf ~/.local/share/Ofi\ Labs/PhantomJS/*
        printf "Cache cleared !\n\n"
    else

        printf "Pas de cache à effacer !\n\n"
    fi

    #casperjs test $pathPreFile ${pathDir}/[0-1]*/[0-9][4][0-9][0-9]-*.js --url=$BASE_URL --url-mailcatcher=$URL_MAILCATCHER --login-backend=$LOGIN_BACKEND --pass-backend=$PASS_BACKEND --login-paypal=$LOGIN_PAYPAL --pass-paypal=$PASS_PAYPAL --xunit=${header}result.xml --ssl-protocol=any --fail-fast
    casperjs test $pathPreFile ${pathDir}/[0-1]*/[0-9][4][0-9][0-9]-*.js --url=$BASE_URL --url-mailcatcher=$URL_MAILCATCHER --xunit=${header}result.xml --ssl-protocol=TLSv1.2 --engine=slimerjs --headless

else
    docker exec -ti $containerMG2 bash
fi
