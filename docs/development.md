# Thunder Development

# Install development environment

## Requirements
- [Acquia DevDesktop](https://dev.acquia.com/downloads)
- [composer](https://getcomposer.org/)
- [npm](https://nodejs.org/en/download/)

```bash
git clone git@github.com:BurdaMagazinOrg/thunder-distribution.git thunder-repo
cd thunder-repo
sh scripts/development/build-thunder.sh
```

This installs Thunder in a directory besides your checkout. Now we have to register the created docroot into Acquia's DevDesktop.
After that we can install the site:
```bash
cd ../thunder
bin/robo site:install devdesktop
```

After that Thunder is successfully installed. Start coding now.

----------

## Drupal Tests

Thunder distribution comes with a set of drupal tests. They can be used to validate Thunder installation or to use provided traits for your own project drupal tests.

#### How to run the tests
In order to execute tests, following steps have to be executed.

Enable the simpletest module. Over administration UI or by drush.

```bash
drush en simpletest
```

To successfully run drupal tests, a Browser with WebDriver is required. You can use one of following:
- [PhantomJS](http://phantomjs.org)
- [Selenium Server Standalone](http://www.seleniumhq.org/download) (* Selenium requires Chrome Browser and [Chrome Driver](https://sites.google.com/a/chromium.org/chromedriver))

Chrome Driver have to be installed in executable path (fe. /usr/local/bin/chromedriver).

It's sufficient to run one of mentioned browsers:
```bash
phantomjs --webdriver=4444
```
or
```bash
selenium-server -p 4444
```

Thunder tests require Mink Selenium2 Driver and that has to be required manually. If you are in your ```docroot``` folder of Thunder installation execute following command:
```bash
composer require "behat/mink-selenium2-driver"
```

After that drupal tests can be executed (if you are in ```docroot``` folder of Thunder installation and composer requirements are installed):
```bash
cd core
php scripts/run-tests.sh --php '/usr/local/bin/php' --verbose --url http://thunder.dev --dburl mysql://drupaluser@127.0.0.1:3306/thunder Thunder
```

This is just an example. For better explanation see [Running PHPUnit tests](https://www.drupal.org/docs/8/phpunit/running-phpunit-tests)

----------

## Coding style

Documentation how to check your code for coding style issues can be found [here](https://github.com/BurdaMagazinOrg/thunder-dev-tools/blob/master/README.md#code-style-guidelines).

----------

## Thunder Travis CI

All Thunder pull requests are execute on [Travis CI](https://travis-ci.org/BurdaMagazinOrg/thunder-distribution). On every pull request tests will be executed (or when new commits are pushed into pull request branch). Tests are executed against PHP versions 5.6 (with drush make install) and 7.1 (with composer install). All code will be checked against coding style.

We support some test execution options. They can be provided in commit message in square brackets []. Here is list of options supported:
- TEST_UPDATE - allowed values: (true), this option will execute custom test path, where update (including execution of update hooks) from latest released version will be tested. This option should be used in case of pull request with update hooks or module update.
- INSTALL_METHOD - allowed values: (drush_make, composer), this options overwrites default install method and it allows to test PH P5.6 and 7.1 with same install method.
- TEST_INSTALLER - allowed values: (true), this option will execute additional tests, that tests installation of Thunder with default language (English) and German. These tests require significant more time to be executed.
- SAUCE_LABS_ENABLED - allowed values: (true), this option will execute tests on [Sauce Labs](https://saucelabs.com), where screenshots and videos of test executions are available for additional investigation of possible problems. This option significantly increases execution time of tests.
