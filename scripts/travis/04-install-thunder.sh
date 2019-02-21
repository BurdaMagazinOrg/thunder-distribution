#!/usr/bin/env bash


# Install thunder and enable Test module
# in provided folder
install_thunder() {
    cd $1

    /usr/bin/env PHP_OPTIONS="-d sendmail_path=`which true`" drush si thunder --db-url=mysql://travis@127.0.0.1/drupal -y thunder_module_configure_form.install_modules_thunder_demo
    drush en simpletest -y

    if [[ "${TEST_DEPLOYMENT}" == "true" ]]; then
        drush -y sql-dump --result-file=${DEPLOYMENT_DUMP_FILE}
    fi
}

# Mock update process for deployment workflow
update_thunder_mock_deployment() {
    # Enable optional modules
    drush -y en password_policy

    drush -y cex sync
    drush -y sql-drop
    drush -y sql-cli < ${DEPLOYMENT_DUMP_FILE}
    drush cr
    drush -y updatedb
    drush -y cim sync
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

    if [[ "${TEST_DEPLOYMENT}" == "true" ]]; then
        update_thunder_mock_deployment
    fi
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
    git clone --depth 1 --single-branch --branch 8.x-2.x https://git.drupal.org/project/thunder_admin.git ${TEST_DIR}/docroot/profiles/thunder/themes/thunder_admin

    composer install --working-dir=${TEST_DIR}/docroot
    composer run-script drupal-phpunit-upgrade --working-dir=${TEST_DIR}/docroot
}

composer_create_thunder() {
    cd ${THUNDER_DIST_DIR}
    composer create-project burdamagazinorg/thunder-project:2.x ${TEST_DIR} --stability dev --no-interaction --no-install

    cd ${TEST_DIR}

    if [[ ${TEST_UPDATE} == "true" ]]; then
        sed -i 's/docroot\/profiles\/contrib/docroot\/profiles/g' composer.json
    fi

    composer config repositories.thunder path ${THUNDER_DIST_DIR}
    composer require "burdamagazinorg/thunder:*" "drupal/thunder_admin:dev-2.x" "mglaman/phpstan-drupal" "phpstan/phpstan-deprecation-rules" --no-progress
}

apply_patches() {
    cd ${TEST_DIR}/docroot
    wget https://www.drupal.org/files/issues/2018-05-24/2975081-6.patch
    patch -p1 < 2975081-6.patch

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

    # Check for deprecated methods.
    cp ${THUNDER_DIST_DIR}/phpstan.neon.dist phpstan.neon
    if [[ ${TEST_UPDATE} == "true" ]]; then
        phpstan analyse --memory-limit 300M ${TEST_DIR}/docroot/profiles/thunder
    else
        phpstan analyse --memory-limit 300M ${TEST_DIR}/docroot/profiles/contrib/thunder
    fi
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
