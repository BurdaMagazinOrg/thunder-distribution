#!/usr/bin/env bash

# Download latest Thunder release for update
if [[ ${TEST_UPDATE} == "true" ]]; then
    # Download latest release from drupal.org
    mkdir -p $UPDATE_BASE_PATH
    cd $UPDATE_BASE_PATH
    drush dl thunder --drupal-project-rename="docroot" -y
    composer install --working-dir=${UPDATE_BASE_PATH}/docroot
fi

cd ${THUNDER_DIST_DIR}
composer create-project burdamagazinorg/thunder-project:2.x ${TEST_DIR} --stability dev --no-interaction --no-install

cd ${TEST_DIR}

if [[ ${TEST_UPDATE} == "true" ]]; then
    sed -i 's/docroot\/profiles\/contrib/docroot\/profiles/g' composer.json
fi

composer config repositories.thunder path ${THUNDER_DIST_DIR}
composer require "burdamagazinorg/thunder:*" "drupal/core:${DRUPAL_CORE}" "mglaman/phpstan-drupal" "nette/di:*@stable" "phpstan/phpstan-deprecation-rules" --no-progress

 # Get custom branch of Thunder Admin theme
rm -rf ${TEST_DIR}/docroot/themes/contrib/thunder_admin
git clone --depth 1 --single-branch --branch ${THUNDER_ADMIN_BRANCH} https://github.com/BurdaMagazinOrg/theme-thunder-admin.git ${TEST_DIR}/docroot/themes/contrib/thunder_admin
