#!/usr/bin/env bash

DISTRIBUTION_REPOSITORY="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../.."

THUNDER_DIRECTORY="$DISTRIBUTION_REPOSITORY/../thunder"

EXPOSED_HTTP_PORT=80

DOCKER_EXEC_PHP="docker-compose exec --user 82 php"

if [ $1 ] ; then
  THUNDER_DIRECTORY="$1"
fi

if [ $2 ] ; then
  EXPOSED_HTTP_PORT="$2"
fi

composer create-project burdamagazinorg/thunder-project:2.x ${THUNDER_DIRECTORY} --stability dev --no-interaction --no-install

sed -e "s|EXPOSED_HTTP_PORT|${EXPOSED_HTTP_PORT}|g" -e "s|THUNDER_DIRECTORY|${THUNDER_DIRECTORY}|g" -e "s|DISTRIBUTION_REPOSITORY|${DISTRIBUTION_REPOSITORY}|g" ${DISTRIBUTION_REPOSITORY}/scripts/development/docker-compose.template.yml > ${THUNDER_DIRECTORY}/docker-compose.yml

cd ${THUNDER_DIRECTORY}

composer config repositories.thunder path ${DISTRIBUTION_REPOSITORY}
composer require "burdamagazinorg/thunder:*" "phpunit/phpunit:~4.8" "behat/mink-selenium2-driver" "behat/mink-goutte-driver" "mikey179/vfsStream" "burdamagazinorg/thunder-dev-tools:*" "burdamagazinorg/robo:*" --no-progress

# The distribution directory gets mounted and cached on docker-compose up
rm docroot/profiles/contrib/thunder

echo "<?php use Thunder\Robo\RoboFileBase; class RoboFile extends RoboFileBase {}" > RoboFile.php

docker-compose down -v
docker-compose up -d

#$DOCKER_EXEC_PHP drush -r docroot si thunder --account-name=admin --account-pass=admin --db-url=mysql://drupal:drupal@mariadb/drupal -y
