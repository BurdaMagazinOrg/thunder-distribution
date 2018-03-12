#!/usr/bin/env bash

# Download thunder from drupal.org with drush
drush_download_thunder() {
    DOWNLOAD_PATH=$1

    mkdir -p $DOWNLOAD_PATH
    cd $DOWNLOAD_PATH
    drush dl thunder --drupal-project-rename="docroot" -y
    composer install --working-dir=${DOWNLOAD_PATH}/docroot
}

# update composer
composer self-update

# remove xdebug to make php execute faster
phpenv config-rm xdebug.ini

# Install Drush and drupalorg_drush module
composer global require drush/drush:^8.1 drupal/coder
phpenv rehash
drush dl drupalorg_drush-7.x

# verify, that makefile is accepted by drupal.org, otherwise we do not need to go any further
drush verify-makefile

# install image magick
wget https://github.com/mkoppanen/imagick/archive/$PHP_IMAGICK_VERSION.tar.gz -O php-imagick-LATEST.tar.gz
yes '' | pecl install -f php-imagick-LATEST.tar.gz

# Build and install the YAML extension for strict parsing.
wget https://github.com/php/pecl-file_formats-yaml/archive/$PHP_YAML_VERSION.tar.gz -O php-yaml-LATEST.tar.gz
tar -C /tmp -zxvf php-yaml-LATEST.tar.gz
cd /tmp/pecl-file_formats-yaml-$PHP_YAML_VERSION
phpize
./configure
make
make install
echo "extension = yaml.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
phpenv rehash
cd $TRAVIS_BUILD_DIR

# Set MySQL Options
mysql -e "SET GLOBAL wait_timeout = 5400;"
mysql -e "SHOW VARIABLES LIKE 'wait_timeout';"

# Prepare MySQL user and database
mysql -e "CREATE DATABASE drupal;"
mysql -e "CREATE USER 'thunder'@'localhost' IDENTIFIED BY 'thunder';"
mysql -e "GRANT ALL ON drupal.* TO 'thunder'@'localhost';"

# PHP conf tweaks
echo 'max_execution_time = 120' >> drupal.php.ini;
echo 'sendmail_path = /bin/true' >> drupal.php.ini;
echo 'always_populate_raw_post_data = -1' >> drupal.php.ini;
phpenv config-add drupal.php.ini
phpenv rehash

# Prepare test directory
mkdir -p ${TEST_DIR}

# Clear drush release history cache, to pick up new releases.
rm -f ~/.drush/cache/download/*---updates.drupal.org-release-history-*

# If we test update, we also need the previous version of thunder downloaded
if [[ ${TEST_UPDATE} == "true" ]]; then
    # Download latest release from drupal.org
    drush_download_thunder $UPDATE_BASE_PATH
fi
