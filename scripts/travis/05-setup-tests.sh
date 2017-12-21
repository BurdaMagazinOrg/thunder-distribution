#!/usr/bin/env bash

# Rebuild caches and start servers
cd ${TEST_DIR}/docroot

# require development packages needed for testing
if [[ ${INSTALL_METHOD} == "drush_make" ]]; then
    composer require "behat/mink-selenium2-driver" "behat/mink-goutte-driver" "mikey179/vfsStream" "lullabot/amp" --no-progress --working-dir ${TEST_DIR}/docroot
elif [[ ${INSTALL_METHOD} == "composer" ]]; then
    composer require "behat/mink-selenium2-driver" "behat/mink-goutte-driver" "mikey179/vfsStream" --no-progress --working-dir ${TEST_DIR}
fi

# Final cache rebuild, to make sure every code change is respected
drush cr

# Remove once Thunder is using 8.5.x and
# https://www.drupal.org/project/drupal/issues/2929198 has been committed
cp ${TEST_DIR}/docroot/profiles/contrib/thunder/travis/.ht.router.php ./
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
