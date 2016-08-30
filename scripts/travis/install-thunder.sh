#!/usr/bin/env bash


# Install thunder and enable Test module
# in provided folder
install_thunder() {
    cd $1

    drush si thunder --db-url=mysql://root:@localhost/drupal -y
    drush en simpletest -y
}

# For daily cron runs, current version from Drupal will be installed
# and after that update will be executed and tested
if [[ ${TRAVIS_EVENT_TYPE} == "cron" ]]; then
    # Install last version released on Drupal
    mkdir -p ${TEST_DIR}-cron-base
    cd ${TEST_DIR}-cron-base

    drush dl thunder --drupal-project-rename="docroot" -y
    install_thunder ${TEST_DIR}-cron-base/docroot
fi

# Install thunder from repository
if [[ ${INSTALL_METHOD} == "drush_make" ]]; then
    cd ${THUNDER_DIST_DIR}

    # Build drupal + thunder from makefile
    drush make --concurrency=5 drupal-org-core.make ${TEST_DIR}/docroot -y
    mkdir ${TEST_DIR}/docroot/profiles/thunder
    shopt -s extglob
    rsync -a . ${TEST_DIR}/docroot/profiles/thunder --exclude docroot

    drush make -y --no-core ${TEST_DIR}/docroot/profiles/thunder/drupal-org.make ${TEST_DIR}/docroot/profiles/thunder
    phantomjs --ssl-protocol=any --ignore-ssl-errors=true ${TEST_DIR}/docroot/vendor/jcalderonzumba/gastonjs/src/Client/main.js 8510 1500 5000 &>/dev/null &

elif [[ ${INSTALL_METHOD} == "composer" ]]; then
    # Build thunder by composer
    composer create-project burdamagazinorg/thunder-infrastructure ${TEST_DIR} --stability dev --no-interaction --no-install

    cd ${TEST_DIR}
    composer config repositories.thunder path ${THUNDER_DIST_DIR}
    composer require "burdamagazinorg/thunder:*" --no-progress
    phantomjs --ssl-protocol=any --ignore-ssl-errors=true ${TEST_DIR}/vendor/jcalderonzumba/gastonjs/src/Client/main.js 8510 1500 5000 &>/dev/null &
fi

# Post install part
if [[ ${TRAVIS_EVENT_TYPE} == "cron" ]]; then
    # Link sites folder from initial installation
    mv ${TEST_DIR}/docroot/sites ${TEST_DIR}/docroot/_sites
    ln -s ${TEST_DIR}-cron-base/docroot/sites ${TEST_DIR}/docroot/sites

    cd ${TEST_DIR}/docroot

    # Execute all required updates
    drush updatedb -y
else
    install_thunder ${TEST_DIR}/docroot
fi

# apply cookie expire patch dor javascript tests
cd ${TEST_DIR}/docroot
wget https://www.drupal.org/files/issues/test-session-expire-2771547-64.patch
patch -p1 < test-session-expire-2771547-64.patch