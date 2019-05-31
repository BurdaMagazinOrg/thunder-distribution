#!/usr/bin/env bash

## Setup environment
# add composer's global bin directory to the path
# see: https://github.com/drush-ops/drush#install---composer
export THUNDER_DIST_DIR=`echo $(pwd -P)`
export TEST_DIR=`echo ${THUNDER_DIST_DIR}"/../test-dir"`
export PATH="$TEST_DIR/bin:$HOME/.composer/vendor/bin:$PATH"
export MINK_DRIVER_ARGS_WEBDRIVER='["chrome", null, "http://localhost:4444/wd/hub"]'

# base path for update tests
export UPDATE_BASE_PATH=${TEST_DIR}-update-base

# Set version of imagick
export PHP_IMAGICK_VERSION="3.4.3"

# Set version for Yaml PHP library
export PHP_YAML_VERSION="2.0.4"

# Database dump for deployment testing
export DEPLOYMENT_DUMP_FILE="${TEST_DIR}/dump_thunder_test_deployment.sql"

# Artifact names and files for AWS S3 backup and reuse
export DB_ARTIFACT_FILE_NAME="${TRAVIS_BUILD_ID}-db-dump.gz"
export DB_ARTIFACT_FILE="${THUNDER_DIST_DIR}/../${DB_ARTIFACT_FILE_NAME}"
export PROJECT_ARTIFACT_FILE_NAME="${TRAVIS_BUILD_ID}-thunder.tar.gz"
export PROJECT_ARTIFACT_FILE="${THUNDER_DIST_DIR}/../${PROJECT_ARTIFACT_FILE_NAME}"
