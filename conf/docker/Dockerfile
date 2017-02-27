FROM hipay/hipay-magento2:latest

MAINTAINER Support Fullservice <support.tpp@hipay.com>

RUN apt-get update \ 
	&& apt-get install -y ssmtp \
		&& echo "sendmail_path = /usr/sbin/ssmtp -t" > /usr/local/etc/php/conf.d/sendmail.ini \
			&& echo "mailhub=smtp:1025\nUseTLS=NO\nFromLineOverride=YES" > /etc/ssmtp/ssmtp.conf \
			&& cp -Rf /etc/apache2/site-available/* /etc/apache2/sites-available \
            && cp -Rf /etc/apache2/site-available/* /etc/apache2/sites-enabled

# Override parent entrypoint to add custom configuration
COPY ./bin/magento2-start-override /usr/local/bin/
RUN chmod u+x /usr/local/bin/magento2-start-override

WORKDIR tmp

# Magerun installation
RUN curl -O  https://files.magerun.net/n98-magerun2.phar && \
    chmod +x ./n98-magerun2.phar && \
    cp ./n98-magerun2.phar /usr/local/bin/ && \
    rm ./n98-magerun2.phar

WORKDIR /var/www/html/magento2/

VOLUME /var/www/html/magento2/

ENTRYPOINT ["magento2-start-override"]




