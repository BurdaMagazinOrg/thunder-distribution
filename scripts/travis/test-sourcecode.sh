# globally require drupal coder for code tests
composer global require drupal/coder

# run phpcs
phpcs --config-set installed_paths ~/.composer/vendor/drupal/coder/coder_sniffer
phpcs --standard=Drupal --report=summary -p .
phpcs --standard=DrupalPractice --report=summary -p .

# JS ESLint checking
npm install -g eslint
eslint .