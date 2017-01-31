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

# Setup Selenium2 parameters
export DISPLAY=:99.0

SELENIUM_PATH="$PWD/travis_selenium"
export SELENIUM_PATH

# Manual overrides of environment variables by commit messages. To override a variable add something like this to
# your commit message:
# git commit -m="Your commit message [TEST_UPDATE=true]"
#
# To override multiple variables us something like this:
# git commit -m="Your other commit message [TEST_UPDATE=true|INSTALL_METHOD=composer]"

# These are the variables, that are allowed to be overridden
ALLOWED_VARIABLES=("TEST_UPDATE" "INSTALL_METHOD")

for VARIABLE_NAME in "${ALLOWED_VARIABLES[@]}"
do
 VALUE=$(echo $TRAVIS_COMMIT_MESSAGE | perl -lne "/[|\[]$VARIABLE_NAME=(.+?)[|\]]/ && print \$1")
 if [[ $VALUE ]]; then
    export $VARIABLE_NAME=$VALUE
 fi
done

echo $TEST_UPDATE

# Do not place any code behind this line.
