#!/bin/bash

if [ "$INSTALL_METHOD" == "drush_make" ]
then
  # Build drupal + thunder from makefile
  drush make --concurrency=5 drupal-org-core.make ${TEST_DIR}/docroot -y
  mkdir ${TEST_DIR}/docroot/profiles/thunder
  shopt -s extglob
  rsync -a . ${TEST_DIR}/docroot/profiles/thunder --exclude docroot
  drush make -y --no-core ${TEST_DIR}/docroot/profiles/thunder/drupal-org.make ${TEST_DIR}/docroot/profiles/thunder
fi

if [ "$INSTALL_METHOD" == "composer" ]
then
    composer create-project burdamagazinorg/thunder-infrastructure ${TEST_DIR} --stability dev --no-interaction --no-install
    cd ${TEST_DIR}
    composer config repositories.thunder path ${THUNDER_DIST_DIR}
    composer require "burdamagazinorg/thunder:*" --no-progress
fi