#!/usr/bin/env bash

cd ${THUNDER_DIST_DIR}
composer create-project burdamagazinorg/thunder-project:2.x ${TEST_DIR} --stability dev --no-interaction --no-install

cd ${TEST_DIR}

if [[ ${TEST_UPDATE} == "true" ]]; then
    sed -i 's/docroot\/profiles\/contrib/docroot\/profiles/g' composer.json
fi

composer config repositories.thunder path ${THUNDER_DIST_DIR}
composer require "burdamagazinorg/thunder:*" "drupal/thunder_admin:dev-2.x" "mglaman/phpstan-drupal" "phpstan/phpstan-deprecation-rules" --no-progress
