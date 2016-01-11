#!/bin/bash

function deploy_to_acquia() {
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

    chmod a+rwx docroot/sites/default/settings.php
    chmod a+rwx docroot/sites/default
    cp -v settings/settings.acquia.php docroot/sites/default/settings.php

    # FIXME workarounds for problematic config management (no stage dependent config) - remove these when issue is fixed
    cp -v settings/settings.dev.php docroot/sites/default/settings.dev.php
    cp -v settings/settings.prod.php docroot/sites/default/settings.prod.php

    rm docroot/sites/default/settings.local.php

    git clone $ACQUIA_REPOSITORY acquia
    cd acquia

    if [ "$COMMIT_TAG" == "" ]
    then
        git rev-parse --verify origin/$TRAVIS_BRANCH;
        if [ "$?" == "0" ]
        then
            echo "checking out branch $TRAVIS_BRANCH:";
            git checkout $TRAVIS_BRANCH
        else
            echo "checking out new branch $TRAVIS_BRANCH:";
            git checkout -b $TRAVIS_BRANCH
            git push -u origin $TRAVIS_BRANCH
        fi
    fi

    mkdir -pv config

    rsync -ah --delete ../docroot/ docroot/
    rsync -ah --delete ../config/staging/ config/staging/
    rsync -ah --delete ../hooks/ hooks/

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
    else
        git push origin $TRAVIS_BRANCH
    fi
}

if [ -z $ACQUIA_REPOSITORY ]
then
    echo 'Build successful, commit can not be deployed, please provide $ACQUIA_REPOSITORY environment variable.'
    exit
fi

if [ -z $ACQUIA_HOST ]
then
    echo 'Build successful, commit can not be deployed, please provide $ACQUIA_HOST environment variable.'
    exit
fi

if [ $TRAVIS_PULL_REQUEST != "false" ]
then
    echo "Build successful, pull requests will not be deployed"
    exit
fi

ssh-keyscan $ACQUIA_HOST >> ~/.ssh/known_hosts
deploy_to_acquia
