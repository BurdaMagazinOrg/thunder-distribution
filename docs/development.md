# Thunder Development

----------

## Drupal Tests

Thunder distribution comes with a set of drupal tests. They can be used to validate Thunder installation or to use provided traits for your own project drupal tests.

#### How to run the tests
In order to execute tests:

1. Enable the simpletest module.

To successfully run drupal tests, a Browser with WebDriver is required. You can use one of following: 

* [PhantomJS](http://phantomjs.org)
* [Selenium Server Standalone](http://www.seleniumhq.org/download) (* Selenium requires FireFox Browser)

It's sufficient to run one of mentioned browsers:
```bash
phantomjs --webdriver=4444
```
or
```bash
selenium-server -p 4444
```

After that drupal tests can be executed (if you are in root folder of Thunder installation and composer requirements are installed):
```bash
cd core/
php scripts/run-tests.sh --php '/usr/local/bin/php' --verbose --url http://thunder.dd:8083 --dburl mysql://drupaluser@127.0.0.1:33067/thunder thunder
```

This is just an example. For better explanation see [Running PHPUnit tests](https://www.drupal.org/docs/8/phpunit/running-phpunit-tests)

----------

## Coding style

Documentation how to check your code for coding style issues can be found [here](https://github.com/BurdaMagazinOrg/thunder-dev-tools/blob/master/README.md#code-style-guidelines).
