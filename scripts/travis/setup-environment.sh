#!/usr/bin/env bash

## Setup environment
export PHANTOMJS_VERSION=1.9.8
# export PATH=$PWD/travis_phantomjs/phantomjs-${PHANTOMJS_VERSION}-linux-x86_64/bin:$PATH

# add composer's global bin directory to the path
# see: https://github.com/drush-ops/drush#install---composer
export PATH="$HOME/.composer/vendor/bin:$PATH"
export THUNDER_DIST_DIR=`echo $(pwd)`
export TEST_DIR=`echo ${THUNDER_DIST_DIR}"/../test-dir"`

# depending on install method, the composer vendor dirrectory is in different places
if [[ ${INSTALL_METHOD} == "drush_make" ]]; then
    LOCAL_COMPOSER_VENDOR_DIR=${TEST_DIR}/docroot/vendor
elif [[ ${INSTALL_METHOD} == "composer" ]]; then
    LOCAL_COMPOSER_VENDOR_DIR=${TEST_DIR}/vendor
fi
export LOCAL_COMPOSER_VENDOR_DIR

# For daily cron runs, current version from Drupal will be installed
# and after that update will be executed and tested
if [[ ${TRAVIS_EVENT_TYPE} == "cron" ]]; then
    TEST_UPDATE="true"
else
    TEST_UPDATE=""
fi
export TEST_UPDATE;

# base path for update tests
export UPDATE_BASE_PATH=${TEST_DIR}-update-base