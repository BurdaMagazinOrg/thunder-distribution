#!/usr/bin/env bash

# Run Drupal tests (@group Thunder)
cd ${TEST_DIR}/docroot

# Make simple export import
if [[ "${TEST_UPDATE}" != "true" ]]; then
    drush -y cex sync

    # We have to use "2>&1" because drush outputs everything to stderr
    DRUSH_CIM_RESULT=$(drush -y cim sync 2>&1)
    if [[ "${DRUSH_CIM_RESULT}" != *"There are no changes to import."* ]]; then
        exit 1
    fi
fi

# execute Drupal tests
thunderDumpFile=thunder.php php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 Thunder

if [[ ${TEST_UPDATE} == "true" ]]; then
    php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 --class "Drupal\Tests\thunder\Functional\Installer\ThunderInstallerTest"
    php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 --class "Drupal\Tests\thunder\Functional\Installer\ThunderInstallerGermanTest"
fi
