# globally require drupal coder for code tests
composer global require drupal/coder

# run phpcs
phpcs --config-set installed_paths ~/.composer/vendor/drupal/coder/coder_sniffer
phpcs --standard=Drupal --report=summary -p .
phpcs --standard=DrupalPractice --report=summary -p .

# JS ESLint checking
rm -rf ~/.nvm
git clone https://github.com/creationix/nvm.git ~/.nvm
(cd ~/.nvm && git checkout `git describe --abbrev=0 --tags`)
set -x
source ~/.nvm/nvm.sh
set +x
nvm install 4
npm install -g eslint
eslint .