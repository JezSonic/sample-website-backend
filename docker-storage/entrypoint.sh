#!/bin/bash
cd /var/www/backend || exit 1;
printf "\033[36m[Backend] => \033[0mInstalling packages...\n"
if [ -e composer.lock ]
then
	composer update
else
	composer install
fi;
if [ -e .env ]
then
    printf "\033[36m[Backend] => \033[0m.env file found but might not be up to date!\n"
else
    printf "\033[36m[Backend] => \033[0mCreating .env file from .env.example...\n"
    cp .env.example .env
fi

if [[ "$1" == '--environment' || "$1" == '-e' ]]; then
	printf "\033[36m[Backend] => \033[0mRunning environment: $2 \n"
	if [[ "$2" == 'prod' || "$2" == 'production' ]]; then
		printf "\033[36m[Backend] => \033[0mAutomatic migration and database seeding is disabled on production environment!\n"
	fi

	if [[ "$2" == 'local' || "$2" == 'baremetal' || "$2" == "qa" ]]; then
		printf "\033[36m[Backend] => \033[0mMigrating and seeding database...\n"
		php artisan migrate
		# shellcheck disable=SC2164
		if [[ ! -f docker-storage/keys/jwtpriv.pem && ! -f docker-storage/keys/jwtpub.pem ]]; then
			printf "\033[36m[Backend] => \033[0mNon-existent RSA keys. Generating a new pair...\n"
			cd docker-storage/keys
			openssl ecparam -name prime256v1 -genkey -noout -out jwtpriv.pem
			openssl ec -in jwtpriv.pem -pubout -out jwtpub.pem
		fi
	fi
	if [[ "$2" == '' && "$2" == ' ' ]]; then
		printf "\033[36m[Backend] => \033[0mEnvironment $2 does not exist. Finishing the script!\n"
        exit;
    fi;
fi

printf "\033[36m[Backend] => \033[0mStarting web services...\n"
service apache2 start
service supervisor start
service cron start
printf "\033[36m[Backend] => \033[0mEntering running state...\n"
tail -f /dev/null
