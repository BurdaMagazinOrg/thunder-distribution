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
#phantomjs --ssl-protocol=any --ignore-ssl-errors=true ${LOCAL_COMPOSER_VENDOR_DIR}/jcalderonzumba/gastonjs/src/Client/main.js 8510 1024 768 false &>/dev/null &

# Setup display for Selenium
export DISPLAY=:99.0
/etc/init.d/xvfb start
sleep 3

# Download and Run Selenium2 Server
# selenium-server -p 4444 &>/dev/null &
wget http://selenium-release.storage.googleapis.com/2.48/selenium-server-standalone-2.48.2.jar
java -jar selenium-server-standalone-2.48.2.jar > /dev/null 2>&1 &
