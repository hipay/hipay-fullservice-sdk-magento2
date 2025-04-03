#!/bin/bash

set -e
COLOR_SUCCESS='\033[0;32m'
NC='\033[0m'
PREFIX_STORE1=$RANDOM$RANDOM
ENV_DEVELOPMENT="development"
ENV_STAGE="stage"
ENV_PROD="production"
NEED_SETUP_CONFIG=0
MAGENTO_ROOT=/bitnami/magento/
MAGENTO_DIR_USER=daemon

printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
printf "\n${COLOR_SUCCESS}           DATABASE CONNECTION           ${NC}\n"
printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
countDB=0
statusDB=0
# Wait max 1min
until [ "$countDB" -gt 5 ]; do
    if mysql -u $MAGENTO_DATABASE_USER -h $MAGENTO_DATABASE_HOST -P $MAGENTO_DATABASE_PORT_NUMBER -D $MAGENTO_DATABASE_NAME -e "SHOW TABLES;" >/dev/null; then
        statusDB=1
        printf "Database is ready !\n"
        break
    else
        countDB=$((countDB + 1))
        if [ "$countDB" -le 5 ]; then
            sleep 10
        fi
    fi
done
if [ "$statusDB" -ne 1 ]; then
    printf "Database is not ready !"
    exit 1
fi

printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
printf "\n${COLOR_SUCCESS}        ELASTICSEARCH CONNECTION         ${NC}\n"
printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
countES=0
statusES=0
# Wait max 5min ( Mac os )
until [ "$countES" -gt 30 ]; do
    if curl $ELASTICSEARCH_HOST:$ELASTICSEARCH_PORT_NUMBER; then
        statusES=1
        printf "ElasticSearch is ready !\n"
        break
    else
        countES=$((countES + 1))
        if [ "$countES" -le 5 ]; then
            sleep 10
        fi
    fi
done
if [ "$statusES" -ne 1 ]; then
    printf "Database is not ready !"
    exit 1
fi

cd $MAGENTO_ROOT

if [ ! -f $MAGENTO_ROOT/app/etc/config.php ] && [ ! -f $MAGENTO_ROOT/app/etc/env.php ]; then
    NEED_SETUP_CONFIG="1"
fi

export COMPOSER_MEMORY_LIMIT=-1

#==========================================
# PARENT ENTRYPOINT
#==========================================
touch /bitnami/magento/.user.ini
mkdir -p /bitnami/magento/pub
touch /bitnami/magento/pub/.user.ini

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
        printf "\n${COLOR_SUCCESS}     CONFIGURE XDEBUG $ENVIRONMENT          ${NC}\n"
        printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

        xdebugFile=/opt/bitnami/php/etc/conf.d/xdebug.ini

        echo 'zend_extension=xdebug' >>$xdebugFile

        echo "xdebug.mode=debug" >>$xdebugFile
        echo "xdebug.idekey=PHPSTORM" >>$xdebugFile

        echo "xdebug.remote_enable=on" >>$xdebugFile
        echo "xdebug.remote_autostart=off" >>$xdebugFile
    fi

    #==========================================
    # VCS AUTHENTICATION
    #==========================================
    printf "Set composer http-basic $GITLAB_API_TOKEN\n"
    gosu $MAGENTO_DIR_USER composer config http-basic.gitlab.hipay.org "x-access-token" "$GITLAB_API_TOKEN"

    printf "Set composer GITHUB http-basic $GITHUB_API_TOKEN\n"
    gosu $MAGENTO_DIR_USER composer config -g github-oauth.github.com $GITHUB_API_TOKEN

    gosu $MAGENTO_DIR_USER composer config repositories.magento composer https://repo.magento.com

    # Transform string vars to array
    OLDIFS=$IFS
    IFS=','
    read -r -a CUSTOM_REPOSITORIES <<<"$CUSTOM_REPOSITORIES"
    read -r -a CUSTOM_PACKAGES <<<"$CUSTOM_PACKAGES"
    read -r -a CUSTOM_MODULES <<<"$CUSTOM_MODULES"
    IFS=$OLDIFS

    # Add custom repositories to composer config
    if [ ! ${#CUSTOM_REPOSITORIES[*]} = 0 ]; then
        cnt_repo=$((${#CUSTOM_REPOSITORIES[*]} - 1))
        for i in $(seq 0 $cnt_repo); do
            j=$(($i + 100)) # increase j to not erase magento repo
            repo="$(echo ${CUSTOM_REPOSITORIES[$i]} | sed 's/^[ \t]*//;s/[ \t]*$//')"
            printf "\nAdd Repository $repo to composer.json"
            gosu $MAGENTO_DIR_USER composer config repositories.$j $repo
        done
    fi

    # Add required packages
    if [ ! ${#CUSTOM_PACKAGES[*]} = 0 ]; then
        cnt_package=$((${#CUSTOM_PACKAGES[*]} - 1))
        for i in $(seq 0 $cnt_package); do
            package=$(echo ${CUSTOM_PACKAGES[$i]} | sed 's/^[ \t]*//;s/[ \t]*$//')
            printf "\nInstall package $package"
            gosu $MAGENTO_DIR_USER composer require $package
        done
    fi

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALLING HIPAY MODULE             ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    su -c 'composer require hipay/hipay-fullservice-sdk-php magento/module-bundle-sample-data magento/module-theme-sample-data magento/module-widget-sample-data magento/module-catalog-sample-data magento/module-cms-sample-data magento/module-tax-sample-data -n && \
      magento module:enable HiPay_FullserviceMagento && \
      magento module:enable Magento_BundleSampleData Magento_ThemeSampleData Magento_CatalogSampleData Magento_CmsSampleData Magento_TaxSampleData && \
      magento setup:upgrade && \
      magento setup:di:compile && \
      magento setup:static-content:deploy -f && \
      magento cache:flush' $MAGENTO_DIR_USER -s /bin/bash

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALLING HIPAY MULTISTORE MODULE             ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    su -c 'magento module:enable HiPay_MultiStores && \
       magento setup:upgrade && \
       magento setup:di:compile && \
       magento setup:static-content:deploy -f && \
       magento cache:flush' $MAGENTO_DIR_USER -s /bin/bash

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
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set hipay/hipay_credentials/hashing_algorithm_test 'SHA512'
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set hipay/configurations/send_notification_url 0

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         ACTIVATE PAYMENT METHODS        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    methods=$(echo $ACTIVE_METHODS | tr "," "\n")
    for code in $methods; do
        printf "\n"
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/$code/active 1
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/$code/debug 1
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/$code/is_test_mode 1
        n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set payment/$code/env stage
        printf "${COLOR_SUCCESS} Method $code is activated in test mode ${NC}\n"
    done

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         UPDATE SEQUENCE ORDER           ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    n98-magerun2.phar db:query "INSERT INTO ${MAGE_DB_PREFIX}sequence_order_1 values ('$PREFIX_STORE1')"
    printf "${COLOR_SUCCESS} Order sequence is $PREFIX_STORE1${NC}\n"

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         FINAL MAGENTO CONFIG        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set currency/options/base EUR
    n98-magerun2.phar -q --skip-root-check --root-dir="$MAGENTO_ROOT" config:store:set currency/options/default EUR
    printf "${COLOR_SUCCESS} Default currency set to EUR${NC}\n"

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
    gosu $MAGENTO_DIR_USER mkdir $MAGENTO_ROOT/var/cache
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
