#!/bin/bash

function deploy_to_acquia() {
   DESTINATION_BRANCH=$1
   env
   echo "Deploying $TRAVIS_BRANCH to $DESTINATION_BRANCH"

   cd $TRAVIS_BUILD_DIR

   git clone --branch $DESTINATION_BRANCH $ACQUIA_REPOSITORY acquia
   rsync -ah --delete htdocs/ acquia/docroot/

   cd acquia/docroot

   # is it possible to access original git user?
   git config user.email "travis@example.com"
   git config user.name "Travis"
   git config --global push.default simple

   git add --all .
   git commit --quiet -m "$TRAVIS_COMMIT"
   git push
}

if [ $TRAVIS_PULL_REQUEST == "true" ]
then
    echo "Build successful, pull requests will not be deployed"
    exit
fi

ssh-keyscan $ACQUIA_HOST >> ~/.ssh/known_hosts

if [ $TRAVIS_BRANCH == "master" ]
then
  deploy_to_acquia master
elif [ $TRAVIS_BRANCH == "develop" ]
then
  deploy_to_acquia develop
elif [ $TRAVIS_BRANCH == "acquia-deploy" ]
then
  deploy_to_acquia develop
else
   echo "Build successful, $TRAVIS_BRANCH will not be deployed"
fi



