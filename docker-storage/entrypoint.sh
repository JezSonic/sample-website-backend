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
php artisan queue:work & disown
printf "\033[36m[Backend] => \033[0mEntering running state...\n"
tail -f /dev/null
