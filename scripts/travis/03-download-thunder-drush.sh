#!/usr/bin/env bash

source ${THUNDER_DIST_DIR}/scripts/travis/functions.sh

# Clear drush release history cache, to pick up new releases.
rm -f ~/.drush/cache/download/*---updates.drupal.org-release-history-*

drush_make_thunder

# require development packages needed for testing
composer require "behat/mink-selenium2-driver" "behat/mink-goutte-driver" "mikey179/vfsStream" "lullabot/amp" "pusher/pusher-php-server:^3.0.0" --no-progress --working-dir ${TEST_DIR}/docroot
