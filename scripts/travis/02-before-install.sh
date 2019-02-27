#!/usr/bin/env bash

# Disable xdebug.
echo "" > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini
phpenv rehash

# Install Drush and coder
composer global require drush/drush:^8.1 drupal/coder
# Stop drush from sending email
echo "sendmail_path = /bin/true" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
phpenv rehash

# Install drupalorg_drush module
drush dl drupalorg_drush-7.x

# Prepare test directory
cd $TRAVIS_BUILD_DIR
mkdir -p ${TEST_DIR}
