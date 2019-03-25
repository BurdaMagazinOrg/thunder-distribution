#!/usr/bin/env bash

# Clear drush release history cache, to pick up new releases.
rm -f ~/.drush/cache/download/*---updates.drupal.org-release-history-*

drush_make_thunder

# require development packages needed for testing
composer require "behat/mink-selenium2-driver" "behat/mink-goutte-driver" "mikey179/vfsStream" "lullabot/amp" "pusher/pusher-php-server:^3.0.0" --no-progress --working-dir ${TEST_DIR}/docroot

drush_make_thunder() {
    cd ${THUNDER_DIST_DIR}

    # Build drupal + thunder from makefile
    drush make --concurrency=5 drupal-org-core.make ${TEST_DIR}/docroot -y
    mkdir ${TEST_DIR}/docroot/profiles/thunder
    shopt -s extglob
    rsync -a . ${TEST_DIR}/docroot/profiles/thunder --exclude docroot

    drush make -y --no-core ${TEST_DIR}/docroot/profiles/thunder/drupal-org.make ${TEST_DIR}/docroot/profiles/thunder

    # Get development branch of Thunder Admin theme (to use same admin theme as for composer build)
    rm -rf ${TEST_DIR}/docroot/profiles/thunder/themes/thunder_admin
    git clone --depth 1 --single-branch --branch 8.x-2.x https://git.drupal.org/project/thunder_admin.git ${TEST_DIR}/docroot/profiles/thunder/themes/thunder_admin

    composer install --working-dir=${TEST_DIR}/docroot
    composer run-script drupal-phpunit-upgrade --working-dir=${TEST_DIR}/docroot
}
