#!/bin/bash
set -e

COLOR_SUCCESS='\033[0;32m'
NC='\033[0m'
MAGENTO_ROOT=/var/www/html
TMP_MAGENTO_DIR=/var/www/html-magento
MAGENTO_DIR_USER=www-data
HOST_UID="${HOST_UID:-1000}"
HOST_GID="${HOST_GID:-1000}"
NEED_SETUP_CONFIG=0


export COMPOSER_MEMORY_LIMIT=-1
export COMPOSER_CACHE_DIR=/var/www/.composer/cache

# Composer cache
mkdir -p /var/www/.composer/cache
chown -R $MAGENTO_DIR_USER:$MAGENTO_DIR_USER /var/www/.composer
chmod -R 775 /var/www/.composer

# Wait for DB
echo -e "${COLOR_SUCCESS} Checking DB connection...${NC}"
countDB=0
until mysql -u "$MAGENTO_DATABASE_USER" -p"$MAGENTO_DATABASE_PASSWORD" -h "$MAGENTO_DATABASE_HOST" -P "$MAGENTO_DATABASE_PORT_NUMBER" -D "$MAGENTO_DATABASE_NAME" -e "SHOW TABLES;" >/dev/null 2>&1; do
    countDB=$((countDB + 1))
    if [ "$countDB" -gt 20 ]; then
        echo " Database not ready!"
        exit 1
    fi
    sleep 5
done
echo -e "${COLOR_SUCCESS} Database ready${NC}"

echo -e "${COLOR_SUCCESS}🔍 Checking OpenSearch...${NC}"

# Sanity check: vars obligatoires
: "${OPENSEARCH_HOST:?OPENSEARCH_HOST non défini}"
: "${OPENSEARCH_PORT_NUMBER:?OPENSEARCH_PORT_NUMBER non défini}"

MAX_WAIT_SECONDS="${OPENSEARCH_MAX_WAIT_SECONDS:-600}"
ELAPSED=0

while true; do
  RESP="$(curl -sS -m 2 -w ' HTTP_CODE:%{http_code}' "http://${OPENSEARCH_HOST}:${OPENSEARCH_PORT_NUMBER}/_cluster/health" || true)"

  CODE="${RESP##*HTTP_CODE:}"
  BODY="${RESP% HTTP_CODE:*}"

  if [ $((ELAPSED % 5)) -eq 0 ]; then
    echo "OpenSearch health code=${CODE} body=${BODY}"
  fi

  if printf '%s' "$BODY" | grep -q '"status"'; then
    echo -e "${COLOR_SUCCESS}✅ OpenSearch ready${NC}"
    break
  fi

  ELAPSED=$((ELAPSED+1))
  if [ "$ELAPSED" -ge "$MAX_WAIT_SECONDS" ]; then
    echo "❌ OpenSearch not ready after ${MAX_WAIT_SECONDS}s! Host=${OPENSEARCH_HOST} Port=${OPENSEARCH_PORT_NUMBER}"
    exit 1
  fi
  sleep 1
done

if [ ! -f $MAGENTO_ROOT/app/etc/config.php ] && [ ! -f $MAGENTO_ROOT/app/etc/env.php ]; then
    NEED_SETUP_CONFIG="1"
fi

if [ "$NEED_SETUP_CONFIG" -eq 1 ]; then
    echo -e "${COLOR_SUCCESS} Installing Magento...${NC}"

    if [ ! -f "$MAGENTO_ROOT/composer.json" ] || ! grep -q '"name": *"magento/project-' "$MAGENTO_ROOT/composer.json"; then

        echo -e "${COLOR_SUCCESS}Installation dans $TMP_MAGENTO_DIR...${NC}"
        mkdir -p $TMP_MAGENTO_DIR
        chown -R $MAGENTO_DIR_USER:$MAGENTO_DIR_USER $TMP_MAGENTO_DIR

        su -s /bin/bash -c "composer create-project --repository=https://repo.magento.com/ magento/project-community-edition=$MAGENTO_VERSION $TMP_MAGENTO_DIR" $MAGENTO_DIR_USER

        echo -e "${COLOR_SUCCESS} Copie des fichiers Magento vers $MAGENTO_ROOT...${NC}"
        rsync -a --remove-source-files $TMP_MAGENTO_DIR/ $MAGENTO_ROOT/
        rm -rf $TMP_MAGENTO_DIR

        chmod +x $MAGENTO_ROOT/bin/magento
        echo -e "${COLOR_SUCCESS} Correction des permissions...${NC}"
        mkdir -p $MAGENTO_ROOT/var/log
        chown -R $MAGENTO_DIR_USER:$MAGENTO_DIR_USER $MAGENTO_ROOT
        find $MAGENTO_ROOT -type d -exec chmod 755 {} \;
        find $MAGENTO_ROOT -type f -exec chmod 644 {} \;
    else
        echo -e "${COLOR_SUCCESS} Magento déjà présent, on continue...${NC}"
    fi

    echo -e "${COLOR_SUCCESS} Running setup:install...${NC}"

    rm -f "$MAGENTO_ROOT/app/etc/config.php"

    su -s /bin/bash -c "chmod +x $MAGENTO_ROOT/bin/magento" $MAGENTO_DIR_USER

   #==========================================
   # VCS AUTHENTICATION
   #==========================================
   printf "Set composer http-basic $GITLAB_API_TOKEN\n"
   gosu $MAGENTO_DIR_USER composer config http-basic.gitlab.hipay.org "x-access-token" "$GITLAB_API_TOKEN"

   printf "Set composer GITHUB http-basic $GITHUB_API_TOKEN\n"
   gosu $MAGENTO_DIR_USER composer config -g github-oauth.github.com $GITHUB_API_TOKEN

   gosu $MAGENTO_DIR_USER composer config repositories.magento composer https://repo.magento.com

   OLDIFS=$IFS
   IFS=','
   read -r -a CUSTOM_REPOSITORIES <<<"$CUSTOM_REPOSITORIES"
   read -r -a CUSTOM_PACKAGES <<<"$CUSTOM_PACKAGES"
   read -r -a CUSTOM_MODULES <<<"$CUSTOM_MODULES"
   IFS=$OLDIFS

   # Add custom repositories
   if [ ! ${#CUSTOM_REPOSITORIES[*]} = 0 ]; then
       cnt_repo=$((${#CUSTOM_REPOSITORIES[*]} - 1))
       for i in $(seq 0 $cnt_repo); do
           j=$(($i + 100))
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
           gosu $MAGENTO_DIR_USER composer require $package -n
       done
   fi

   CUSTOM_MODULES_TO_ENABLE=""
   if [ ! ${#CUSTOM_MODULES[*]} = 0 ]; then
       cnt_module=$((${#CUSTOM_MODULES[*]} - 1))
       for i in $(seq 0 $cnt_module); do
           module=$(echo ${CUSTOM_MODULES[$i]} | sed 's/^[ \t]*//;s/[ \t]*$//')
           CUSTOM_MODULES_TO_ENABLE="$CUSTOM_MODULES_TO_ENABLE $module"
       done
   fi

   gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && \
       bin/magento setup:install \
           --base-url=http://${MAGENTO_HOST}:${MAGENTO_EXTERNAL_HTTP_PORT_NUMBER}/ \
           --use-secure=1 \
           --use-secure-admin=1 \
           --db-host=$MAGENTO_DATABASE_HOST \
           --db-name=$MAGENTO_DATABASE_NAME \
           --db-user=$MAGENTO_DATABASE_USER \
           --db-password=$MAGENTO_DATABASE_PASSWORD \
           --backend-frontname=admin \
           --admin-firstname=Admin \
           --admin-lastname=User \
           --admin-email=$MAGENTO_EMAIL \
           --admin-user=$MAGENTO_USERNAME \
           --admin-password=$MAGENTO_PASSWORD \
           --language=en_US \
           --currency=EUR \
           --timezone=Europe/Paris \
           --use-rewrites=1 \
           --search-engine=opensearch \
           --opensearch-host=${OPENSEARCH_HOST} \
           --opensearch-port=${OPENSEARCH_PORT_NUMBER}"


    echo -e "${COLOR_SUCCESS} Magento installé avec succès${NC}"

    echo -e "${COLOR_SUCCESS} Activation module HiPay...${NC}"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && \
        bin/magento module:enable HiPay_FullserviceMagento $CUSTOM_MODULES_TO_ENABLE "

    # =====================================================
    #           INSTALLING HIPAY MODULE (Sample Data)
    # =====================================================
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     INSTALLING HIPAY MODULE             ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && \
      composer require -n \
        magento/module-bundle-sample-data \
        magento/module-theme-sample-data \
        magento/module-widget-sample-data \
        magento/module-catalog-sample-data \
        magento/module-cms-sample-data \
        magento/module-tax-sample-data && \
      bin/magento module:enable \
        Magento_BundleSampleData \
        Magento_ThemeSampleData \
        Magento_CatalogSampleData \
        Magento_CmsSampleData \
        Magento_TaxSampleData && \
      bin/magento config:set dev/static/sign 0 && \
      bin/magento config:set twofactorauth/general/enable 0 && \
      bin/magento setup:static-content:deploy -f fr_FR en_US && \
      bin/magento setup:upgrade && \
      bin/magento setup:di:compile && \
      bin/magento index:reindex  && \
      bin/magento cache:flush"

    # =====================================================
    #           CONFIGURING HIPAY CREDENTIALS
    # =====================================================
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}     CONFIGURING HIPAY CREDENTIAL        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    echo -e "${COLOR_SUCCESS}== Dump variables HiPay ==${NC}"
    echo "HIPAY_API_USER_TEST=${HIPAY_API_USER_TEST}"
    echo "HIPAY_API_PASSWORD_TEST=${HIPAY_API_PASSWORD_TEST}"
    echo "HIPAY_SECRET_PASSPHRASE_TEST=${HIPAY_SECRET_PASSPHRASE_TEST}"
    echo "HIPAY_TOKENJS_USERNAME_TEST=${HIPAY_TOKENJS_USERNAME_TEST}"
    echo "HIPAY_TOKENJS_PUBLICKEY_TEST=${HIPAY_TOKENJS_PUBLICKEY_TEST}"
    echo "HIPAY_APPLEPAY_USERNAME_TEST=${HIPAY_APPLEPAY_USERNAME_TEST}"
    echo "HIPAY_APPLEPAY_PASSWORD_TEST=${HIPAY_APPLEPAY_PASSWORD_TEST}"
    echo "HIPAY_APPLEPAY_SECRET_PASSPHRASE_TEST=${HIPAY_APPLEPAY_SECRET_PASSPHRASE_TEST}"
    echo "HIPAY_APPLEPAY_TOKENJS_USERNAME_TEST=${HIPAY_APPLEPAY_TOKENJS_USERNAME_TEST}"
    echo "HIPAY_APPLEPAY_TOKENJS_PUBLICKEY_TEST=${HIPAY_APPLEPAY_TOKENJS_PUBLICKEY_TEST}"


    # === CONFIGURING HIPAY CREDENTIALS (bin/magento direct) ===
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials/api_username_test '$HIPAY_API_USER_TEST'"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials/api_password_test '$HIPAY_API_PASSWORD_TEST'"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials/secret_passphrase_test '$HIPAY_SECRET_PASSPHRASE_TEST'"

    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials_moto/api_username_test '$HIPAY_API_USER_TEST'"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials_moto/api_password_test '$HIPAY_API_PASSWORD_TEST'"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials_moto/secret_passphrase_test '$HIPAY_SECRET_PASSPHRASE_TEST'"

    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials_tokenjs/api_username_test '$HIPAY_TOKENJS_USERNAME_TEST'"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials_tokenjs/api_password_test '$HIPAY_TOKENJS_PUBLICKEY_TEST'"

    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials_applepay/api_username_test '$HIPAY_APPLEPAY_USERNAME_TEST'"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials_applepay/api_password_test '$HIPAY_APPLEPAY_PASSWORD_TEST'"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials_applepay/secret_passphrase_test '$HIPAY_APPLEPAY_SECRET_PASSPHRASE_TEST'"

    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials_applepay_tokenjs/api_username_test '$HIPAY_APPLEPAY_TOKENJS_USERNAME_TEST'"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials_applepay_tokenjs/api_password_test '$HIPAY_APPLEPAY_TOKENJS_PUBLICKEY_TEST'"

    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/hipay_credentials/hashing_algorithm_test 'SHA512'"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set hipay/configurations/send_notification_url 0"


    # =====================================================
    #           ACTIVATE PAYMENT METHODS
    # =====================================================
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         ACTIVATE PAYMENT METHODS        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    methods=$(echo "$ACTIVE_METHODS" | tr "," "\n")
    for code in $methods; do
        printf "\n"
        gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set payment/$code/active 1"
        gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set payment/$code/debug 1"
        gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set payment/$code/env stage"
        printf "${COLOR_SUCCESS} Method $code is activated in test mode ${NC}\n"
    done

    # =====================================================
    #           FINAL MAGENTO CONFIG
    # =====================================================
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}         FINAL MAGENTO CONFIG        ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set currency/options/base EUR"
    gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && bin/magento config:set currency/options/default EUR"

    printf "${COLOR_SUCCESS} Default currency set to EUR${NC}\n"

    PREFIX_STORE1=${PREFIX_STORE1:-$RANDOM$RANDOM}
    TABLE="${MAGE_DB_PREFIX}sequence_order_1"

    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"
    printf "\n${COLOR_SUCCESS}        UPDATE SEQUENCE ORDER            ${NC}\n"
    printf "\n${COLOR_SUCCESS} ======================================= ${NC}\n"

    mysql -h "$MAGENTO_DATABASE_HOST" -P "$MAGENTO_DATABASE_PORT_NUMBER" \
          -u "$MAGENTO_DATABASE_USER" -p"$MAGENTO_DATABASE_PASSWORD" \
          "$MAGENTO_DATABASE_NAME" \
          -e "INSERT INTO \`${TABLE}\` (sequence_value) VALUES (${PREFIX_STORE1});"

    printf "${COLOR_SUCCESS} Order sequence is ${PREFIX_STORE1}${NC}\n"

fi

echo -e "${COLOR_SUCCESS}🔧 Fix ownership (host user)${NC}"
getent group "$HOST_GID" >/dev/null 2>&1 || groupadd -g "$HOST_GID" hostgroup || true
id -u "$HOST_UID" >/dev/null 2>&1 || useradd -u "$HOST_UID" -g "$HOST_GID" -M -s /usr/sbin/nologin hostuser || true

chown -R "$HOST_UID:$HOST_GID" "$MAGENTO_ROOT"

RUNTIME_DIRS=(
  "$MAGENTO_ROOT/var"
  "$MAGENTO_ROOT/generated"
  "$MAGENTO_ROOT/pub/static"
  "$MAGENTO_ROOT/pub/media"
)

chown -R "$MAGENTO_DIR_USER:$MAGENTO_DIR_USER" "${RUNTIME_DIRS[@]}"

for d in "${RUNTIME_DIRS[@]}"; do
  find "$d" -type d -exec chmod 2775 {} \;
  find "$d" -type f -exec chmod 664 {} \;
  chgrp -R "$MAGENTO_DIR_USER" "$d"
done


echo -e "${COLOR_SUCCESS} Ownership & permissions fixed${NC}"

if [ -f "$MAGENTO_ROOT/auth.json" ]; then
  install -d -m 775 -o $MAGENTO_DIR_USER -g $MAGENTO_DIR_USER /var/www/.composer
  mv -f "$MAGENTO_ROOT/auth.json" /var/www/.composer/auth.json
  chown $MAGENTO_DIR_USER:$MAGENTO_DIR_USER /var/www/.composer/auth.json
  chmod 600 /var/www/.composer/auth.json
fi

gosu $MAGENTO_DIR_USER bash -lc "cd $MAGENTO_ROOT && \
      bin/magento cache:flush"

printf "${COLOR_SUCCESS}                                                                                            ${NC}\n"
printf "${COLOR_SUCCESS}    |======================================================================                 ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                                       ${NC}\n"
printf "${COLOR_SUCCESS}    |               DOCKER MAGENTO TO HIPAY ${ENVIRONMENT} IS UP                            ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                                       ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL FRONT       : http://${MAGENTO_HOST}:${MAGENTO_EXTERNAL_HTTP_PORT_NUMBER}       ${NC}\n"
printf "${COLOR_SUCCESS}    |   URL BACK        : http://${MAGENTO_HOST}:${MAGENTO_EXTERNAL_HTTP_PORT_NUMBER}/admin ${NC}\n"
printf "${COLOR_SUCCESS}    |                                                                                       ${NC}\n"
printf "${COLOR_SUCCESS}    |   PHP VERSION     : $(php -r 'echo PHP_VERSION;')                                     ${NC}\n"
printf "${COLOR_SUCCESS}    |   MAGENTO VERSION : $(grep -Po '"version": "\K.*(?=")' $MAGENTO_ROOT/composer.json)   ${NC}\n"
printf "${COLOR_SUCCESS}    |======================================================================                 ${NC}\n"

echo -e "${COLOR_SUCCESS} Magento + HiPay prêt !${NC}"


exec php-fpm -F
