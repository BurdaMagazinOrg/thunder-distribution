#!/usr/bin/env bash
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
