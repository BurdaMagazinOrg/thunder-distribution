#!/usr/bin/env bash

# Run Drupal tests
cd ${TEST_DIR}/docroot

php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --suppress-deprecations --verbose --color --url http://localhost:8080 ThunderConfig
