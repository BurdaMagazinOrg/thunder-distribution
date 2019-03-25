#!/usr/bin/env bash

# Disable xdebug.
echo "" > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini
# Stop drush from sending email
echo "sendmail_path = /bin/true" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
phpenv rehash

# Prepare test directory
cd $TRAVIS_BUILD_DIR
mkdir -p ${TEST_DIR}

# Install Drush
composer global require drush/drush:^8.1

# Download latest Thunder release for update
if [[ ${TEST_UPDATE} == "true" ]]; then
    # Download latest release from drupal.org
    mkdir -p $UPDATE_BASE_PATH
    cd $UPDATE_BASE_PATH
    drush dl thunder --drupal-project-rename="docroot" -y
    composer install --working-dir=${UPDATE_BASE_PATH}/docroot
fi
