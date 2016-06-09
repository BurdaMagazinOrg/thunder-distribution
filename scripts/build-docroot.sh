#!/bin/bash
if [ "$INSTALL_METHOD" == "drush_make" ]
then
  # Build drupal + thunder from makefile
  drush make --concurrency=5 drupal-org-core.make test-dir/docroot -y
  mkdir test-dir/docroot/profiles/thunder
  shopt -s extglob
  rsync -a --progress . test-dir/docroot/profiles/thunder --exclude docroot
  drush make -y --no-core test-dir/docroot/profiles/thunder/drupal-org.make test-dir/docroot/profiles/thunder
fi

if [ "$INSTALL_METHOD" == "composer" ]
then
    composer create-project burdamagazinorg/thunder-infrastructure test-dir --stability dev --no-interaction
    cd test-dir
    composer require burdamagazinorg/thunder:dev-8.x-1.x
    composer update
fi