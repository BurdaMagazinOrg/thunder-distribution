#!/usr/bin/env bash

EXPOSED_HTTP_PORT=80

RANDOM_NUMBER=$(jot -r 1  100000 999999)

DISTRIBUTION_REPOSITORY=$( cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../.." && pwd )

DISTRIBUTION_REPOSITORY_SYNC_KEY=${RANDOM_NUMBER}${DISTRIBUTION_REPOSITORY//\//-}

THUNDER_DIRECTORY=$( cd "${DISTRIBUTION_REPOSITORY}/../thunder" && pwd )

if [ $1 ] ; then
  mkdir -p ${1}
  THUNDER_DIRECTORY=$( cd ${1} && pwd )
fi

if [ $2 ] ; then
  EXPOSED_HTTP_PORT="$2"
fi

THUNDER_DIRECTORY_SYNC_KEY=${RANDOM_NUMBER}${THUNDER_DIRECTORY//\//-}

DOCKER_EXEC_PHP="docker-compose exec --user 82 php"

composer create-project burdamagazinorg/thunder-project:2.x ${THUNDER_DIRECTORY} --stability dev --no-interaction --no-install

sed -e "s|THUNDER_DIRECTORY|${THUNDER_DIRECTORY}|g" -e "s|DISTRIBUTION_REPOSITORY|${DISTRIBUTION_REPOSITORY}|g" -e "s|THUNDER_SYNC_DIRECTORY|${THUNDER_DIRECTORY_SYNC_KEY}|g" -e "s|DISTRIBUTION_SYNC_REPOSITORY|${DISTRIBUTION_REPOSITORY_SYNC_KEY}|g" -e "s|EXPOSED_HTTP_PORT|${EXPOSED_HTTP_PORT}|g" ${DISTRIBUTION_REPOSITORY}/scripts/development/docker-compose.template.yml > ${THUNDER_DIRECTORY}/docker-compose.yml
sed -e "s|THUNDER_DIRECTORY|${THUNDER_DIRECTORY}|g" -e "s|DISTRIBUTION_REPOSITORY|${DISTRIBUTION_REPOSITORY}|g" -e "s|THUNDER_SYNC_DIRECTORY|${THUNDER_DIRECTORY_SYNC_KEY}|g" -e "s|DISTRIBUTION_SYNC_REPOSITORY|${DISTRIBUTION_REPOSITORY_SYNC_KEY}|g" -e "s|SYNC_PORT|${SYNC_PORT}|g" ${DISTRIBUTION_REPOSITORY}/scripts/development/docker-sync-template.yml > ${THUNDER_DIRECTORY}/docker-sync.yml

cd ${THUNDER_DIRECTORY}

composer config repositories.thunder path ${DISTRIBUTION_REPOSITORY}
composer require "burdamagazinorg/thunder:*" "phpunit/phpunit:~4.8" "behat/mink-selenium2-driver" "behat/mink-goutte-driver" "mikey179/vfsStream" "burdamagazinorg/thunder-dev-tools:*" "burdamagazinorg/robo:*" --no-progress

echo "<?php use Thunder\Robo\RoboFileBase; class RoboFile extends RoboFileBase {}" > RoboFile.php

rm docroot/profiles/contrib/thunder

docker-compose down -v
docker-sync start
docker-compose up -d
