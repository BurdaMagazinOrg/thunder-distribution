#!/usr/bin/env bash

source ${THUNDER_DIST_DIR}/scripts/travis/functions.sh

# Clear drush release history cache, to pick up new releases.
rm -f ~/.drush/cache/download/*---updates.drupal.org-release-history-*

drush_make_thunder

# Install Thunder
if [[ ${TEST_UPDATE} == "true" ]]; then
    # Download latest release from drupal.org
    drush_download_thunder $UPDATE_BASE_PATH
    # Install last drupal org version and update to currently tested version
    install_thunder ${UPDATE_BASE_PATH}/docroot
    update_thunder
else
    install_thunder ${TEST_DIR}/docroot
fi

create_testing_dump

apply_patches
