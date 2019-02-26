#!/usr/bin/env bash

# Install drupalorg_drush module
drush dl drupalorg_drush-7.x
# verify, that makefile is accepted by drupal.org, otherwise we do not need to go any further
drush verify-makefile

# run phpcs
phpcs --config-set installed_paths ~/.composer/vendor/drupal/coder/coder_sniffer
# Ignore check of .md files, because they should be able to contain more then 80 characters per line.
phpcs --standard=Drupal --extensions=php,module,inc,install,test,profile,theme --ignore=*.md -p .
phpcs --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme -p .

# JS ESLint checking
if ! [ -x "$(command -v eslint)" ]; then
    npm install -g eslint
fi
eslint .

# Build docroot
composer_create_thunder

# Check for deprecated methods.
cp ${THUNDER_DIST_DIR}/phpstan.neon.dist phpstan.neon
phpstan analyse --memory-limit 300M ${TEST_DIR}/docroot/profiles/contrib/thunder
