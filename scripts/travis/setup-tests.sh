#!/usr/bin/env bash

# Rebuild caches and start servers
cd ${TEST_DIR}/docroot

# require Selenium2 Driver
composer require "behat/mink-selenium2-driver"

# Final cache rebuild, to make sure every code change is respected
drush cr

# Run the webserver
drush runserver --default-server=builtin 8080 &>/dev/null &

# Run phantomjs for javascript tests
phantomjs --ssl-protocol=any --ignore-ssl-errors=true ${LOCAL_COMPOSER_VENDOR_DIR}/jcalderonzumba/gastonjs/src/Client/main.js 8510 1024 768 false &>/dev/null &

# Run Selenium2 Server
bash -e /etc/init.d/xvfb start
sleep 3
java -jar "${SELENIUM_PATH}/selenium-server-standalone-2.53.1.jar" > /dev/null 2>&1 &
