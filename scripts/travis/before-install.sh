#!/usr/bin/env bash

# Download thunder from drupal.org with drush
drush_download_thunder() {
    DOWNLOAD_PATH=$1

    mkdir -p ${DOWNLOAD_PATH}
    cd ${DOWNLOAD_PATH}

    drush dl thunder --drupal-project-rename="docroot" -y
}

# update composer
composer self-update

# download + install Selenium2
if [ ! -d "$SELENIUM_PATH" ]; then
  mkdir -p $SELENIUM_PATH;
fi

if [ ! -f "$SELENIUM_PATH/selenium-server-standalone-2.53.1.jar" ]; then
  wget http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.1.jar -O "$SELENIUM_PATH/selenium-server-standalone-2.53.1.jar"
fi

# Install Drush and drupalorg_drush module
composer global require drush/drush:~8
phpenv rehash
drush dl drupalorg_drush-7.x

# verify, that makefile is accepted by drupal.org, otherwise we do not need to go any further
drush verify-makefile

# install image magick
printf "\n" | pecl install imagick


# remove xdebug to make php execute faster
phpenv config-rm xdebug.ini

# Set MySQL Options
mysql -e 'SET GLOBAL wait_timeout = 5400;'
mysql -e "SHOW VARIABLES LIKE 'wait_timeout'"

# PHP conf tweaks
echo 'max_execution_time = 120' >> drupal.php.ini;
echo 'sendmail_path = /bin/true' >> drupal.php.ini;
phpenv config-add drupal.php.ini
phpenv rehash

# Prepare test directory
mkdir -p ${TEST_DIR}

# Clear drush release history cache, to pick up new releases.
rm -f ~/.drush/cache/download/*---updates.drupal.org-release-history-*

# keep travis running without output
bash -e ${THUNDER_DIST_DIR}/scripts/travis/keep-travis-running.sh &

# If we test update, we also need the previous version of thunder downloaded
if [[ ${TEST_UPDATE} == "true" ]]; then
    # Download latest release from drupal.org
    drush_download_thunder {$UPDATE_BASE_PATH}
fi