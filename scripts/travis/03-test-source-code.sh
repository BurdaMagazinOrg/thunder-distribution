# run phpcs
phpcs --config-set installed_paths ~/.composer/vendor/drupal/coder/coder_sniffer
phpcs --standard=Drupal --extensions=php,module,inc,install,test,profile,theme -p .
phpcs --standard=DrupalPractice --extensions=php,module,inc,install,test,profile,theme -p .

# JS ESLint checking
mv ~/.nvm ~/.nvm-backup
git clone https://github.com/creationix/nvm.git ~/.nvm
(cd ~/.nvm && git checkout `git describe --abbrev=0 --tags`)
set -x
source ~/.nvm/nvm.sh
set +x
nvm install 4
npm install -g eslint
eslint .
rm -rf ~/.nvm
mv ~/.nvm-backup ~/.nvm
