#!/usr/bin/env bash


# Install Drush and drupalorg_drush module
composer global require drush/drush:^8.1 drupal/coder
phpenv rehash
drush dl drupalorg_drush-7.x

# verify, that makefile is accepted by drupal.org, otherwise we do not need to go any further
drush verify-makefile

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

# Disable xdebug.
echo "" > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini

# Stop drush from sending email
echo "sendmail_path = /bin/true" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
phpenv rehash

# Prepare test directory
cd $TRAVIS_BUILD_DIR
mkdir -p ${TEST_DIR}

# Clear drush release history cache, to pick up new releases.
rm -f ~/.drush/cache/download/*---updates.drupal.org-release-history-*
