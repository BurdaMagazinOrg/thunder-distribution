#!/usr/bin/env bash
# remove xdebug to make php execute faster
phpenv config-rm xdebug.ini

# get most current composer
composer self-update

# Clear drush release history cache, to pick up new releases.
rm -f ~/.drush/cache/download/*---updates.drupal.org-release-history-*

# keep travis running without output
bash -e ${THUNDER_DIST_DIR}/scripts/travis/keep-travis-running.sh &