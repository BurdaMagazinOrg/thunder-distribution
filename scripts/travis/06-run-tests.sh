#!/usr/bin/env bash

# Run Drupal tests (@group Thunder)
cd ${TEST_DIR}/docroot

# execute Drupal tests
thunderDumpFile=thunder.php.gz php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --verbose --color --url http://localhost:8080 Thunder

if [[ ${TEST_UPDATE} == "true" || ${TEST_INSTALLER} == "true" ]]; then
    thunderDumpFile=thunder.php.gz php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --verbose --color --url http://localhost:8080 ThunderInstaller
fi
