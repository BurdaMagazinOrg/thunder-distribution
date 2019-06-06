#!/usr/bin/env bash
#
# Download stored database dump from S3 and import it

# Download project artifact from S3
aws s3 cp "s3://thunder-builds/${DB_ARTIFACT_FILE_NAME}" "${DB_ARTIFACT_FILE}"

# Unzip to deployment file. Deployment file should be provided for other scripts to work properly.
gunzip < "${DB_ARTIFACT_FILE}" > "${DEPLOYMENT_DUMP_FILE}"

# Import database
cd "${TEST_DIR}/docroot"
drush -y sql-create
drush -y sql-cli < "${DEPLOYMENT_DUMP_FILE}"
