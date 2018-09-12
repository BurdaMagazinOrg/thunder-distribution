#!/usr/bin/env bash

# Run Drupal tests (@group Thunder)
cd ${TEST_DIR}/docroot

# execute Drupal tests
thunderDumpFile=thunder.php.gz php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 Thunder_restricted
# disable configuration testing for update test path
if [[ ${TEST_UPDATE} != "true" ]]; then
    thunderDumpFile=thunder.php.gz php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 ThunderConfig
fi


if [[ ${TEST_UPDATE} == "true" || ${TEST_INSTALLER} == "true" ]]; then
    php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 --class "Drupal\Tests\thunder\Functional\Installer\ThunderInstallerTest"
    php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 --class "Drupal\Tests\thunder\Functional\Installer\ThunderInstallerGermanTest"
fi
