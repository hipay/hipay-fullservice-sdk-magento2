#!/bin/bash

set -e

#==========================================
#  Use this script for one first starting
#
#  WARNING : Put your credentials in auth.env
#  and hipay.env before start and update
#  the docker-compose to link these files
#==========================================

BASE_URL="http://127.0.0.1:8096/"
URL_MAILCATCHER="http://localhost:1016/"
header="bin/tests/"
pathPreFile=${header}000*/0_init/*.js
pathDir=${header}0*

MAGENTO_ROOT=./src

if [ "$1" = '' ] || [ "$1" = '--help' ]; then
    printf "\n                                                      "
    printf "\n ==================================================== "
    printf "\n                  HIPAY'S HELPER                      "
    printf "\n ==================================================== "
    printf "\n                                                      "
    printf "\n      - init      : Build images and run containers   "
    printf "\n      - restart   : Run containers if they exist yet  "
    printf "\n      - static    : Delete static files from Hipay's module and deploy them "
    printf "\n      - command   : Send command to bin/magento       "
fi

if [ "$1" = 'init' ]; then
    if [ -f ./bin/docker/conf/development/auth.env ]; then
        docker compose down -v
        sudo rm -Rf log/ src/pub/static src/var src/generated web/
        COMPOSE_HTTP_TIMEOUT=500 docker compose up -d --build
    else
        echo "Put your credentials in auth.env and hipay.env before start and update the docker-compose to link these files"
    fi

elif [ "$1" = 'kill' ]; then
    docker compose down -v
    sudo rm -Rf log/ src/pub/static src/var src/generated

elif [ "$1" = 'restart' ]; then
    docker compose stop
    docker compose up -d --build

elif [ "$1" = "cache" ]; then
    docker compose exec phpfpm bin/magento cache:flush
    docker compose exec phpfpm bin/magento cache:clean

elif [ "$1" = 'static' ]; then
    docker compose exec phpfpm rm -rf pub/static/frontend/Magento/luma/en_US/HiPay_FullserviceMagento/
    docker compose exec phpfpm bin/magento setup:static-content:deploy -t Magento/luma -f
    docker compose exec phpfpm bin/magento cache:clean

elif [ "$1" = 'static-fr' ]; then
    docker compose exec phpfpm bin/magento cache:clean
    docker compose exec phpfpm bin/magento cache:flush
    docker compose exec phpfpm rm -rf pub/static/* var/view_preprocessed/* generated/code/*
    docker compose exec phpfpm bin/magento setup:static-content:deploy fr_FR -f
    docker compose exec phpfpm bin/magento cache:clean
    docker compose exec phpfpm bin/magento cache:flush

elif [ "$1" = 'developer' ]; then
    docker compose exec phpfpm bin/magento deploy:mode:set developer
    docker compose exec phpfpm bin/magento setup:upgrade
    docker compose exec phpfpm bin/magento cache:clean
    docker compose exec phpfpm bin/magento cache:flush

elif [ "$1" = 'production' ]; then
    docker compose exec phpfpm bin/magento deploy:mode:set production

elif [ "$1" = 'di' ]; then
    docker compose exec phpfpm rm -rf var/cache var/di var/generation var/page_cache
    docker compose exec phpfpm bin/magento setup:di:compile
    docker compose exec phpfpm bin/magento cache:clean

elif [ "$1" = "db" ]; then
    docker compose exec db sh -lc '
      exec mariadb -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" -D"$MARIADB_DATABASE"'
elif [ "$1" = 'command' ]; then
    shift
    docker compose exec phpfpm bin/magento "$@"

elif [ "$1" = 'l' ]; then
    docker compose logs -f

elif [ "$1" = 'install' ]; then
    docker compose exec phpfpm bin/magento module:enable --clear-static-content HiPay_FullserviceMagento
    docker compose exec phpfpm bin/magento setup:upgrade
    docker compose exec phpfpm bin/magento cache:clean

elif [ "$1" = 'test' ]; then
    rm -rf bin/tests/errors/*
    cd bin/tests/000_lib
    npm install
    cd ../../../

    if [ "$(ls -A ~/.local/share/Ofi\ Labs/PhantomJS/ 2>/dev/null)" ]; then
        rm -rf ~/.local/share/Ofi\ Labs/PhantomJS/*
        printf "Cache cleared !\n\n"
    else
        printf "Pas de cache à effacer !\n\n"
    fi

    casperjs test $pathPreFile ${pathDir}/[0-1]*/[0-9][4][0-9][0-9]-*.js \
        --url=$BASE_URL \
        --url-mailcatcher=$URL_MAILCATCHER \
        --xunit=${header}result.xml \
        --ssl-protocol=TLSv1.2 \
        --engine=slimerjs --headless

else
    docker compose exec phpfpm bash
fi
