#!/bin/sh
set -e

cd /var/www/html

# Railway injects PORT; default to 8080
export PORT="${PORT:-8080}"

# Generate nginx.conf from template (substitutes ${PORT})
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Ensure storage dirs exist and are writable
mkdir -p storage/framework/{sessions,views,cache/data} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Cache Laravel config/routes/views for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run DB migrations (--force skips confirmation in production)
php artisan migrate --force

# Hand off to supervisord
exec supervisord -n -c /etc/supervisord.conf
