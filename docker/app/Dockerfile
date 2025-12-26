FROM php:8.3-fpm

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Install required system libraries and PHP extensions
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev zlib1g-dev libicu-dev libxml2-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite intl zip xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www

# Expose PHP-FPM port (TCP 9000)
EXPOSE 9000

# Copy custom entrypoint script (if any)
COPY docker/app/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Configure PHP-FPM to listen on TCP instead of socket
RUN sed -i 's|listen = .*|listen = 9000|' /usr/local/etc/php-fpm.d/www.conf
# Fix permissions for Laravel directories
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm", "-F"]
