# Use a smaller base image if possible
FROM ubuntu:24.04

# Set environment variables
ENV DEBIAN_FRONTEND=noninteractive \
    TZ=UTC

# Update package list and install essential tools
RUN apt-get update -y -q && \
	apt-get upgrade -y -q && \
    apt-get install -y --no-install-recommends \
    gnupg \
    software-properties-common \
    curl \
    ca-certificates \
    unzip

# Add PHP PPA and update package list
RUN add-apt-repository -y ppa:ondrej/php && \
    apt-get update -y

# Install PHP and related extensions
RUN apt-get install -y --no-install-recommends \
	php8.4-pgsql php8.4-gd \
	php8.4-curl \
	php8.4-imap php8.4-mysql php8.4-mbstring \
	php8.4-xml php8.4-zip php8.4-bcmath php8.4-soap php8.4-yaml \
	php8.4-intl php8.4-readline \
	php8.4-ldap \
	php8.4-msgpack php8.4-igbinary php8.4-redis \
	php8.4-memcached php8.4-pcov php8.4-xdebug \
	php8.4-fileinfo

# Install additional tools
RUN apt-get install -y --no-install-recommends \
    mysql-client \
	apache2 \
    libapache2-mod-php8.4 \
    cron \
    supervisor

# Clean up to reduce image size
RUN apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

# Copy Supervisor configuration
COPY docker-storage/configs/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-storage/configs/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

# Enable PHP modules
RUN phpenmod gd pdo_mysql mysql mbstring redis zip curl openssl

# Copy Apache configuration
COPY docker-storage/configs/apache2.conf /etc/apache2/apache2.conf
COPY docker-storage/configs/000-backend.conf /etc/apache2/sites-enabled/000-default.conf

# Enable Apache modules
RUN a2enmod rewrite ssl php8.4

# Set the entrypoint
ENTRYPOINT ["/var/www/backend/docker-storage/entrypoint.sh", "-e", "local"]
