#!/usr/bin/env bash

## Setup environment
export PHANTOMJS_VERSION=2.1.1
export PATH=$PWD/travis_phantomjs/phantomjs-${PHANTOMJS_VERSION}-linux-x86_64/bin:$PATH

# add composer's global bin directory to the path
# see: https://github.com/drush-ops/drush#install---composer
export PATH="$HOME/.composer/vendor/bin:$PATH"
export THUNDER_DIST_DIR=`echo $(pwd)`
export TEST_DIR=`echo ${THUNDER_DIST_DIR}"/../test-dir"`
