#!/usr/bin/env bash
#
# Download stored project artifact on S3 and export it

# Download project artifact from S3
AWS_ACCESS_KEY_ID="${ARTIFACTS_KEY}" AWS_SECRET_ACCESS_KEY="${ARTIFACTS_SECRET}" aws s3 cp "s3://thunder-builds/${PROJECT_ARTIFACT_FILE_NAME}" "${PROJECT_ARTIFACT_FILE}"

# Extract files to test directory
tar -zxf "${PROJECT_ARTIFACT_FILE}" -C "${TEST_DIR}"

# Install development dependencies
cd "${TEST_DIR}"
composer install --dev
