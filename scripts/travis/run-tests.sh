#!/usr/bin/env bash

# Run Drupal tests (@group Thunder)
cd ${TEST_DIR}/docroot

# execute Drupal tests
php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --die-on-fail --verbose --color --url http://localhost:8080 Thunder
