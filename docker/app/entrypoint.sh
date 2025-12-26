#!/bin/sh

# Fix permissions first
chown -R www-data:www-data /var/www
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Step 1: Copy .env if not exists
if [ ! -f /var/www/.env ]; then
  cp /var/www/.env.example /var/www/.env
fi

# Step 2: Install Composer dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader

# Step 3: Generate application key
php artisan key:generate

# Step 4: Run migrations and seeders
php artisan migrate --seed --force

# Step 5: Clear cache and set permissions
php artisan config:clear
php artisan cache:clear
php artisan view:clear
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Step 6: Start scheduler in background
php artisan schedule:work > /dev/null 2>&1 &

# Step 7: Start queue worker in background
php artisan queue:work > /dev/null 2>&1 &

# Step 8: Start PHP-FPM
php-fpm -F
