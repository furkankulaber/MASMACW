#!/bin/bash

echo "STEP 1: CHECKING IF symfony FOLDER EXISTS "

if [ ! -d "/var/www/symfony" ]; then
    echo "NOTICE: symfony folder does not exist, creating /var/www/symfony"
    mkdir /var/www/symfony
fi

echo "STEP 2: SETTING FILE PERMISSIONS AND OWNERSHIP OF symfony FOLDER "
chmod +x /var/www/symfony
    
echo "STEP 3: PREPARING FILE PERMISSONS AND OWNERSHIP OF symfony FOLDER "

cd /var/www/symfony
chown -R root:root /var/www/symfony

echo "STEP 4: UPDATING SYMFONY4 INSTALLATION IF NECESSARY "
php -d memory_limit=-1 /usr/local/bin/composer install
php -d /var/www/symfony/bin/console doctrine:schema:drop --force  --env=prod && /var/www/symfony/bin/console doctrine:schema:update --force --env=prod && /var/www/symfony/bin/console doctrine:fixtures:load --append --env=dev

echo "STEP 5: STARTING FPM AND SUPERVISOR "
/usr/bin/supervisord
