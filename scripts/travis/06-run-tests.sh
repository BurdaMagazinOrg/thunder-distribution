#!/usr/bin/env bash

# Run Drupal tests (@group Thunder)
cd ${TEST_DIR}/docroot

cp ${THUNDER_DIST_DIR}/scripts/travis/phpunit.xml.dist phpunit.xml

# Make simple export import
if [[ "${TEST_UPDATE}" != "true" && "${TEST_DEPLOYMENT}" == "true" ]]; then
    drush -y cex sync

    # We have to use "2>&1" because drush outputs everything to stderr
    DRUSH_CIM_RESULT=$(drush -y cim sync 2>&1)
    if [[ "${DRUSH_CIM_RESULT}" != *"There are no changes to import."* ]]; then
        exit 1
    fi
fi

# disable configuration testing for update test path
if [[ ${TEST_UPDATE} != "true" ]]; then
    thunderDumpFile=thunder.php paratest --phpunit=/home/travis/build/BurdaMagazinOrg/thunder-distribution/../test-dir/bin/phpunit ${THUNDER_DIST_DIR}/tests --runner WrapperRunner -p2 --group ThunderConfig
fi

# execute Drupal tests
thunderDumpFile=thunder.php paratest --phpunit=/home/travis/build/BurdaMagazinOrg/thunder-distribution/../test-dir/bin/phpunit ${THUNDER_DIST_DIR}/tests --runner WrapperRunner -p2 --group Thunder,thunder_updater

if [[ ${TEST_UPDATE} == "true" || ${TEST_INSTALLER} == "true" ]]; then
    thunderDumpFile=thunder.php paratest --phpunit=/home/travis/build/BurdaMagazinOrg/thunder-distribution/../test-dir/bin/phpunit ${THUNDER_DIST_DIR}/tests --runner WrapperRunner -p2 --group ThunderInstaller
fi
