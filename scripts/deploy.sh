#!/bin/bash

function deploy_to_test_site() {
    echo "Deploying $TRAVIS_BRANCH"

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

    git clone git@github.com:BurdaMagazinOrg/thunder-test-site.git thunder-test-site
    if [ ! -d "thunder-test-site" ]
    then
        echo 'Could not checkout thunder-test-site repository.'
        exit
    fi
    cd thunder-test-site

    git checkout 8.x-1.x

    composer require "burdamagazinorg/thunder:dev-$TRAVIS_BRANCH"

    # do not fix line endings, keep everything as is
    echo "* -text" > docroot/.gitattributes

    git config user.email "$LAST_COMMIT_USER_EMAIL"
    git config user.name "$LAST_COMMIT_USER"
    git config --global push.default simple

    git add --all .
    git commit --quiet -m "$LAST_COMMIT_INFO"

    git push
}

if [ $TRAVIS_PULL_REQUEST != "false" ]
then
    echo "Build successful, pull requests will not be deployed"
    exit
fi

deploy_to_test_site