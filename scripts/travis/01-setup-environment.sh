#!/usr/bin/env bash

## Setup environment
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

# Update tests can be forced by adding [TEST_UPDATE=true] to your commits
TEST_UPDATE_OVERRIDE=`./scripts/travis/message-parser.php "${TRAVIS_COMMIT_MESSAGE}" TEST_UPDATE`;

if [[ ${TEST_UPDATE_OVERRIDE} == "true" ]]; then
    TEST_UPDATE="true"
# For daily cron runs, current version from Drupal will be installed
# and after that update will be executed and tested
elif [[ ${TRAVIS_EVENT_TYPE} == "cron" ]]; then
    TEST_UPDATE="true"
else
    TEST_UPDATE=""
fi

echo TEST_UPDATE;
export TEST_UPDATE;

# base path for update tests
export UPDATE_BASE_PATH=${TEST_DIR}-update-base

# Setup Selenium2 parameters
export DISPLAY=:99.0

SELENIUM_PATH="$PWD/travis_selenium"
export SELENIUM_PATH
