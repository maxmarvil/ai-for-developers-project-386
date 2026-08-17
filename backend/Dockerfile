FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    git \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pdo_mysql \
        zip \
        intl \
        mbstring \
        xml \
        bcmath \
        opcache \
        pcntl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Copy pre-downloaded Composer from build context
COPY composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create non-root user matching host (optional, for file permissions)
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN groupadd -g ${GROUP_ID} appuser && \
    useradd -u ${USER_ID} -g appuser -m appuser

USER appuser
