# Thunder Development

## Install development environment

### Requirements
- [Acquia DevDesktop](https://dev.acquia.com/downloads)
- [composer](https://getcomposer.org/)

### Install Thunder for development
[Follow this instruction to get the code](https://github.com/thunder/thunder-develop/blob/master/README.md)

Now we have to register the created docroot into Acquia's DevDesktop and then we can install the site.

After that, Thunder is successfully installed. Start coding now.

----------

## Update profile configuration

The Thunder distributions ships the config_profile module as a dev
dependency for easier config updates. The workflow for updating config
files that are shipped in the distribution should be:
* Install the latest dev version of Thunder
* Enable the Config Profile module
  ```
  drush en config_profile
  ```
* Make all your changes in the UI
* Export your configuration
  ```
  drush cex
  ```
  The configuration is exported to the chosen config_directory and simultaneously to your profile folder.
* config_profile has now copied all the config changes to the profile
folder
* Put all new config files to the desired folder and add track it in git
* Remove all untracked files
  ```
  git clean -fd
  ```

## Drupal Tests

Thunder distribution comes with a set of drupal tests. They can be used to validate Thunder installation or to use provided traits for your own project drupal tests.

#### How to run the tests
In order to execute tests, following steps have to be executed.

Enable the Simpletest module. Over administration UI or by drush.

```bash
drush -y en simpletest
```

To successfully run drupal tests, a Browser with WebDriver is required. Use selenium chrome docker image.

On Mac you have to alias localhost:
```bash
sudo ifconfig lo0 alias 172.16.123.1
```
```bash
docker run -d -P -p 4444:4444 -v $(pwd)/$(drush eval "echo drupal_get_path('profile', 'thunder');")/tests:/tests \
 --shm-size 256m --add-host="thunder.dd:172.16.123.1" selenium/standalone-chrome:3.14.0-iron
```
Note a specific version of chrome is required due to https://bugs.chromium.org/p/chromedriver/issues/detail?id=2198

To debug a browser you can use following commands:
```bash
docker run -d -P -p 6000:5900 -p 4444:4444 -v $(pwd)/$(drush eval "echo drupal_get_path('profile', 'thunder');")/tests:/tests \
 --shm-size 256m --add-host="thunder.dd:172.16.123.1" selenium/standalone-chrome-debug:3.14.0-iron
```
and connect with you vnc client (on mac you can use finder: go to -> connect to server [⌘K]). Address: `vnc://localhost:6000`, the password is: `secret`

Thunder tests require Mink Selenium2 Driver and that has to be required manually. If you are in your ```docroot``` folder of Thunder installation execute following command:
```bash
composer require "behat/mink-selenium2-driver" "behat/mink-goutte-driver"
```

After that drupal tests can be executed (if you are in ```docroot``` folder of Thunder installation and composer requirements are installed):

```bash
php ./core/scripts/run-tests.sh --php '/usr/local/bin/php' --verbose --url http://thunder.dev --dburl mysql://drupaluser@127.0.0.1:3306/thunder Thunder
```

To speed things up run tests using a database dump:
```bash
DEVDESKTOP_DRUPAL_SETTINGS_DIR="${HOME}/.acquia/DevDesktop/DrupalSettings" \
php ./core/scripts/db-tools.php dump-database-d8-mysql | gzip > thunder.sql.gz

thunderDumpFile=thunder.sql.gz php ./core/scripts/run-tests.sh --php '/usr/local/bin/php' \
--verbose --url http://thunder.dd:8083 --dburl mysql://drupaluser@127.0.0.1:33067/thunder Thunder
```
and run them individually:
```bash
thunderDumpFile=thunder.sql.gz php ./core/scripts/run-tests.sh --php '/usr/local/bin/php' \
--verbose --url http://thunder.dd:8083 --dburl mysql://drupaluser@127.0.0.1:33067/thunder --class "Drupal\Tests\thunder\Functional\InstalledConfigurationTest"
```

This is just an example. For better explanation see [Running PHPUnit tests](https://www.drupal.org/docs/8/phpunit/running-phpunit-tests)

Sometimes tests are executed inside docker container where selenium is running inside other containers and it's not possible to access it over localhost.
Or there are cases when two separated containers are running on the same machine but on different ports (for example Chrome and Firefox selenium containers).
For cases like this you can set environment variable `MINK_DRIVER_ARGS_WEBDRIVER` in following way:

```export MINK_DRIVER_ARGS_WEBDRIVER='["chrome", null, "http://localhost:4444/wd/hub"]'```

That information will be picked up by testing classes and used for selenium endpoint.

----------

## Coding style

Documentation how to check your code for coding style issues can be found [here](https://github.com/BurdaMagazinOrg/thunder-dev-tools/blob/master/README.md#code-style-guidelines).

----------

## Thunder Travis CI

All Thunder pull requests are execute on [Travis CI](https://travis-ci.com/thunder/thunder-distribution). On every pull request tests will be executed (or when new commits are pushed into pull request branch). Tests are executed against PHP version 7.3 (with drush make install and with composer install). All code will be checked against coding style.

We support some test execution options. They can be provided in commit message in square brackets []. Here is list of options supported:
- TEST_UPDATE - this option will execute custom test path, where update (including execution of update hooks) from latest released version will be tested. This option should be used in case of pull request with update hooks or module update.

Example to execute update test path:
```
git commit -m "[TEST_UPDATE] Trigger update test path"
```

----------

## Updating Thunder

Thunder tries to provide updates for every change that was made. That could be changes on existing configurations or adding of new configurations.

### Writing update hooks

To support the creation of update hooks, Thunder integrated the `update_helper` module. That contains several methods to e.g. update existing configuration or enabling modules.

Outputting results of update hook is highly recommended for that we have provided UpdateLogger, it handles output of result properly for `drush` or  UI (`update.php`) update workflow.
That's why every update hook that changes something should log what is changed and was it successful or it has failed. And last line in update hook should be returning of UpdateLogger output.
UpdateLogger service is also used by Thunder Updater and it can be retrieved from it. Here are two examples how to get and use UpdateLogger.
All text logged as as INFO, will be outputted as success in `drush` output.

```php
  // Get service directly.
  /** @var \Drupal\update_helper\UpdateLogger $updateLogger */
  $updateLogger = \Drupal::service('update_helper.logger');

  // Log change success or failures.
  if (...) {
    $updateLogger->info('Change is successful.');
  }
  else {
    $updateLogger->warning('Change has failed.');
  }

  // At end of update hook return result of UpdateLogger::output().
  return $updateLogger->output();
```

Other way to get UpdateLogger is from Update Helper Updater service.
```php
  // Get service from Thunder Updater service.
  /** @var \Drupal\update_helper\Updater $updater */
  $updater = \Drupal::service('update_helper.updater');
  $updateLogger = $updater->logger();

  ...

  // At end of update hook return result of UpdateLogger::output().
  return $updateLogger->output();
```

#### Importing new configurations

You have to create configuration update definition file with global `import_configs` action. For example:
```yaml
__global:
  import_configs:
    - config.to.import
    - config.to.import-no2
```

After that you just have to execute configuration update. For example:
```php
  /** @var \Drupal\update_helper\Updater $updater */
  $updater = \Drupal::service('update_helper.updater');
  $updater->executeUpdate('thunder_article', 'thunder_update_8101');

  return $updater->logger()->output();
```
This update hook will import configurations, that are in a module or profile config directory.

#### Updating existing configuration (with manually defined configuration changes)

Before Drupal\update_helper\Updater::updateConfig() updates existing configuration, it could check the current values of that config. That helps to leave modified, existing configuration in a valid state.

```php
  // List of configurations that should be checked for existence.
  $expectedConfig['content']['field_url'] = [
    'type' => 'instagram_embed',
    'weight' => 0,
    'label' => 'hidden',
    'settings' => [
      'width' => 241,
      'height' => 313,
    ],
    'third_party_settings' => [],
  ];

  // New configuration that should be applied.
  $newConfig['content']['thumbnail'] = [
    'type' => 'image',
    'weight' => 0,
    'region' => 'content',
    'label' => 'hidden',
    'settings' => [
      'image_style' => 'media_thumbnail',
      'image_link' => '',
    ],
    'third_party_settings' => [],
  ];

  /** @var \Drupal\update_helper\Updater $updater */
  $updater = \Drupal::service('update_helper.updater');
  $updater->updateConfig('core.entity_view_display.media.instagram.thumbnail', $newConfig, $expectedConfig);
```

#### Updating existing configuration (with using of generated configuration changes)

With Thunder Updater module, we have provided Drupal Console command that will generate update configuration changes (it's called configuration update definition or CUD). Configuration update definition (CUD) will be stored in `config/update` directory of the module and it can be easily executed with Thunder Updater.

Workflow to generate Thunder configuration update is following:
1. Make clean install of the previous version of Thunder (version for which one you want to create configuration update). For example, if you are merging changes to `develop` branch, then you should install Thunder for that branch
2. When Thunder is installed, make code update (with code update also configuration files will be updated, but not active configuration in database)
3. Execute update hooks if it's necessary (e.g. in case when you have module and/or core updates in your branch)
4. Now is a moment to generate Thunder configuration update code. For that we have provided following drupal console command: `drupal generate:thunder:update`. That command should be executed and there are several information that has to be filled, like module name where all generated data will be saved (CUD file, checklist `update.yml` and update hook function). Then also information for checklist entry, like title, success message and failure message. Command will generate CUD file and save it in `config/update` folder of the module, it will add entry in `update.yml` file for the checklist and it will create update hook function in `<module_name>.install` file.
5. After the command has finished it will display what files are modified and generated. It's always good to make an additional check of generated code.

Additional information about command options are provided with `drupal generate:thunder:update --help` and it's also possible to provide all information directly in command line without using the wizard.

When an update for Thunder is created don't forget to commit your update hook with `[TEST_UPDATE=true]` flag in your commit message, so that it's automatically tested.
