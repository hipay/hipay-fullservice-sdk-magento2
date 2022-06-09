#!/bin/bash

set +e
COLOR_SUCCESS='\033[0;32m'
NC='\033[0m'
PREFIX_STORE1=$RANDOM$RANDOM
ENV_DEVELOPMENT="development"
ENV_STAGE="stage"
ENV_PROD="production"
NEED_SETUP_CONFIG=0
MAGENTO_ROOT=/bitnami/magento/

if [ ! -f $MAGENTO_ROOT/app/etc/config.php ] && [ ! -f $MAGENTO_ROOT/app/etc/env.php ]; then
    NEED_SETUP_CONFIG="1"
fi

export COMPOSER_MEMORY_LIMIT=-1

#==========================================
# PARENT ENTRYPOINT
#==========================================
sed -i '/exec "$@"/d' /opt/bitnami/scripts/magento/entrypoint.sh
/bin/bash /opt/bitnami/scripts/magento/entrypoint.sh "$@"

#==========================================
#  INIT HIPAY CONFIGURATION AND DEV
#==========================================
if [ "$NEED_SETUP_CONFIG" = "1" ]; then

    #==========================================
    # XDebug
    #==========================================
    if [[ "$XDEBUG_ENABLED" = "1" ]]; then
        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
        printf "\n${COLOR_SUCCESS}     ENABLE XDEBUG $ENVIRONMENT          ${NC}\n"
        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

        pecl install xdebug
        xdebugFile=/opt/bitnami/php/etc/conf.d/xdebug.ini

        echo 'zend_extension=xdebug' >>$xdebugFile

        echo "xdebug.mode=debug" >>$xdebugFile
        echo "xdebug.idekey=PHPSTORM" >>$xdebugFile

        echo "xdebug.remote_enable=on" >>$xdebugFile
        echo "xdebug.remote_autostart=off" >>$xdebugFile
    fi

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALLING HIPAY MODULE             ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    cd /bitnami/magento
    su -c 'composer require hipay/hipay-fullservice-sdk-php && \
      php bin/magento module:enable HiPay_FullserviceMagento && \
      php bin/magento setup:upgrade && \
      php bin/magento setup:di:compile && \
      php bin/magento setup:static-content:deploy -f && \
      php bin/magento cache:flush' daemon -s /bin/bash

#    su -c 'rm -rf /bitnami/magento/vendor/hipay/hipay-fullservice-sdk-magento2 && ln -s /tmp/HiPay/FullserviceMagento /bitnami/magento/vendor/hipay/hipay-fullservice-sdk-magento2'

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     CONFIGURING HIPAY CREDENTIAL        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set hipay/hipay_credentials/api_username_test $HIPAY_API_USER_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set --encrypt hipay/hipay_credentials/api_password_test $HIPAY_API_PASSWORD_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set --encrypt hipay/hipay_credentials/secret_passphrase_test $HIPAY_SECRET_PASSPHRASE_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set hipay/hipay_credentials_moto/api_username_test $HIPAY_API_USER_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set --encrypt hipay/hipay_credentials_moto/api_password_test $HIPAY_API_PASSWORD_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set --encrypt hipay/hipay_credentials_moto/secret_passphrase_test $HIPAY_SECRET_PASSPHRASE_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set hipay/hipay_credentials_tokenjs/api_username_test $HIPAY_TOKENJS_USERNAME_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set --encrypt hipay/hipay_credentials_tokenjs/api_password_test $HIPAY_TOKENJS_PUBLICKEY_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set hipay/hipay_credentials_applepay/api_username_test $HIPAY_APPLEPAY_USERNAME_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set --encrypt hipay/hipay_credentials_applepay/api_password_test $HIPAY_APPLEPAY_PASSWORD_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set --encrypt hipay/hipay_credentials_applepay/secret_passphrase_test $HIPAY_APPLEPAY_SECRET_PASSPHRASE_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set hipay/hipay_credentials_applepay_tokenjs/api_username_test $HIPAY_APPLEPAY_TOKENJS_USERNAME_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set --encrypt hipay/hipay_credentials_applepay_tokenjs/api_password_test $HIPAY_APPLEPAY_TOKENJS_PUBLICKEY_TEST
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/hipay_cc/cctypes "VI,MC,AE,CB,MI"
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set hipay/hipay_credentials/hashing_algorithm_test 'SHA512'

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         ACTIVATE PAYMENT METHODS        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    methods=$(echo $ACTIVE_METHODS | tr "," "\n")
    for code in $methods; do
        printf "\n"
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/$code/active 1
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/$code/debug 1
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/$code/is_test_mode 1
        printf "${COLOR_SUCCESS} Method $code is activated in test mode ${NC}\n"
    done

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         UPDATE SEQUENCE ORDER           ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    n98-magerun2.phar db:query "INSERT INTO ${MAGE_DB_PREFIX}sequence_order_1 values ('$PREFIX_STORE1')"
    printf "${COLOR_SUCCESS} Order sequence is $PREFIX_STORE1${NC}\n"

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         LINK WITH HIPAY'S SDK PHP        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    if [ -f $MAGENTO_ROOT/sdk/hipay-fullservice-sdk-php/composer.json ]; then
        cd $MAGENTO_ROOT/vendor/hipay/
        rm -Rf hipay-fullservice-sdk-php
        ln -sf $MAGENTO_ROOT/sdk/hipay-fullservice-sdk-php hipay-fullservice-sdk-php
        printf "${COLOR_SUCCESS} HiPay's SDK php is now linked ${NC}\n"
    fi

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}              FOLDER CACHE               ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    rm -Rf $MAGENTO_ROOT/var/cache
    gosu daemon mkdir $MAGENTO_ROOT/var/cache
    chmod 775 $MAGENTO_ROOT/var/cache
    chown -R daemon: $MAGENTO_ROOT/var/cache
    chown -R daemon: $MAGENTO_ROOT/generated
fi

printf "${COLOR_SUCCESS}                                                                                            ${NC}\n"
printf "${COLOR_SUCCESS}    |======================================================================                 ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                                       ${NC}\n"
printf "${COLOR_SUCCESS}    |               DOCKER MAGENTO TO HIPAY $ENVIRONMENT IS UP                              ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                                       ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL FRONT       : http://${MAGENTO_HOST}:${MAGENTO_EXTERNAL_HTTP_PORT_NUMBER}       ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL BACK        : http://${MAGENTO_HOST}:${MAGENTO_EXTERNAL_HTTP_PORT_NUMBER}/admin ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                                       ${NC}\n"
printf "${COLOR_SUCCESS}    |   PHP VERSION     : $(php -r 'echo PHP_VERSION;')                                     ${NC}\n"
printf "${COLOR_SUCCESS}    |   MAGENTO VERSION : $(grep -Po '"version": "\K.*(?=")' $MAGENTO_ROOT/composer.json)   ${NC}\n"
printf "${COLOR_SUCCESS}    |======================================================================                 ${NC}\n"

chmod -R a+rw $MAGENTO_ROOT
exec "$@"