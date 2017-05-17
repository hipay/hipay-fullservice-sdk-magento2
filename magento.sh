#!/bin/bash

#==========================================
#  Use this script for one first starting
#
#  WARNING : Put your credentials in auth.env
#  and hipay.env before start and update
#  the docker-compose.dev to link this files
#==========================================
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
    if [ -f ./conf/env/auth.env ];then
        sudo docker-compose stop
        sudo docker-compose rm -fv
        sudo rm -Rf data/ log/ web/
        sudo docker-compose -f docker-compose.yml -f docker-compose.dev.yml build --no-cache
        sudo docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
        sudo docker cp magento2-hipay-fullservice:/var/www/html/magento2 web/
        sudo docker-compose logs -f
    else
        echo "Put your credentials in auth.env and hipay.env before start update the docker-compose.dev to link this files"
    fi
elif [ "$1" = 'restart' ];then
    sudo docker-compose stop
    sudo docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d
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
else
    docker exec magento2-hipay-fullservice gosu magento2 php bin/magento $1
fi