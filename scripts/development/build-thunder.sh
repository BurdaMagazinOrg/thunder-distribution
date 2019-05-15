#!/usr/bin/env bash

BASE_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )/../.."

DEST_DIR="$BASE_DIR/../thunder"

if [ $1 ] ; then
  DEST_DIR="$1"
fi

composer create-project thunder/thunder-project:3.x ${DEST_DIR} --stability dev --no-interaction --no-install

cd ${DEST_DIR}
composer config repositories.thunder path ${BASE_DIR}
composer config repositories.thunder_admin git https://github.com/BurdaMagazinOrg/theme-thunder-admin.git

composer require "thunder/thunder-distribution:*" "phpunit/phpunit:~4.8" "behat/mink-selenium2-driver" "behat/mink-goutte-driver" "mikey179/vfsStream" "burdamagazinorg/thunder-dev-tools:*" "burdamagazinorg/robo:*" --no-progress

echo "<?php use Thunder\Robo\RoboFileBase; class RoboFile extends RoboFileBase {}" > RoboFile.php
