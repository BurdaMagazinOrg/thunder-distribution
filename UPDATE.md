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

Second, we removed some modules from our codebase. If you are using one of
following modules, please add them to the composer.json of your project.
* valiton/harbourmaster
* drupal/riddle_marketplace
* drupal/nexx_integration
* burdamagazinorg/infinite_module
* burdamagazinorg/infinite_theme

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
in the composer.json of your project.

### Pre-requirements for the media update
After that the following steps should be done for the update:
* Add drupal/media_entity ^2.0 to your composer.json
* Add drupal/video_embed_field ^2.0 to your composer.json
* Make sure that you use the "Media in core" branch for all your
media_* modules. (For the media modules in Thunder, we take care of that)
* Make sure that all your media_entity related code is moved to media.

See here for more information:
* [Moved a refined version of the contributed Media entity module to core as Media module](https://www.drupal.org/node/2863992)
* [FAQ - Transition from Media Entity to Media in core](https://www.drupal.org/docs/8/core/modules/media/faq-transition-from-media-entity-to-media-in-core#upgrade-instructions-from-media-entity-contrib-to-media-in-core)

### Execute the media update
All you need to do now is:
```
drush updb
```
Then you will be informed that all your media_entiy code has to be
media in core compatible now. If you are sure that you are ready for the
migration, call the drush command again.
```
drush updb
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
* Add https://www.drupal.org/files/issues/2018-12-11/2997799-22.patch
to your composer.json in the patch section for drupal/video_embed_field
* ```
  drush en vem_migrate_oembed
  ```
* ```
  drush video-embed-media-migrate-oembed
  ```
* ```
  drush pmu video_embed_field
  ```
* Remove the video_embed_field module and patch from your composer.json
