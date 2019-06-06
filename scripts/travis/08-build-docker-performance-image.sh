#!/usr/bin/env bash
#
# Download stored project artifact on S3 and pack it into Thunder performance docker image

# Get Thunder performance Docker project for packaging
THUNDER_PERFORMANCE="${TEST_DIR}/../docker-thunder-performance"
git clone https://github.com/thunder/docker-thunder-performance.git "${THUNDER_PERFORMANCE}"

# Download project artifact from S3
aws s3 cp "s3://thunder-builds/${PROJECT_ARTIFACT_FILE_NAME}" "${PROJECT_ARTIFACT_FILE}"

# Extract files to www directory for Docker image packaging
mkdir -p "${THUNDER_PERFORMANCE}/www"
tar -zxf "${PROJECT_ARTIFACT_FILE}" -C "${THUNDER_PERFORMANCE}/www"

# Build Docker image
cd "${THUNDER_PERFORMANCE}"

# Build Docker image and tag it
DOCKER_IMAGE_TAG=$(echo "thunder-performance:${BRANCH_NAME}-${TRAVIS_JOB_ID}" | sed 's/\//_/g')
DOCKER_IMAGE_TAG=$(echo "burda/${DOCKER_IMAGE_TAG}")
./build.sh --tag "${DOCKER_IMAGE_TAG}"

# list Docker images
docker images

# Login to Docker HUB
echo "${DOCKER_PASSWORD_BASE64}" | base64 -d | docker login -u "${DOCKER_USERNAME}" --password-stdin

# Push to Docker HUB
docker push "${DOCKER_IMAGE_TAG}"

# Trigger AWS create ECS task function
API_CALL_HTTP_CODE=$(curl -X GET \
  "https://qnxrqizu3k.execute-api.eu-central-1.amazonaws.com/default/createEcsTaskDefinition?docker_image=${DOCKER_IMAGE_TAG}&task_defintion_template=thunder_performance_runner&elastic_apm_context_tag_branch=${BRANCH_NAME}" \
  -H "x-api-key: ${AWS_API_KEY_CREATE_ECS_TASK}" \
  --output /dev/stderr \
  --write-out "%{http_code}")

if [[ "${API_CALL_HTTP_CODE}" != "200" ]]; then
    exit 1
fi
