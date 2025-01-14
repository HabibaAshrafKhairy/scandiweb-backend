# Use official PHP Apache image
FROM php:8.1-apache

# Install required tools and libraries
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql \
    && apt-get clean  # Clean up to reduce image size


# Install nano
RUN apt-get update && apt-get install -y nano

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable apache mod_rewrite
RUN a2enmod rewrite

# Set the working directory inside the container
WORKDIR /var/www/html

COPY . .

RUN composer install

# Expose the default Apache port
EXPOSE 80

ARG DB_HOST=db
ENV DB_HOST=$DB_HOST
ARG DB_NAME=scandiweb
ENV DB_NAME=$DB_NAME
ARG DB_USER=root
ENV DB_USER=$DB_USER
ARG DB_PASS=yourpassword
ENV DB_PASS=$DB_PASS
