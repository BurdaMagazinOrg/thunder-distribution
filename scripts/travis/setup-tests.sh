#!/usr/bin/env bash

# Rebuild caches and start servers
cd ${TEST_DIR}/docroot

# Final cache rebuild, to make sure every code change is respected
drush cr

# Run phantomjs for javascript tests
phantomjs --ssl-protocol=any --ignore-ssl-errors=true ${LOCAL_COMPOSER_VENDOR_DIR}/jcalderonzumba/gastonjs/src/Client/main.js 8510 1024 2250 &>/dev/null &