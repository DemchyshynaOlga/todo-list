#!/bin/sh
set -e

# Run migrations if we're starting php-fpm
if [ "$1" = "php-fpm" ]; then
    cd /var/www/sample
    php artisan migrate --force || true
fi

# Execute the original command
exec "$@"
