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

### Install drush
Drush is the command line interface to drupal, most administrative and deployment tasks can be performed with it, 
the easiest way to install it is with composer [get composer](https://getcomposer.org/download/). 
For the BDD Drush 8 is required, which is the current dev-master branch of drush.

    ~/your-project-dir $ composer global require drush/drush:dev-master
    ~/your-project-dir $ export PATH="$HOME/.composer/vendor/bin:$PATH"

More information about [drush](http://docs.drush.org/) and [drush installation](http://docs.drush.org/en/master/install/)

## Development setup
### Get thunder
Add the thunder repository to your repository as an upstream repository and fetch the content

    ~/your-project-dir $ git remote add upstream git@github.com:BurdaMagazinOrg/thunder.git
    ~/your-project-dir $ git fetch upstream
    
Now merge the core to your project, you can merge a specific version tag or simply master, which points the most current release

    ~/your-project-dir $ git merge upstream/master

### Install drupal
Use the provided drush make file to create the site in the folder htdocs

    ~/your-project-dir $ drush make --prepare-install thunder/thunder.yml htdocs

The document root of the project resides in the "htdocs" directory of the repository, point your webserver to this
directory. Make sure you have created a MySQL database for your project have your database credentials ready.

Copy the sample settings.local.php into the sited directory and edit the file to fill in your database credentials.

    ~/your-project-dir $ cp thunder/example-settings/settings.local.php htdocs/sites/default/settings.local.php

Include settings.local.php in htdocs/sites/default/settings.php (see the example at the bottom of the file)

Now you can install drupal, first enter the drupal directory and use drush to install the site:

    ~/your-project-dir $ cd htdocs
    ~/your-project-dir/htdocs $ drush site-install thunder --yes --notify --site-name="Project name" --account-name=admin --account-pass=admin --site-mail=admin@example.com
