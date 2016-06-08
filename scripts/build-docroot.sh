#!/bin/bash

if [ $INSTALL_METHOD == "drush_make" ]
then
  # Build drupal + thunder from makefile
  drush make --concurrency=5 drupal-org-core.make docroot -y
  mkdir docroot/profiles/thunder
  shopt -s extglob
  cp -r !(docroot) docroot/profiles/thunder
  drush make -y --no-core docroot/profiles/thunder/drupal-org.make docroot/profiles/thunder
fi

if [ $INSTALL_METHOD == "composer" ]
then
    composer create-project burdamagazinorg/thunder-infrastructure test-dir --stability dev --no-interaction
    cd test-dir
    composer require burdamagazinorg/thunder:dev-8.x-1.x

fi