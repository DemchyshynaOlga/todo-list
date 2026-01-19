#!/bin/sh
set -e

cd /var/www/sample

# Create .env file from .env.example if it doesn't exist
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    fi
fi

# Generate application key if not set
php artisan key:generate --force || true

# Run migrations if we're starting php-fpm
if [ "$1" = "php-fpm" ]; then
    php artisan migrate --force || true
fi

# Execute the original command
exec "$@"
