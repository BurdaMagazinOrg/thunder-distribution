#!/usr/bin/env bash

# Download latest Thunder release for update
if [[ ${TEST_UPDATE} == "true" ]]; then
    # Download latest release from drupal.org
    mkdir -p $UPDATE_BASE_PATH
    cd $UPDATE_BASE_PATH
    drush dl thunder --drupal-project-rename="docroot" -y
    composer install --working-dir=${$UPDATE_BASE_PATH}/docroot
fi

# Download Thunder
if [[ ${INSTALL_METHOD} == "composer" ]]; then
  source ${THUNDER_DIST_DIR}/scripts/travis/03-download-thunder-composer.sh
else
  source ${THUNDER_DIST_DIR}/scripts/travis/03-download-thunder-drush.sh
fi
