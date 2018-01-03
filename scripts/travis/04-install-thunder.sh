#!/usr/bin/env bash


# Install thunder and enable Test module
# in provided folder
install_thunder() {
    cd $1

    /usr/bin/env PHP_OPTIONS="-d sendmail_path=`which true`" drush si thunder --db-url=mysql://thunder:thunder@127.0.0.1/drupal -y thunder_module_configure_form.install_modules_thunder_demo
    drush en simpletest -y
}

# Update thunder to current test version
update_thunder() {
    # Link sites folder from initial installation
    mv ${TEST_DIR}/docroot/sites ${TEST_DIR}/docroot/_sites
    ln -s ${UPDATE_BASE_PATH}/docroot/sites ${TEST_DIR}/docroot/sites

    cd ${TEST_DIR}/docroot

    # Execute all required updates
    drush cr
    drush updatedb -y

    # Adjust theme logo path because it can be different in case of composer build
    drush -y php-eval "Drupal::configFactory()->getEditable('thunder_base.settings')->set('logo.path', drupal_get_path('profile', 'thunder') . '/themes/thunder_base/images/Thunder-white_400x90.png')->save(TRUE);"
}

drush_make_thunder() {
    cd ${THUNDER_DIST_DIR}

    # Build drupal + thunder from makefile
    drush make --concurrency=5 drupal-org-core.make ${TEST_DIR}/docroot -y
    mkdir ${TEST_DIR}/docroot/profiles/thunder
    shopt -s extglob
    rsync -a . ${TEST_DIR}/docroot/profiles/thunder --exclude docroot

    drush make -y --no-core ${TEST_DIR}/docroot/profiles/thunder/drupal-org.make ${TEST_DIR}/docroot/profiles/thunder

    # Get development branch of Thunder Admin theme (to use same admin theme as for composer build)
    rm -rf ${TEST_DIR}/docroot/profiles/thunder/themes/thunder_admin
    git clone --depth 1 --branch 8.x-2.x https://git.drupal.org/project/thunder_admin.git ${TEST_DIR}/docroot/profiles/thunder/themes/thunder_admin

    composer install --working-dir=${TEST_DIR}/docroot
}

composer_create_thunder() {
    cd ${THUNDER_DIST_DIR}
    composer create-project burdamagazinorg/thunder-project:2.x ${TEST_DIR} --stability dev --no-interaction --no-install

    cd ${TEST_DIR}
    composer config repositories.thunder path ${THUNDER_DIST_DIR}
    composer require "burdamagazinorg/thunder:*" "drupal/thunder_admin:dev-2.x" "drupal/riddle_marketplace:^3.0" "drupal/nexx_integration:^1.0" "valiton/harbourmaster:~8.1" --no-progress
}

apply_patches() {
    cd ${TEST_DIR}/docroot

    #EXAMPLE:
    # apply cookie expire patch for javascript tests
    #wget https://www.drupal.org/files/issues/test-session-expire-2771547-64.patch
    #patch -p1 < test-session-expire-2771547-64.patch
}

create_testing_dump() {
    cd ${TEST_DIR}/docroot

    php ./core/scripts/db-tools.php dump-database-d8-mysql | gzip > thunder.php.gz
}
# Build current revision of thunder
if [[ ${INSTALL_METHOD} == "drush_make" ]]; then
    drush_make_thunder
elif [[ ${INSTALL_METHOD} == "composer" ]]; then
    composer_create_thunder
fi

# Install Thunder
if [[ ${TEST_UPDATE} == "true" ]]; then
    # Install last drupal org version and update to currently tested version
    install_thunder ${UPDATE_BASE_PATH}/docroot
    update_thunder
else
    install_thunder ${TEST_DIR}/docroot
fi

create_testing_dump

apply_patches
