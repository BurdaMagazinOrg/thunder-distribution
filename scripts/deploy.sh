#!/bin/bash

# no actual deploy is happening right now
# this will be implement as soon as we have acquia cloud access

if [ $TRAVIS_PULL_REQUEST == "true" ]
then
    echo "Build successful, pull requests will not be deployed"
fi

if [ $TRAVIS_BRANCH == "master" ]
then
  echo 'Deploy to stage.'
elif [ $TRAVIS_BRANCH == "develop" ]
then
  echo 'Deploy to testing.'
else
   echo "Build successful, $TRAVIS_BRANCH will not be deployed"
fi
