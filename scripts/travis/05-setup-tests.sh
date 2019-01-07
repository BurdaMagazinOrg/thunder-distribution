#!/usr/bin/env bash

# Rebuild caches and start servers
cd ${TEST_DIR}/docroot

# require development packages needed for testing
if [[ ${INSTALL_METHOD} == "drush_make" ]]; then
    composer require "behat/mink-selenium2-driver" "behat/mink-goutte-driver" "mikey179/vfsStream" "lullabot/amp" "pusher/pusher-php-server:^3.0.0" --no-progress --working-dir ${TEST_DIR}/docroot
fi

# Final cache rebuild, to make sure every code change is respected
drush cr

# Run the webserver
php -S localhost:8080 .ht.router.php &>/dev/null &

docker run -d -p 4444:4444 -v $(pwd)/$(drush eval "echo drupal_get_path('profile', 'thunder');")/tests:/tests -v /dev/shm:/dev/shm --net=host selenium/standalone-chrome:3.14.0-iron
docker ps -a
