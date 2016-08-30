#!/usr/bin/env bash

# Run Drupal tests (@group Thunder)
cd ${TEST_DIR}/docroot
php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --die-on-fail --verbose --url http://localhost:8080 Thunder