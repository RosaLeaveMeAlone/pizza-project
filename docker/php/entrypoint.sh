#!/bin/bash
set -e

if [ ! -f "vendor/autoload.php" ]; then
    echo "Installing composer dependencies"
    composer install --no-progress --no-interaction
fi

if [ ! -f ".env" ]; then
    echo "Creating env file for env $APP_ENV"
    cp .env.example .env
else
    echo "env file exists."
fi

role=${CONTAINER_ROLE:-app}

if [ "$role" = "app" ]; then
    # Create SQLite database if it doesn't exist
    if [ ! -f "/var/www/html/database/database.sqlite" ]; then
        echo "Creating SQLite database"
        touch /var/www/html/database/database.sqlite
        chmod 664 /var/www/html/database/database.sqlite
    fi
    
    php artisan migrate --force
    php artisan optimize
    php artisan view:cache
    
    # php-fpm
    exec php-fpm
elif [ "$role" = "scheduler" ]; then
    echo "Starting worker for role $role"
    php artisan schedule:work
fi