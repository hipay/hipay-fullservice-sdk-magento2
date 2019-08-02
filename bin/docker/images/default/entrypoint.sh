#!/bin/bash

set +e
COLOR_SUCCESS='\033[0;32m'
NC='\033[0m'
PREFIX_STORE1=$RANDOM
ENV_DEVELOPMENT="development"
ENV_STAGE="stage"
ENV_PROD="production"
NEED_SETUP_CONFIG=0

#==========================================
# CHECK IF MAGENTO IS INSTALLED
#==========================================
if [ ! -f /var/www/html/magento2/app/etc/config.php ] && [ ! -f /var/www/html/magento2/app/etc/env.php ]; then
    NEED_SETUP_CONFIG="1"

    #==========================================
    # VCS AUTHENTICATION
    #==========================================
    printf "Set composer http-basic $GITLAB_API_TOKEN"
    gosu magento2 composer config http-basic.gitlab.hipay.org "x-access-token" "$GITLAB_API_TOKEN"

    printf "Set composer GITHUB http-basic $GITHUB_API_TOKEN"
    gosu magento2 composer config -g github-oauth.github.com $GITHUB_API_TOKEN
fi

#==========================================
# PARENT ENTRYPOINT
#==========================================
/bin/bash /usr/local/bin/magento2-start "$@ --no-exec-apache"

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

        echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini
        echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini
        echo "xdebug.remote_connect_back=On" >> /usr/local/etc/php/conf.d/xdebug.ini
        echo "xdebug.idekey=magento2" >> /usr/local/etc/php/conf.d/xdebug.ini
    fi

    #==========================================
    # MAIL CONFIGURATION
    #==========================================
    echo "mailhub=$SMTP_LINK\nUseTLS=NO\nFromLineOverride=YES" > /etc/ssmtp/ssmtp.conf \

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
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/hipay_cc/cctypes "VI,MC,AE,CB,MI"

    if [ "$ENVIRONMENT" = "$ENV_PROD" ];then
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set hipay/configurations/send_notification_url 1
    fi

    if [ "$ENVIRONMENT" != "$ENV_DEVELOPMENT" ];then
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set hipay/hipay_credentials/hashing_algorithm_test 'SHA512'
    fi

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         ACTIVATE PAYMENT METHODS        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    methods=$(echo $ACTIVE_METHODS| tr "," "\n")
    for code in $methods
    do
        printf "\n"
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/$code/active 1
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/$code/debug 1
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/$code/is_test_mode 1
        printf "${COLOR_SUCCESS} Method $code is activated in test mode ${NC}\n"
    done


    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         UPDATE SEQUENCE ORDER           ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    n98-magerun2.phar db:query "INSERT INTO mage_sequence_order_1 values ('$PREFIX_STORE1')"
    printf "${COLOR_SUCCESS} Order sequence is $PREFIX_STORE1${NC}\n"

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         LINK WITH HIPAY'S SDK PHP        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    if [ -f /var/www/html/magento2/sdk/hipay-fullservice-sdk-php/composer.json ];then
       cd /var/www/html/magento2/vendor/hipay/
       rm -Rf hipay-fullservice-sdk-php
       ln -sf /var/www/html/magento2/sdk/hipay-fullservice-sdk-php hipay-fullservice-sdk-php
       printf "${COLOR_SUCCESS} HiPay's SDK php is now linked ${NC}\n"
    fi

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}              FOLDER CACHE               ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    rm -Rf /var/www/html/magento2/var/cache
    gosu magento2 mkdir /var/www/html/magento2/var/cache
    chmod 775 /var/www/html/magento2/var/cache
    chown -R magento2:magento2 /var/www/html/magento2/var/cache
    chown -R magento2:www-data /var/www/html/magento2/generated
fi

printf "${COLOR_SUCCESS}                                                                            ${NC}\n"
printf "${COLOR_SUCCESS}    |======================================================================${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                      ${NC}\n"
printf "${COLOR_SUCCESS}    |               DOCKER MAGENTO TO HIPAY $ENVIRONMENT IS UP             ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                      ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL FRONT       : $MAGE_BASE_URL                                   ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL BACK        : $MAGE_BASE_URLadmin                             ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL MAIL CATCHER: $MAGENTO_URL:1095/                               ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                      ${NC}\n"
printf "${COLOR_SUCCESS}    |   PHP VERSION     : $PHP_VERSION                                     ${NC}\n"
printf "${COLOR_SUCCESS}    |   MAGENTO VERSION : $MAGE_VERSION                                    ${NC}\n"
printf "${COLOR_SUCCESS}    |======================================================================${NC}\n"

exec apache2 -DFOREGROUND
