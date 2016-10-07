FROM sirateck/devops-magento2:latest

MAINTAINER support Fullservice <support.tpp@hipay.com>

RUN apt-get update \ 
	&& apt-get install -y ssmtp \
		&& echo "sendmail_path = /usr/sbin/ssmtp -t" > /usr/local/etc/php/conf.d/sendmail.ini \
			&& echo "mailhub=smtp:1025\nUseTLS=NO\nFromLineOverride=YES" > /etc/ssmtp/ssmtp.conf \
				&& sed -i "s/apapche2/apache2/" /etc/apache2/site-available/000-default.conf \
					&& sed -i "s/LogLevel debug ssl:warn/ /" /etc/apache2/site-available/000-default.conf \
						&& sed -i "s/php_admin_value error_reporting 30719/ /" /etc/apache2/site-available/000-default.conf \
							&& cp -Rf /etc/apache2/site-available/* /etc/apache2/sites-available \
								&& cp -Rf /etc/apache2/site-available/* /etc/apache2/sites-enabled

