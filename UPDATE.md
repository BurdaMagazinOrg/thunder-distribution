# Update instructions Thunder 2 -> Thunder 3

These are the instructions to manually update to Thunder 3. The most
significant change is the migration to media in core. But we also made
some significant changes to our composer.json.

### General composer.json adjustments
First, we moved the composer package under the thunder namespace. So you
need to replace
```
"burdamagazinorg/thunder": "~8.2",
```
by
```
"thunder/thunder-distribution": "~3.3",
```
in the require section of your composer.json.


We also switched from bower-asset to npm-asset for our frontend-libraries.
In order to get the libraries downloaded to the correct location, please
replace
```
"installer-types": ["bower-asset"],
```
by
```
"installer-types": ["bower-asset", "npm-asset"],
```
in the composer.json of your project and add "type:npm-asset" to the "docroot/libraries/{$name}" section in installer-paths.

You have to update composer now

```
composer update
```

Additionally, we removed some modules from our codebase. If you are using one of
following modules, please require them manually for your project.

```
composer require drupal/views_load_more --no-update
composer require drupal/breakpoint_js_settings --no-update
composer require valiton/harbourmaster --no-update
composer require drupal/riddle_marketplace:~3.0 --no-update
composer require drupal/nexx_integration:~3.0 --no-update
composer require burdamagazinorg/infinite_module:~1.0 --no-update
composer require burdamagazinorg/infinite_theme:~1.0 --no-update
```
### Updating fb_instant_articles
If you are using the fb_instant_articles, please note that the RSS feed url will change
and therefore needs to be updated in the facebook account.

When updating while fb_instant_articles is enabled, there will be an error message like `The "fiafields" plugin does not exist. Valid plugin IDs for Drupal\views\Plugin\ViewsPluginManager are: ...`
this is due to invalid configuration present in the system before the update and can safely be ignored.

### Pre-requirements for the media update
First we should make sure that the latest drush version is installed.
```
composer require drush/drush:~9.7.0 --no-update
```

After that the following steps should be done for the update:

```
composer require drupal/media_entity:^2.0 drupal/media_entity_image drupal/video_embed_field
```

* Make sure that you use the "Media in core" branch for all your
media_* modules. (For the media modules in Thunder, we take care of that)
* Make sure that all your code that uses media_entity API is modified to use the core media API.

See here for more information:
* [Moved a refined version of the contributed Media entity module to core as Media module](https://www.drupal.org/node/2863992)
* [FAQ - Transition from Media Entity to Media in core](https://www.drupal.org/docs/8/core/modules/media/faq-transition-from-media-entity-to-media-in-core#upgrade-instructions-from-media-entity-contrib-to-media-in-core)

### Execute the media update
All you need to do now is:

```
drush updb
drush cr
```

### Cleanup codebase
Now the update is done and you can remove some modules from your project.
```
composer remove drupal/media_entity drupal/media_entity_image
```

## Additional tasks

### Removing support for some entity browser configurations
We removed the compatibility layer for the media_browser and
gallery_browser. If you still relying on these, please move to the image
browser.

## Additional not required tasks:

### Generic view for entity browser
Entity browser views now support contextual parameters. So we removed
views.view.image_browser.yml, views.view.video_browser.yml and
views.view.riddle_browser.yml and added a generic entity browser view
that gets filtered based on the allowed media types of the current
field.

The old views are still supported, but we would recommend to import
views.view.entity_browser.yml and use that in the entity browser.

Steps to migrate:
* Copy config/optional/views.view.entity_browser into your config
directory
* ```
  drush cim
  ```
* Goto admin/config/content/entity_browser
* Use the view "Entity Browser : Entity Browser" in the widgets section
in each of your entity browser configurations

### Moving from video_embed_field to oEmbed
In our default configuration we moved from video_embed_field to media
oEmbed and we recommend it to you, too.

Steps to migrate:
* Add https://www.drupal.org/files/issues/2019-07-03/2997799-29.patch
to your composer.json in the patch section for drupal/video_embed_field, it should look similar to this:

```
        "patches": {
            "drupal/video_embed_field": {
                "Include upgrade path from video_embed_field to oEmbed": "https://www.drupal.org/files/issues/2019-07-03/2997799-29.patch"
            }
        },
```

* ```
  composer update
  ```

* ```
  drush pm:enable vem_migrate_oembed
  ```
* ```
  drush vem:migrate_oembed
  ```
* ```
  drush pm:uninstall video_embed_field
  ```
* Remove the video_embed_field module and patch from your composer.json
