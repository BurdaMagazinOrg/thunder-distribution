#!/usr/bin/env bash

# Download thunder from drupal.org with drush
drush_download_thunder() {
    DOWNLOAD_PATH=$1

    mkdir -p ${DOWNLOAD_PATH}
    cd ${DOWNLOAD_PATH}

    drush dl thunder --drupal-project-rename="docroot" -y
}

# remove xdebug to make php execute faster
phpenv config-rm xdebug.ini

# Set MySQL Options
mysql -u root -e 'SET GLOBAL wait_timeout = 5400;'
mysql -u root -e "SHOW VARIABLES LIKE 'wait_timeout'"

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