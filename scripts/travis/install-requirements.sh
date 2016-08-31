#!/usr/bin/env bash

# install required phantomjs Version, if its not already build from travis cache
if [ $(phantomjs --version) != ${PHANTOMJS_VERSION} ]; then
    rm -rf $PWD/travis_phantomjs; mkdir -p $PWD/travis_phantomjs;
fi
if [ $(phantomjs --version) != ${PHANTOMJS_VERSION} ]; then
    wget https://assets.membergetmember.co/software/phantomjs-${PHANTOMJS_VERSION}-linux-x86_64.tar.bz2 -O $PWD/travis_phantomjs/phantomjs-${PHANTOMJS_VERSION}-linux-x86_64.tar.bz2;
fi
if [ $(phantomjs --version) != ${PHANTOMJS_VERSION} ]; then
    tar -xf $PWD/travis_phantomjs/phantomjs-${PHANTOMJS_VERSION}-linux-x86_64.tar.bz2 -C $PWD/travis_phantomjs;
fi

# Install Drush and drupalorg_drush module
composer global require drush/drush:~8
phpenv rehash
drush dl drupalorg_drush-7.x

# verify, that makefile is accepted by drupal.org, otherwise we do not need to go any further
drush verify-makefile

# Install and configure behat
composer global require "behat/behat:~3.0" "drupal/drupal-extension:^3.2" "devinci/devinci-behat-extension:dev-master"
BEHAT_PARAMS='{"extensions":{"Drupal\\DrupalExtension":{"drupal":{"drupal_root":"TEST_DIR_MACRO/docroot"}},"Behat\\MinkExtension":{"base_url":"http://localhost:8080"}}}'
BEHAT_PARAMS=`echo $BEHAT_PARAMS | sed -e s#TEST_DIR_MACRO#$TEST_DIR#g`
export BEHAT_PARAMS

# install image magick
printf "\n" | pecl install imagick
