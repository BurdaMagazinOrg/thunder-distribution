#!/usr/bin/env bash

# Rebuild caches and start servers
cd ${TEST_DIR}/docroot

# require Selenium2 Driver
composer require "behat/mink-selenium2-driver"

# globally require drupal coder for code tests
composer global require drupal/coder

# Final cache rebuild, to make sure every code change is respected
drush cr

# Run the webserver
drush runserver --default-server=builtin 8080 &>/dev/null &

# Run Selenium2 Server
bash -e /etc/init.d/xvfb start
sleep 3
java -jar "${SELENIUM_PATH}/selenium-server-standalone-2.53.1.jar" > /dev/null 2>&1 &
