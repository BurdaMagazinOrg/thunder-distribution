#!/usr/bin/env bash
#
# Package and upload built Thunder project and database

# Package database
gzip < "${DEPLOYMENT_DUMP_FILE}" > "${DB_ARTIFACT_FILE}"

# Include performance measurement module in artifact
cd "${TEST_DIR}"
composer require "thunder/thunder_performance_measurement:dev-master" --no-interaction --update-no-dev

# Cleanup project
cd "${TEST_DIR}"
composer install --no-dev
rm -rf "${TEST_DIR}/bin"
rm -rf "${TEST_DIR}/docroot/sites/default/files/*"
find "${TEST_DIR}" -type d -name ".git" | xargs rm -rf
find "${THUNDER_DIST_DIR}" -type d -name ".git" | xargs rm -rf

# Make zip for package
cd "${TEST_DIR}" && tar -czhf "${PROJECT_ARTIFACT_FILE}" .

# Upload files to S3 bucket
aws s3 cp "${DB_ARTIFACT_FILE}" "s3://thunder-builds/${DB_ARTIFACT_FILE_NAME}"
aws s3 cp "${PROJECT_ARTIFACT_FILE}" "s3://thunder-builds/${PROJECT_ARTIFACT_FILE_NAME}"
