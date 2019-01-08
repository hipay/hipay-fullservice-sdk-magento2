FROM hipay/hipay-magento2:2.1.16

MAINTAINER Support Fullservice <support.tpp@hipay.com>

RUN apt-get update \ 
	&& apt-get install -y ssmtp vim wget ssh \
		&& echo "sendmail_path = /usr/sbin/ssmtp -t" > /usr/local/etc/php/conf.d/sendmail.ini \
			&& echo "mailhub=smtp:1025\nUseTLS=NO\nFromLineOverride=YES" > /etc/ssmtp/ssmtp.conf \
			&& cp -Rf /etc/apache2/site-available/* /etc/apache2/sites-available \
            && cp -Rf /etc/apache2/site-available/* /etc/apache2/sites-enabled

# Override parent entrypoint to add custom configuration
COPY ./bin/docker/images/default/entrypoint.sh /usr/local/bin/
RUN chmod u+x /usr/local/bin/entrypoint.sh

WORKDIR tmp

# Magerun installation
RUN curl -O  https://files.magerun.net/n98-magerun2.phar && \
    chmod +x ./n98-magerun2.phar && \
    cp ./n98-magerun2.phar /usr/local/bin/ && \
    rm ./n98-magerun2.phar

RUN wget https://phar.phpunit.de/phpunit-6.2.phar && \
    chmod +x phpunit-6.2.phar && \
    mv -f phpunit-6.2.phar /usr/local/bin/phpunit  && \
    phpunit --version

WORKDIR /var/www/html/magento2/

VOLUME /var/www/html/magento2/

ENTRYPOINT ["entrypoint.sh"]





