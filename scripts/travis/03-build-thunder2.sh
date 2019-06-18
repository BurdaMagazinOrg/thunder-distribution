#!/usr/bin/env bash

cd ${THUNDER_DIST_DIR}
composer create-project BurdaMagazinOrg/thunder-project:2.x ${TEST_DIR} --stability dev --no-interaction --no-install

cd ${TEST_DIR}

cp ${THUNDER_DIST_DIR}/tests/fixtures/thunder2.composer.lock composer.lock
composer install
composer drupal-scaffold
