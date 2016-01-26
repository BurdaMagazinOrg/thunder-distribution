# Thunder - Burda Drupal Distribution Core

## Status

## General system dependencies
This guide assumes you can setup your database and web server. 

* PHP 5.6
* MySQL > 5.5
* Apache or nginx

## Additional development dependencies
Examples in this readme are written for a posix compliant system like OSX and Linux. Windows works as well, but most 
commands will work differently, please consult the given links to external documentation.

* git
* drush

## Development setup
### Get thunder
Basically there are two different ways to get thunder. The simplest way to get thunder up and running is to use the drupal distribution hosted at drupal org, but if you want to have a more complex development environment you can use the github repository including alle fileas for integration into travis ci and acquia cloud.
#### Simple install from drupal.org
Visit [thunder](https://www.drupal.org/project/thunder) and download the most recent tarball. Follow standard drupal installation instructions as given [here](https://www.drupal.org/documentation/install), but use the previously downloaded thunder tarball instead of drupal. Use the given thunder installation profile when asked to select a profile.

#### Full development build from github
This assumes, that "~/your-project-dir" is your local path to an existing git repository for your project
Add the thunder repository to your repository as an upstream repository and fetch the content

    ~/your-project-dir $ git remote add upstream git@github.com:BurdaMagazinOrg/thunder.git
    ~/your-project-dir $ git fetch upstream
    
Now merge the core to your project, you can merge a specific version tag or simply master, which points the most current release

    ~/your-project-dir $ git merge upstream/master

### Prepare environment
The thunder repository contains several example files which help to configure a working development environment, 
the full development stack consists of github as git repository, travis for continous integration and acquia cloud for hosting.
To make them all play together several configuration files have to be modified.

#### Prepare git
copy .example.gitignore to .gitignore 

#### Install drush and phing
Drush is the command line interface to drupal, most administrative and deployment tasks can be performed with it, 
the easiest way to install it is with composer [get composer](https://getcomposer.org/download/). Phing is used to automate the build process
For thunder drush 8 is required. A composer.json is provided to install drush and phing
locally, or you can install both globally

Local install:

    ~/your-project-dir $ composer install
    
This will install drush and phing inside the vendor directory, to have an easier access, you can install them globally:
 
    ~/your-project-dir $ composer global require drush/drush:dev-master 
    ~/your-project-dir $ composer global require phing/phing:2.*
    ~/your-project-dir $ export PATH="$HOME/.composer/vendor/bin:$PATH"

More information about [drush](http://docs.drush.org/) and [drush installation](http://docs.drush.org/en/master/install/)

#### Install drupal
Copy and edit the example settings files in the settings folder. In settings.php change the placeholder <insert your profile> and <insert your acquia settings file>
you get your acquia settings file include from your acquia clouds settings.php

    ~/your-project-dir $ cp settings/example.settings.acquia.php settings/settings.acquia.php
    ~/your-project-dir $ cp settings/example.settings.local.php settings/settings.local.php

Create your projects drush make file and include the thunder make file in it. Add all additional modules not provided by thunder to this make file.

    ~/your-project-dir $ touch your-project.yml

The file should at least contain the following lines:

    includes:
      thunder:
        thunder.yml
        

Copy example build properties file and change its content to match your environment. Replace the line makefile=thunder.yml with your makefile. 

    ~/your-project-dir $ cp example.build.process build.process

Use phing to create the site in the folder docroot

    ~/your-project-dir $ phing make

The document root of the project resides in the "docroot" directory of the repository, point your webserver to this
directory. Make sure you have created a MySQL database for your project have your database credentials ready.
Include settings.local.php in docroot/sites/default/settings.php (see the example at the bottom of the file)

Now you can install drupal, first enter the drupal directory and use drush to install the site:

    ~/your-project-dir $ cd docroot
    ~/your-project-dir/docroot $ drush site-install standard --yes --notify --site-name="Project name" --account-name=admin --account-pass=admin --site-mail=admin@example.com

#### Prepare travis
Copy .example.travis.yml to .travis.yml, open the file and replace <insert-your-profile> with your installation profile.

    ~/your-project-dir $ cp tracis/.example.travis.yml .travis.yml
 
Copy example.travis.settings.php to travis.settings.php. This is the drupal settings file for travis.

    ~/your-project-dir $ cp tracis/example.travis.settings.php travis.settings.php
