#!/usr/bin/env bash

source ${THUNDER_DIST_DIR}/scripts/travis/05-setup-tests.sh

# Run Drupal tests
cd ${TEST_DIR}/docroot

thunderDumpFile=thunder.php php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 ThunderConfig
