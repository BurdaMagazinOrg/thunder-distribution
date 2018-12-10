# Update instructions Thunder 2 -> Thunder 3

These are the instructions to manually update to Thunder 3. The most
significant change is the migration to media in core.

We also removed some modules from our codebase. If you are using one of
following modules, please add them to the composer.json of your project.
* valiton/harbourmaster
* drupal/riddle_marketplace
* drupal/nexx_integration
* burdamagazinorg/infinite_module
* burdamagazinorg/infinite_theme

After that the following steps should be done for the update:
* Add drupal/media_entity ^2.0 to your composer.json
* Make sure that you use the "Media in core" branch for all your
media_* modules. (For the media modules in Thunder, we take care of that)
* Make sure that all your media_entity related code is moved to media.

See here for more information:
* [Moved a refined version of the contributed Media entity module to core as Media module](https://www.drupal.org/node/2863992)
* [FAQ - Transition from Media Entity to Media in core](https://www.drupal.org/docs/8/core/modules/media/faq-transition-from-media-entity-to-media-in-core#upgrade-instructions-from-media-entity-contrib-to-media-in-core)

All you need to do now is:
```
drush updb
```

