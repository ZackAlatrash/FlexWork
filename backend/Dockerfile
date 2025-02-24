# Use the official PHP 8.2 image with FPM
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip curl

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set the working directory
WORKDIR /var/www/html

# Copy the Laravel project files into the container
COPY . .

# Install Composer inside the container
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

# Set permissions
RUN chmod -R 777 storage bootstrap/cache

# Start PHP-FPM
CMD ["php-fpm"]
