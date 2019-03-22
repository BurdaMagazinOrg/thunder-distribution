#!/usr/bin/env bash

source ${THUNDER_DIST_DIR}/scripts/travis/functions.sh

# install image magick
wget https://github.com/mkoppanen/imagick/archive/$PHP_IMAGICK_VERSION.tar.gz -O php-imagick-LATEST.tar.gz
yes '' | pecl install -f php-imagick-LATEST.tar.gz

# Build and install the YAML extension for strict parsing.
wget https://github.com/php/pecl-file_formats-yaml/archive/$PHP_YAML_VERSION.tar.gz -O php-yaml-LATEST.tar.gz
tar -C /tmp -zxvf php-yaml-LATEST.tar.gz
cd /tmp/pecl-file_formats-yaml-$PHP_YAML_VERSION
phpize
./configure
make
make install
echo "extension = yaml.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
phpenv rehash

create_testing_dump

apply_patches

# Rebuild caches and start servers
cd ${TEST_DIR}/docroot

# Final cache rebuild, to make sure every code change is respected
drush cr

# Run the webserver
php -S localhost:8080 .ht.router.php &>/dev/null &
