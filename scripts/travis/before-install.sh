#!/usr/bin/env bash

## Setup environment
export PHANTOMJS_VERSION=2.1.1
export PATH=$PWD/travis_phantomjs/phantomjs-${PHANTOMJS_VERSION}-linux-x86_64/bin:$PATH

# add composer's global bin directory to the path
# see: https://github.com/drush-ops/drush#install---composer
export PATH="$HOME/.composer/vendor/bin:$PATH"
export THUNDER_DIST_DIR=`echo $(pwd)`
export TEST_DIR=`echo ${THUNDER_DIST_DIR}"/../test-dir"`


# remove xdebug to make php execute faster
phpenv config-rm xdebug.ini

# get most current composer
composer self-update

# Clear drush release history cache, to pick up new releases.
rm -f ~/.drush/cache/download/*---updates.drupal.org-release-history-*

# keep travis running without output
bash -e ${THUNDER_DIST_DIR}/scripts/travis/keep-travis-running.sh &