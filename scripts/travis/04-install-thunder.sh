#!/usr/bin/env bash

# Install thunder and enable Test module
# in provided folder
install_thunder() {
    cd $1

    /usr/bin/env PHP_OPTIONS="-d sendmail_path=`which true`" drush si thunder --db-url=mysql://travis@127.0.0.1/drupal -y thunder_module_configure_form.install_modules_thunder_demo
    drush en simpletest -y

    drush -y sql-dump --result-file=${DEPLOYMENT_DUMP_FILE}
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

    update_thunder_mock_deployment
}

# Install Thunder
if [[ ${TEST_UPDATE} == "true" ]]; then
    # Install last drupal org version and update to currently tested version
    install_thunder ${UPDATE_BASE_PATH}/docroot
    update_thunder
else
    install_thunder ${TEST_DIR}/docroot
fi
