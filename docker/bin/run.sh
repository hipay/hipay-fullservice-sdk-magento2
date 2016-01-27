#!/bin/sh
set -e

if [ $MAGE_DB_HOST = "localhost" ] || [ $MAGE_DB_HOST = "127.0.0.1" ]; then
	echo "\n-- Start Local MySQL server ...";
	service mysql start
	if [ $MAGE_DB_PASSWORD != "" ] && [ $MAGE_DB_NAME != "" ]  && [ $MAGE_DB_USER != "" ]; then
		echo "\n* Create Database $MAGE_DB_NAME  ...";
		mysql -h $MAGE_DB_HOST -u root --execute="CREATE DATABASE $MAGE_DB_NAME;";
		echo "\n* Create User $MAGE_DB_USER  and Grant ALL privileges on databse $MAGE_DB_NAME  ...";
		mysql -h $MAGE_DB_HOST -u root --execute="CREATE USER '$MAGE_DB_USER'@'localhost' IDENTIFIED BY '$MAGE_DB_PASSWORD';";
		mysql -h $MAGE_DB_HOST -u root --execute="GRANT ALL ON $MAGE_DB_NAME.* to $MAGE_DB_USER@'localhost' IDENTIFIED BY '$MAGE_DB_PASSWORD'; ";
		mysql -h $MAGE_DB_HOST -u root --execute="GRANT ALL ON $MAGE_DB_NAME.* to $MAGE_DB_USER@'%' IDENTIFIED BY '$MAGE_DB_PASSWORD'; ";
		echo "\n* Flush privileges ...";
		mysql -h $MAGE_DB_HOST -u root --execute="flush privileges; " 2> /dev/null;
	fi
fi

if [ ! -f /var/www/html/magento2/app/etc/config.php ] && [ ! -f /var/www/html/magento2/app/etc/env.php ]; then
	echo "\n* ./app/etc/config.php and ./app/etc/env.php not found. So, this magento isn't installed."
	if [ $MAGE_INSTALL = 1 ]; then
		echo "\n* Start Magento2 Command line intallation ..."
		MAGE_CLEANUP_DATABASE_CMD=""
		MAGE_INSTALL_SAMPLE_DTA_CMD=""
		if [ $MAGE_CLEANUP_DATABASE = 1 ];  then
			MAGE_CLEANUP_DATABASE_CMD="--cleanup-databse "
		fi
		
		if [ $MAGE_INSTALL_SAMPLE_DATA = 1 ]; then
			MAGE_INSTALL_SAMPLE_DTA_CMD="--use-sample-data "
		fi
		
		echo "\n* Run install command: "
		echo "\n 		su magento2 -c 'bin/magento setup:install'  \\ "
		echo "					'--db-host=$MAGE_DB_HOST' \\ "
		echo "					'--db-name=$MAGE_DB_NAME' \\"
		echo "					'--db-user=$MAGE_DB_USER' \\"
		echo "					'--db-passsword=$MAGE_DB_PASSWORD' \\"
		echo "					'--base-url=$MAGE_BASE_URL' \\"
		echo " 					'--admin-firstname=$MAGE_ADMIN_FIRSTNAME'  \\"
		echo " 					'--admin-lastname=$MAGE_ADMIN_LASTNAME'  \\"
		echo " 					'--admin-email=$MAGE_ADMIN_EMAIL'  \\"
		echo " 					'--admin-user=$MAGE_ADMIN_USER'  \\"
		echo " 					'--admin-password=$MAGE_ADMIN_PWD'  \\"
		echo " 					'--use-rewrites=$MAGE_USE_REWRITES'  \\"
		echo " 					'--backend-frontname=$MAGE_BACKEND_FRONTNAME'  \\"
		echo " 					'$MAGE_CLEANUP_DATABASE_CMD' \\ "
		echo " 					'$MAGE_INSTALL_SAMPLE_DTA_CMD'  "
		su magento2 -c  'bin/magento setup:install  --db-host=$MAGE_DB_HOST --db-name=$MAGE_DB_NAME  --db-user=$MAGE_DB_USER  --db-password=$MAGE_DB_PASSWORD  --base-url=$MAGE_BASE_URL --backend-frontname=$MAGE_BACKEND_FRONTNAME --admin-firstname=$MAGE_ADMIN_FIRSTNAME --admin-lastname=$MAGE_ADMIN_LASTNAME --admin-email=$MAGE_ADMIN_EMAIL --admin-user=$MAGE_ADMIN_USER --admin-password=$MAGE_ADMIN_PWD  --use-rewrites=$MAGE_USE_REWRITES $MAGE_CLEANUP_DATABASE_CMD $MAGE_INSTALL_SAMPLE_DTA_CMD' 
		
		echo "\n* Reindex all indexes ..."
		su magento2 -c 'bin/magento indexer:reindex'
	fi
else
	echo "\n* Magento is already installed."
fi

if [ $HIPAY_INSTALL_MODULE = 1 ]; then

	
	echo "\n* Set minimum-statility to 'dev' in composer.json "
	su magento2 -c 'sed -i -e"s/\"minimum-stability\": \"alpha\"/\"minimum-stability\": \"dev\"/g" composer.json'
	
	echo "\n* Set Developer mode in .htaccess file "
	su magento2 -c 'sed -i -e"s/#   SetEnv MAGE_MODE developer/   SetEnv MAGE_MODE developer/g" .htaccess'
	
	# Because now, we call composer with magento2 user
	# We must copy auth.json to magento2 user home directory
	echo "Copy /root/.composer/auth.json to /home/magento2/.composer/"
	if [ ! -d /home/magento/.composer ]; then
		echo "Create .composer directory in home directory"
		mkdir /home/magento2/.composer
	fi
	cp /root/.composer/auth.json /home/magento2/.composer/
	chown -R magento2:magento2 /home/magento2/.composer/
	chmod -R 770 /home/magento2/.composer/
	
	echo "\n * Add modules repositories to composer";
	echo "composer config repositories.1 vcs git@github.com:hipay/hipay-fullservice-sdk-magento2.git"
	su magento2 -c "composer config repositories.1 vcs git@github.com:hipay/hipay-fullservice-sdk-magento2.git"
	echo "composer config repositories.2 vcs git@github.com:hipay/hipay-fullservice-sdk-php.git"
	su magento2 -c "composer config repositories.2 vcs git@github.com:hipay/hipay-fullservice-sdk-php.git"
	
	echo "\n* Run composer require"
	echo "composer require hipay/hipay-fullservice-sdk-magento2 dev-develop"
	su magento2 -c "composer require hipay/hipay-fullservice-sdk-magento2 dev-develop"
	
	echo "\n* Enable Module Hipay Magento ..."
	su magento2 -c 'bin/magento module:enable --clear-static-content Hipay_Fullservice'
	echo "\n* Run setup:upgrade ..."
	su magento2 -c 'bin/magento setup:upgrade'
	echo "\n* Disable all cache types"
	su magento2 -c 'bin/magento cache:disable'
	echo "\n* Apply patch to prevent bad path due to symlink when static content is deploying  ..."
	su magento2 -c 'cp -f /home/magento2/hipay-fullservice-sdk-magento2/docker/patch/Read.php vendor/magento/framework/Filesystem/Directory/Read.php'
	echo "\n* Deploy static content ..."
	su magento2 -c 'bin/magento setup:static-content:deploy'
	
	echo "\n* Remove module copied by composer and create symlink from shared volume to app/code/Hipay/Fullservice/ ..."
	su magento2 -c "rm -r app/code/Hipay/Magento"
	su magento2 -c "ln -s /home/magento2/hipay-fullservice-sdk-magento2/src app/code/Hipay/Fullservice"
fi

# We need to remove the pid file or Apache won't start after being stopped
if [ -f /var/run/apache2/apache2.pid  ]; then
    rm -f /var/run/apache2/apache2.pid
fi

echo "\n* Start Apache in foreground";
exec apache2 -DFOREGROUND


