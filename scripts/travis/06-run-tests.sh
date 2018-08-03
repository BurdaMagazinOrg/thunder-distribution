#!/usr/bin/env bash

# Run Drupal tests (@group Thunder)
cd ${TEST_DIR}/docroot

# disable configuration testing for update test path
if [[ ${TEST_UPDATE} != "true" ]]; then
    thunderDumpFile=thunder.php.gz php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 ThunderConfig
fi

# execute Drupal tests
thunderDumpFile=thunder.php.gz php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 Thunder,thunder_updater

if [[ ${TEST_UPDATE} == "true" || ${TEST_INSTALLER} == "true" ]]; then
    php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 --class "Drupal\thunder\Tests\Installer\ThunderInstallerTest"
    php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 --class "Drupal\thunder\Tests\Installer\ThunderInstallerGermanTest"
fi
