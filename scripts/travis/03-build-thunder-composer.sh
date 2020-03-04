#!/usr/bin/env bash

# Download latest Thunder release for update
if [[ ${TEST_UPDATE} == "true" ]]; then
    # Download latest release from drupal.org
    mkdir -p $UPDATE_BASE_PATH
    cd $UPDATE_BASE_PATH

    # Get latest Thunder 2 release
    THUNDER_2_LATEST=$(git ls-remote --sort="version:refname" --tags --refs https://git.drupal.org/project/thunder.git 8.x-2.* | cut -d/ -f3 | tail -n1)
    wget https://ftp.drupal.org/files/projects/thunder-${THUNDER_2_LATEST}-core.tar.gz
    tar -zxf thunder-${THUNDER_2_LATEST}-core.tar.gz
    mv thunder-${THUNDER_2_LATEST} ${UPDATE_BASE_PATH}/docroot

    # Install latest Thunder 2 release
    composer install --working-dir=${UPDATE_BASE_PATH}/docroot
fi

cd ${THUNDER_DIST_DIR}
composer create-project burdamagazinorg/thunder-project:2.x ${TEST_DIR} --stability dev --no-interaction --no-install

cd ${TEST_DIR}

if [[ ${TEST_UPDATE} == "true" ]]; then
    sed -i 's/docroot\/profiles\/contrib/docroot\/profiles/g' composer.json
fi

composer config repositories.thunder path ${THUNDER_DIST_DIR}
COMPOSER_MEMORY_LIMIT=-1 composer require "burdamagazinorg/thunder:*" "thunder/thunder_testing_demo:2.x-dev" "mglaman/phpstan-drupal:~0.11.11" "phpstan/phpstan-deprecation-rules:~0.11.0" --no-progress

 # Get custom branch of Thunder Admin theme
rm -rf ${TEST_DIR}/docroot/themes/contrib/thunder_admin
git clone --depth 1 --single-branch --branch ${THUNDER_ADMIN_BRANCH} https://github.com/BurdaMagazinOrg/theme-thunder-admin.git ${TEST_DIR}/docroot/themes/contrib/thunder_admin
