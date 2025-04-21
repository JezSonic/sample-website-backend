FROM ubuntu:24.04
ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=UTC
RUN apt -y -q update \
	&& mkdir -p /etc/apt/keyrings \
	&& apt -y -q upgrade
RUN apt install -y gnupg software-properties-common curl ca-certificates unzip #unzip is for composer
RUN add-apt-repository -y ppa:ondrej/php
RUN apt -y -q update && apt install -y php8.4-cli php8.4-dev \
	php8.4-pgsql php8.4-gd \
	php8.4-curl \
	php8.4-imap php8.4-mysql php8.4-mbstring \
	php8.4-xml php8.4-zip php8.4-bcmath php8.4-soap php8.4-yaml \
	php8.4-intl php8.4-readline \
	php8.4-ldap \
	php8.4-msgpack php8.4-igbinary php8.4-redis \
	php8.4-memcached php8.4-pcov php8.4-xdebug \
	php8.4-fileinfo
RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
RUN apt install -y mysql-client libapache2-mod-php8.4 cron supervisor
COPY docker-storage/configs/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-storage/configs/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf
RUN phpenmod gd pdo_mysql mysql mbstring redis zip curl openssl
COPY docker-storage/configs/apache2.conf /etc/apache2/apache2.conf
RUN a2enmod rewrite \
	&& a2enmod ssl \
	&& a2enmod php8.4
COPY docker-storage/configs/000-backend.conf /etc/apache2/sites-enabled/000-default.conf
#COPY docker-storage/configs/cron /etc/cron.d/cron
#RUN chmod 0644 /etc/cron.d/cron
#RUN crontab /etc/cron.d/cron
ENTRYPOINT ["/var/www/backend/docker-storage/entrypoint.sh", "-e", "local"]
