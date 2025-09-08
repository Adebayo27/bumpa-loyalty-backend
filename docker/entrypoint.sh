#!/bin/sh

# Ensure storage directories exist and have correct permissions
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/framework/cache
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
chown -R www-data:www-data /var/www/storage
chmod -R 775 /var/www/storage

# Clear caches to ensure a clean start
php /var/www/artisan config:clear
php /var/www/artisan cache:clear
php /var/www/artisan route:clear

# Run migrations
php artisan migrate --force

# Start Supervisor (which will start both Nginx and PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
