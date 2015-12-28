#!/bin/bash

function deploy_to_acquia() {
   DESTINATION_BRANCH=$1

   echo "Deploying $TRAVIS_BRANCH to $DESTINATION_BRANCH"

   cd $TRAVIS_BUILD_DIR
   LAST_COMMIT_INFO=$(git log -1 --pretty="[%h] (%an) %B")
   LAST_COMMIT_USER=$(git show -s --format="%an")
   LAST_COMMIT_USER_EMAIL=$(git show -s --format="%ae")
   if [ "$TRAVIS_TAG" == "" ]
   then
    COMMIT_TAG=$(git tag --points-at $TRAVIS_COMMIT)
   else
    COMMIT_TAG=$TRAVIS_TAG
   fi

   chmod a+rwx docroot/sites/default/settings.php
   chmod a+rwx docroot/sites/default
   cp settings/settings.acquia.php docroot/sites/default/settings.php
   rm docroot/sites/default/settings.local.php


   if [ "$COMMIT_TAG" == "" ]
   then
    git clone --branch $DESTINATION_BRANCH $ACQUIA_REPOSITORY acquia
   else
    git clone $ACQUIA_REPOSITORY acquia
   fi

   mkdir -p acquia/config

   rsync -ah --delete docroot/ acquia/docroot/
   rsync -ah --delete config/staging/ acquia/config/staging/

   cd acquia
   # do not fix line endings, keep everything as is
   echo "* -text" > docroot/.gitattributes

   git config user.email "$LAST_COMMIT_USER_EMAIL"
   git config user.name "$LAST_COMMIT_USER"
   git config --global push.default simple

   git add --all .
   git commit --quiet -m "$LAST_COMMIT_INFO"

   if [ "$COMMIT_TAG" != "" ]
   then
    git tag $COMMIT_TAG
    git push origin $COMMIT_TAG
   fi
   git push
}

if [ $ACQUIA_REPOSITORY == "" ]
then
    echo "Build successful, pull requests can not be deployed, please provide $ACQUIA_REPOSITORY environment variable."
    exit
fi

if [ $ACQUIA_HOST == "" ]
then
    echo "Build successful, pull requests can not be deployed, please provide $ACQUIA_HOST environment variable."
    exit
fi

if [ $TRAVIS_PULL_REQUEST != "false" ]
then
    echo "Build successful, pull requests will not be deployed"
    exit
fi

ssh-keyscan $ACQUIA_HOST >> ~/.ssh/known_hosts

deploy_to_acquia $TRAVIS_BRANCH
