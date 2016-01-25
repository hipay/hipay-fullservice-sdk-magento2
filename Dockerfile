FROM php:5.5-apache

MAINTAINER Kassim Belghait <kassim@sirateck.com>

#================================================
# Customize sources for apt-get
#================================================
RUN  echo "deb http://repo.mysql.com/apt/debian/ jessie mysql-5.6\n" > /etc/apt/sources.list.d/mysql.list
RUN apt-key adv --keyserver pgp.mit.edu --recv-keys 5072E1F5 

#=======================
# Environment variables for Mysql
#=======================
ENV DB_ROOT_PASSWD magento2

# Prepare MySQL non-interactive installation
ENV DEBIAN_FRONTEND noninteractive

# Set Mysql default root password
RUN echo mysql-server-5.6 mysql-server/root_password password $DB_ROOT_PASSWD | debconf-set-selections
RUN echo mysql-server-5.6 mysql-server/root_password_again password $DB_ROOT_PASSWD | debconf-set-selections
#======================
# Install packages needed by php's extensions
# PHP image already install following extensions:
#	- openssl, curl, zlib,recode,realine,mysqlnd
#======================
RUN apt-get update \
	&& apt-get -qqy --no-install-recommends install \
		git \
	 	libmcrypt-dev \
		libjpeg62-turbo-dev \
		libpng12-dev \
		libfreetype6-dev \
		libxslt1-dev \
		libicu-dev \
		mysql-client \
		mysql-server \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-configure zip --enable-zip \
    && docker-php-ext-install mcrypt gd intl mbstring soap xsl zip pdo_mysql 
    
 #==============================
 # Install composer
 #==============================
 RUN curl -sS https://getcomposer.org/installer | php -- --filename=composer -- --install-dir=/usr/local/bin

#===============
# PHP configuration
#===============
ENV PHP_TIMEZONE Europe/Paris
COPY docker/conf/php.ini /usr/local/etc/php/
RUN echo "date.timezone = '$PHP_TIMEZONE'" >> /usr/local/etc/php/php.ini \
	&& ln -s /usr/local/bin/php /usr/bin/php

#====================================
# Apache configuration
# Active mod rewrite
#====================================
RUN a2enmod rewrite

#=================
# Mysql Configuration
#=================
RUN sed -i -e"s/^bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/" /etc/mysql/my.cnf
EXPOSE 3306

WORKDIR /var/www/html/magento2

#===========================
# Copy composer config
# Copy auth.json (required repo.magento.com) in ROOT directory
# Copy composer.json
#===========================
COPY docker/conf/auth.json /root/.composer/
COPY docker/conf/composer.json.dist composer.json

# Get Magento CE metapackage and sample data
RUN composer install

#=============================
# Create Magento2 user and put it in web server's group
# Set permissions ownership
#============================
RUN adduser --disabled-password --gecos "" magento2
RUN usermod -a -G www-data magento2
RUN usermod -a -G magento2 www-data
RUN chown -R magento2:www-data .
RUN find . -type d -exec chmod 770 {} \; \
	&& find . -type f -exec chmod 660 {} \; \
	&& chmod u+x bin/magento

#==========================
# ENV variables used by magento installation
#==========================
ENV MAGE_INSTALL  1
ENV MAGE_INSTALL_SAMPLE_DATA 1
ENV MAGE_ADMIN_FIRSTNAME John
ENV MAGE_ADMIN_LASTNAME Doe
ENV MAGE_ADMIN_EMAIL john.doe@yopmail.com
ENV MAGE_ADMIN_USER admin
ENV MAGE_ADMIN_PWD admin123
ENV MAGE_BASE_URL http://127.0.0.1:8080/magento2
ENV MAGE_BASE_URL_SECURE https://127.0.0.1:8080/magento2
ENV MAGE_BACKEND_FRONTNAME admin
ENV MAGE_DB_HOST 127.0.0.1
ENV MAGE_DB_NAME magento2
ENV MAGE_DB_USER magento2
ENV MAGE_DB_PASSWORD magento2
ENV MAGE_DB_PREFIX mage_
ENV MAGE_LANGUAGE fr_fr
ENV MAGE_CURRENCY EUR
ENV MAGE_TIMEZONE Europe/Paris
ENV MAGE_USE_REWRITES 1
ENV MAGE_USE_SECURE 0
ENV MAGE_USE_SECURE_ADMIN 0
ENV MAGE_ADMIN_USE_SECURITY_KEY 1
ENV MAGE_SESSION_SAVE files
ENV MAGE_KEY 0
ENV MAGE_CLEANUP_DATABASE 1
ENV MAGE_DB_INIT_STATEMENTS  SET NAMES utf8;
ENV MAGE_SALES_ORDER_INCREMENT_PREFIX 0


ENV HIPAY_INSTALL_MODULE 1


#CMD ["apache2-foreground"]
COPY docker/bin/run.sh /tmp/
ENTRYPOINT ["/tmp/run.sh"]





