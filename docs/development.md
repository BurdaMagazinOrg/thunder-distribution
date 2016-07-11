# Thunder Development

----------

## Behat Tests

Thunder distribution comes with [Behat](http://docs.behat.org) tests. They can be used to validate Thunder installation or to use provided context for your own project Behat tests.

#### How to run Behat tests
In order to execute Behat tests:

1. Rename [behat.local.yml.example](../tests/behat/behat.local.yml.example) in your Thunder installation to "behat.local.yml"
2. Change configuration in that file:
* "base_url" - has to be URL of your Thunder installation
* "drupal_root" - has to be Path to **docroot** of your Thunder installation

To successfully run Behat tests, Browser with WebDriver is required. You can use one of following: 

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

After that Behat tests can be executed (if you are in root folder of Thunder distribution and composer requirements are installed):
```bash
vendor/bin/behat --config tests/behat/behat.travis.yml
```
