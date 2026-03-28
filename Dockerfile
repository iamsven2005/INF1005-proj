FROM php:8.2-apache

# Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli zip \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite
RUN a2enmod rewrite && \
    echo '<Directory /var/www/html>\n    AllowOverride None\n    Require all granted\n</Directory>\nErrorDocument 404 /404.php' \
    >> /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy ONLY composer.json + lock first (better layer caching)
COPY ./src/composer.json ./src/composer.lock* ./

# Install composer packages (creates vendor/ INSIDE the container)
RUN composer install --no-interaction --optimize-autoloader

# Now copy the rest of your source files
COPY ./src /var/www/html

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]
