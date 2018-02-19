#!/usr/bin/env bash

# Rebuild caches and start servers
cd ${TEST_DIR}/docroot

# require development packages needed for testing
if [[ ${INSTALL_METHOD} == "drush_make" ]]; then
    composer require "behat/mink-selenium2-driver" "behat/mink-goutte-driver" "mikey179/vfsStream" "lullabot/amp" --no-progress --working-dir ${TEST_DIR}/docroot
elif [[ ${INSTALL_METHOD} == "composer" ]]; then
    composer require "behat/mink-selenium2-driver" --no-progress --working-dir ${TEST_DIR}
fi

# Final cache rebuild, to make sure every code change is respected
drush cr

# Get the 8.5.x version of .ht.router.php from commit 0b2d458.
curl https://raw.githubusercontent.com/drupal/drupal/0b2d458da9f84aeb31f72a8aef7b0f174249e2ad/.ht.router.php --output .ht.router.php
# Run the webserver
php -S localhost:8080 .ht.router.php &>/dev/null &

# Run Sauce Labs connector manually if Sauce Labs is enabled
if [[ ${SAUCE_LABS_ENABLED} == "true" ]]; then
    curl -s https://raw.githubusercontent.com/travis-ci/travis-build/master/lib/travis/build/addons/sauce_connect/templates/sauce_connect.sh -o sauce_connect.sh
    source sauce_connect.sh

    travis_start_sauce_connect
fi

docker run -d -p 4444:4444 -v $(pwd)/$(drush eval "echo drupal_get_path('profile', 'thunder');")/tests:/tests -v /dev/shm:/dev/shm --net=host selenium/standalone-chrome:3.8.1-aluminum
docker ps -a
