#!/bin/bash

# run Drupal tests (@group Thunder)
php ${TEST_DIR}/docroot/core/scripts/run-tests.sh --php `which php` --die-on-fail --verbose --url http://localhost:8080 Thunder

# run Behat tests
behat --config ${THUNDER_DIST_DIR}/tests/behat/behat.travis.yml