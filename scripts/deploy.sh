#!/bin/bash

# no actual deploy is happening right now
# this will be implement as soon as we have acquia cloud access

if [ $TRAVIS_PULL_REQUEST == "true" ]
then
    echo "Build successful, pull requests will not be deployed"
    exit
fi

ssh-keyscan $ACQUIA_HOST >> ~/.ssh/known_hosts

if [ $TRAVIS_BRANCH == "master" ]
then
  echo 'Deploy to stage.'
  git clone --branch $TRAVIS_BRANCH $ACQUIA_REPOSITORY acquia
  ls -l acquia
elif [ $TRAVIS_BRANCH == "develop" ]
then
  echo 'Deploy to testing.'
  git clone --branch $TRAVIS_BRANCH $ACQUIA_REPOSITORY acquia
  ls -l acquia
else
   echo "Build successful, $TRAVIS_BRANCH will not be deployed"
fi
