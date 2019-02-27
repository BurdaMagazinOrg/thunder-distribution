#!/usr/bin/env bash

source ${THUNDER_DIST_DIR}/scripts/travis/functions.sh

# Download latest Thunder release for update
if [[ ${TEST_UPDATE} == "true" ]]; then
    # Download latest release from drupal.org
    drush_download_thunder $UPDATE_BASE_PATH
fi

# Download Thunder
if [[ ${INSTALL_METHOD} == "composer" ]]; then
  source ${THUNDER_DIST_DIR}/scripts/travis/03-download-thunder-composer.sh
else
  source ${THUNDER_DIST_DIR}/scripts/travis/03-download-thunder-drush.sh
fi
