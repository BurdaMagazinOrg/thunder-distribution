#!/usr/bin/env bash

# Rebuild caches and start servers
cd ${TEST_DIR}/docroot

# Final cache rebuild, to make sure every code change is respected
drush cr

# Run the webserver
drush runserver --default-server=builtin 8080 &>/dev/null &

# Run phantomjs for javascript tests
phantomjs --ssl-protocol=any --ignore-ssl-errors=true ${LOCAL_COMPOSER_VENDOR_DIR}/jcalderonzumba/gastonjs/src/Client/main.js 8510 1024 768 false &>/dev/null &
