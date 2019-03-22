#!/usr/bin/env bash

# Run Drupal tests
cd ${TEST_DIR}/docroot

php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --sqlite /tmp/test.sqlite --dburl mysql://travis@127.0.0.1/drupal --verbose --color ThunderConfig
#thunderDumpFile=thunder.php php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 ThunderConfig
