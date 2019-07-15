#!/usr/bin/env bash

# install image magick
wget -q https://github.com/mkoppanen/imagick/archive/$PHP_IMAGICK_VERSION.tar.gz -O php-imagick-LATEST.tar.gz
yes '' | pecl -q install -f php-imagick-LATEST.tar.gz

# Build and install the YAML extension for strict parsing.
wget -q https://github.com/php/pecl-file_formats-yaml/archive/$PHP_YAML_VERSION.tar.gz -O php-yaml-LATEST.tar.gz
tar -C /tmp -zxf php-yaml-LATEST.tar.gz
cd /tmp/pecl-file_formats-yaml-$PHP_YAML_VERSION
phpize
# Silence configure
./configure > /dev/null
make -s
make -s install
echo "extension = yaml.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
phpenv rehash

cd ${TEST_DIR}/docroot

#EXAMPLE:
# apply cookie expire patch for javascript tests
#wget https://www.drupal.org/files/issues/test-session-expire-2771547-64.patch
#patch -p1 < test-session-expire-2771547-64.patch

# CREATE TESTING DUMP
php ./core/scripts/db-tools.php dump-database-d8-mysql > thunder.php

# Run the webserver
php -S localhost:8080 .ht.router.php &>/dev/null &
