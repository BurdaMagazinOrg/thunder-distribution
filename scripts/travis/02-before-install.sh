#!/usr/bin/env bash

# Disable xdebug.
echo "" > ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini
# Stop drush from sending email
echo "sendmail_path = /bin/true" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
phpenv rehash

# Install AWS CLI
pip install --user awscli

# Prepare test directory
cd $TRAVIS_BUILD_DIR
mkdir -p ${TEST_DIR}

# Install Drush
composer global require drush/drush:^8.1

# Clear drush release history cache, to pick up new releases.
rm -f ~/.drush/cache/download/*---updates.drupal.org-release-history-*
