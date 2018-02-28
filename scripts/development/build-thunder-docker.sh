#!/usr/bin/env bash

PROJECT_NAME="thunder"

if [ $1 ] ; then
  PROJECT_NAME=$1
fi

PROJECT_NAME=$(echo "${PROJECT_NAME}" | tr '[:upper:]' '[:lower:]')

PROJECT_DOCKER_NAME=${PROJECT_NAME//[![:alnum:]]}

INSTALLATIONS_DIRECTORY="${HOME}/tmp/installations"

if [ $2 ] ; then
  INSTALLATIONS_DIRECTORY=$2
fi

PROJECT_DIRECTORY="${INSTALLATIONS_DIRECTORY}/${PROJECT_NAME}"

mkdir -p $PROJECT_DIRECTORY

PROJECT_KEY=${PROJECT_DIRECTORY//\//}

DISTRIBUTION_REPOSITORY=$( cd "$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../.." && pwd )

DISTRIBUTION_KEY=${DISTRIBUTION_REPOSITORY//\//}

composer create-project burdamagazinorg/thunder-project:2.x ${PROJECT_DIRECTORY} --stability dev --no-interaction --no-install

sed -e "s|PROJECT_DIRECTORY|${PROJECT_DIRECTORY}|g" -e "s|DISTRIBUTION_REPOSITORY|${DISTRIBUTION_REPOSITORY}|g" -e "s|PROJECT_KEY|${PROJECT_KEY}|g" -e "s|DISTRIBUTION_KEY|${DISTRIBUTION_KEY}|g" -e "s|PROJECT_DOCKER_NAME|${PROJECT_DOCKER_NAME}|g" ${DISTRIBUTION_REPOSITORY}/scripts/development/docker-compose.template.yml > ${PROJECT_DIRECTORY}/docker-compose.yml

cd ${PROJECT_DIRECTORY}

composer config repositories.thunder path ${DISTRIBUTION_REPOSITORY}
composer require "burdamagazinorg/thunder:*" "phpunit/phpunit:~4.8" "behat/mink-selenium2-driver" "behat/mink-goutte-driver" "mikey179/vfsStream" "burdamagazinorg/thunder-dev-tools:*" "burdamagazinorg/robo:*" --no-progress

echo "<?php use Thunder\Robo\RoboFileBase; class RoboFile extends RoboFileBase {}" > RoboFile.php

echo -e "<?php\n\nrequire_once DRUPAL_ROOT . '/sites/default/default.settings.php';\n\$settings['hash_salt'] = '$(date | md5)';\n\$databases['default']['default'] = [\n  'database' => 'thunder',\n  'username' => 'thunder',\n  'password' => 'thunder',\n  'prefix' => '',\n  'host' => 'mariadb',\n  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',\n  'driver' => 'mysql'\n];\n" > docroot/sites/default/settings.php

docker-compose down -v --remove-orphans
docker-compose -p ${PROJECT_DOCKER_NAME} up -d
