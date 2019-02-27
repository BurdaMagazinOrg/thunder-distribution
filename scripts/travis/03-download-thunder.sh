#!/usr/bin/env bash

# Download Thunder
if [[ ${INSTALL_METHOD} == "composer" ]]; then
  source ${THUNDER_DIST_DIR}/scripts/travis/03-download-thunder-composer.sh
else
  source ${THUNDER_DIST_DIR}/scripts/travis/03-download-thunder-drush.sh
fi
