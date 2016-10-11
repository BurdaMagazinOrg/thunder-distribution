#!/usr/bin/env bash

# update composer
composer self-update

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
phantomjs --version

# download + install Selenium2
if [ ! -d "$SELENIUM_PATH" ]; then
  mkdir -p $SELENIUM_PATH;
fi

if [ ! -f "$SELENIUM_PATH/selenium-server-standalone-2.53.1.jar" ]; then
  wget http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.1.jar -O "$SELENIUM_PATH/selenium-server-standalone-2.53.1.jar"
fi

# Install Drush and drupalorg_drush module
composer global require drush/drush:~8
phpenv rehash
drush dl drupalorg_drush-7.x

# verify, that makefile is accepted by drupal.org, otherwise we do not need to go any further
drush verify-makefile

# install image magick
printf "\n" | pecl install imagick
