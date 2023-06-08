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
        docker compose rm -sfv
        docker compose rm -sfv mariadb
        docker volume rm -f hipay-fullservice-sdk-magento2_mariadb_data
        sudo rm -Rf log/ web/
        COMPOSE_HTTP_TIMEOUT=500 docker compose up -d --build
        # docker cp $containerMG2:/bitnami/magento web/
    else
        echo "Put your credentials in auth.env and hipay.env before start update the docker-compose-bitnami to link this files"
    fi
elif [ "$1" = 'kill' ]; then
    docker compose rm -sfv
    docker compose rm -sfv mariadb
    docker volume rm -f hipay-fullservice-sdk-magento2_mariadb_data
    sudo rm -Rf log/ web/
elif [ "$1" = 'start_https' ]; then
    docker compose -f docker-compose-bitnami-https.yml up -d --build
elif [ "$1" = 'restart' ]; then
    docker compose stop
    docker compose up -d --build
elif [ "$1" = "cache" ]; then
    docker exec $containerMG2 gosu daemon php /bitnami/magento/bin/magento c:f
    docker exec $containerMG2 gosu daemon php /bitnami/magento/bin/magento c:c
elif [ "$1" = 'static' ]; then
    docker exec $containerMG2 rm -Rf /bitnami/magento/pub/static/frontend/Magento/luma/en_US/HiPay_FullserviceMagento/
    docker exec $containerMG2 gosu daemon php /bitnami/magento/bin/magento setup:static-content:deploy -t Magento/luma
    docker exec $containerMG2 gosu daemon php /bitnami/magento/bin/magento c:c
elif [ "$1" = 'di' ]; then
    docker exec $containerMG2 rm -Rf /bitnami/magento/var/cache /bitnami/magento/var/di /bitnami/magento/var/generation /bitnami/magento/var/page_cache
    docker exec $containerMG2 gosu daemon php /bitnami/magento/bin/magento setup:di:compile
    docker exec $containerMG2 gosu daemon php /bitnami/magento/bin/magento c:c
elif [ "$1" = "db" ]; then
    docker exec -ti $containerMG2 mariadb -u bn_magento -D bitnami_magento -h mariadb
elif [ "$1" = 'command' ]; then
    docker exec $containerMG2 gosu daemon php /bitnami/magento/bin/magento $2
elif [ "$1" = 'l' ]; then
    docker compose logs -f
elif [ "$1" = 'install' ]; then
    docker exec $containerMG2 gosu daemon /bitnami/magento/bin/magento module:enable --clear-static-content HiPay_FullServiceMagento
    docker exec $containerMG2 gosu daemon /bitnami/magento/bin/magento setup:upgrade
    docker exec $containerMG2 gosu daemon /bitnami/magento/bin/magento c:c
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
