#!/bin/bash

# no actual deploy is happening right now
# this will be implement as soon as we have acquia cloud access

if [ $TRAVIS_PULL_REQUEST == "true" ]
then
    echo "Build successful, pull requests will not be deployed"
    exit
fi

ssh-keyscan $ACQUIA_HOST >> ~/.ssh/known_hosts

echo "On branch $TRAVIS_BRANCH"

if [ $TRAVIS_BRANCH == "master" ]
then
  echo 'Deploy to stage.'
  deploy_to_acquia
elif [ $TRAVIS_BRANCH == "develop" ]
then
  echo 'Deploy to testing.'
  deploy_to_acquia
elif [ $TRAVIS_BRANCH == "acquia-deploy" ]
then
  echo 'Deploy to testing.'
  deploy_to_acquia
else
   echo "Build successful, $TRAVIS_BRANCH will not be deployed"
fi


deploy_to_acquia() {
   cd $TRAVIS_BUILD_DIR
   git clone --branch $TRAVIS_BRANCH $ACQUIA_REPOSITORY acquia
   rsync -avh --delete htdocs acquia/docroot

   ls acquia/docroot/modules
   ls acquia/docroot/modules/contrib

   cd acquia/docroot
   git add .
   git commit --quiet -m "$TRAVIS_COMMIT"
   git push
}
