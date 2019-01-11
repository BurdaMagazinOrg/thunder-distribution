#!/usr/bin/env bash

## Setup environment
# add composer's global bin directory to the path
# see: https://github.com/drush-ops/drush#install---composer
export THUNDER_DIST_DIR=`echo $(pwd)`
export TEST_DIR=`echo ${THUNDER_DIST_DIR}"/../test-dir"`
export PATH="$TEST_DIR/bin:$HOME/.composer/vendor/bin:$PATH"
export TEST_INSTALLER="false"
export MINK_DRIVER_ARGS_WEBDRIVER='["chrome", null, "http://localhost:4444/wd/hub"]'

# For daily cron runs, current version from Drupal will be installed
# and after that update will be executed and tested
if [[ ${TRAVIS_EVENT_TYPE} == "cron" ]]; then
    TEST_UPDATE="true"
else
    TEST_UPDATE=""
fi
export TEST_UPDATE;

# Flag used to define if test should run deployment workflow
export TEST_DEPLOYMENT="true"
export DEPLOYMENT_DUMP_FILE="${TEST_DIR}/dump_thunder_test_deployment.sql"

# base path for update tests
export UPDATE_BASE_PATH=${TEST_DIR}-update-base

# Set version of imagick
export PHP_IMAGICK_VERSION="3.4.3"

# Set version for Yaml PHP library
export PHP_YAML_VERSION="2.0.4"

# Set a default install method if none set.
if [[ ${INSTALL_METHOD} == "" ]]; then
  export INSTALL_METHOD=composer
fi;

# Manual overrides of environment variables by commit messages. To override a variable add something like this to
# your commit message:
# git commit -m "Your commit message [TEST_UPDATE=true]"
#
# To override multiple variables us something like this:
# git commit -m "Your other commit message [TEST_UPDATE=true|INSTALL_METHOD=composer]"
if [[ ${TRAVIS_EVENT_TYPE} == "pull_request" ]]; then
    # These are the variables, that are allowed to be overridden
    ALLOWED_VARIABLES=("TEST_UPDATE" "INSTALL_METHOD" "TEST_INSTALLER")
    COMMIT_MESSAGE=$(git log --no-merges -1 --pretty="%B")
    for VARIABLE_NAME in "${ALLOWED_VARIABLES[@]}"
    do
        VALUE=$(echo $COMMIT_MESSAGE | perl -lne "/[|\[]$VARIABLE_NAME=(.+?)[|\]]/ && print \$1")
        if [[ $VALUE ]]; then
            export $VARIABLE_NAME=$VALUE
        fi
    done
fi
# Do not place any code behind this line.
