#!/usr/bin/env bash

# Rebuild caches and start servers
cd ${TEST_DIR}/docroot

# require Selenium2 Driver
if [[ ${INSTALL_METHOD} == "drush_make" ]]; then
    composer require "behat/mink-selenium2-driver" "behat/mink-goutte-driver" --no-progress --working-dir ${TEST_DIR}/docroot
elif [[ ${INSTALL_METHOD} == "composer" ]]; then
    composer require "behat/mink-selenium2-driver" "behat/mink-goutte-driver" --no-progress --working-dir ${TEST_DIR}
fi

# Final cache rebuild, to make sure every code change is respected
drush cr

# Run the webserver
drush runserver --default-server=builtin 8080 &>/dev/null &

# Run Selenium2 Server
bash -e /etc/init.d/xvfb start
sleep 3
java -jar -Dwebdriver.chrome.driver="${SELENIUM_PATH}/chromedriver-$CHROME_DRIVER_VERSION" "${SELENIUM_PATH}/selenium-server-standalone-3.0.1.jar" > /dev/null 2>&1 &
