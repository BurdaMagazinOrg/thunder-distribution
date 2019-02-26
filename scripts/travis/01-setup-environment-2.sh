#!/usr/bin/env bash

# Disable xdebug.
echo "" > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini
phpenv rehash

# Install Drush and coder
composer global require drush/drush:^8.1 drupal/coder
# Stop drush from sending email
echo "sendmail_path = /bin/true" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
phpenv rehash
