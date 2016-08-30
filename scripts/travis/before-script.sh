#!/usr/bin/env bash

# start servers
cd ${TEST_DIR}/docroot
drush runserver --default-server=builtin 8080 &>/dev/null &