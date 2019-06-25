#!/usr/bin/env bash

cd ${TEST_DIR}
composer update

cd ${TEST_DIR}/docroot
drush updb -y

cd ${TEST_DIR}
composer config repositories.thunder path ${THUNDER_DIST_DIR}

composer remove burdamagazinorg/thunder --no-update
composer require "thunder/thunder-distribution:*" --no-update

composer update

jq '.extra.patches += {"drupal/video_embed_field":{"Include upgrade path from video_embed_field":"https://www.drupal.org/files/issues/2019-06-04/2997799-25.patch"}}' composer.json > composer1.json
mv composer1.json composer.json

composer require "drupal/media_entity:^2.0-beta4" "drupal/video_embed_field:^2.0" "drupal/media_entity_image" "drupal/riddle_marketplace:^3.0-beta2"

cd ${TEST_DIR}/docroot
drush cr
drush updb -y
drush en vem_migrate_oembed -y
drush vem:migrate_oembed
