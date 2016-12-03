# Change Log

## [8.1.0-rc3](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-rc3) 2016-12-01
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-rc2...8.x-1.0-rc3)

Test improvements
- Added tests for SEO functionality (Metatag, Sitemap)
- Code style tests and fixes
- Update tests to work with current module markup
 
Config changes
- Activated revisions for article and basic page
- Media URLs are required for twitter and instagram entities
- UX: better responsiveness on content list 
- Improve image style for media thumbnails
- UX: Add author filter to content overview
- UX: Add sorting to entity browser views
- Enable auto select on multiple_image_browser 
- Reorganize node and term edit pages
- Auto open media browser
- reduce allowed allowed upload extensions in image browser
- Label form element non-required in the entity form
- Change gallery paragraph to simple widget
- Use responsive images in gallery

Fixes
- Fix entity browser preselection after error
- Add missing svg files for entity browser
- UX dropzone: [Checkmark indicator for upload screen](https://www.drupal.org/node/2696915) 
- UX dropzone: [Implement maxFiles](https://www.drupal.org/node/2633346) 
- UX dropzone: [Improved MultiStep selection display](https://www.drupal.org/node/2823670) 
- Fix term access for unpublished terms
- UX media browser: [Open entity browser with one click from Entity Browser IEF widget](https://www.drupal.org/node/2778305) 
- [Make instagram responsive](https://www.drupal.org/node/2807735) 
- Fix PHP Notice 
- Better entity browser usability on mobile devices

## [8.1.0-rc2](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-rc2) 2016-10-18
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-rc1...8.x-1.0-rc2)

- Update to Drupal 8.2.1
- Introduce functional Javascript testing
- Reorganized travis.yml
- Fix metatag default configuration
- Fix display of default values in meta tag
- Fix media expire
- Fix saving of galleries in entity browser (did not save without saving the article). Yay! [[Needs tests] Entity Browser widget loses selected images in inline entity form](https://www.drupal.org/node/2764889)
- Fix save after reordering a newly added paragraph. Also Yay! [Saving problem in preview mode with IEF items](https://www.drupal.org/node/2804377)
- Fix paragraphs viewmode [IEF Simple Widget not working in paragraphs with preview mode](https://www.drupal.org/node/2722097)
- Fix several schema files
- Fix vanilla slick gallery display
- Move FIA code to a separate module

## [8.1.0-rc1](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-rc1) 2016-09-08
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-beta12...8.x-1.0-rc1)

- Integration of Harbourmaster single sign-on solution
- Nicer content overview with better searchability
- Taxonomy term status is now actually beeing used. Beware: If you have taxonomy terms without a status they will not be shown anymore!
- Improved demo content

## [8.1.0-beta12](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-beta12) 2016-09-08
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-beta11...8.x-1.0-beta12)

- Cleanup Release
- removing obsolete patches
- updating to current module versions, drupal core
- Fine tune editor permissions
- deeper integration of facebook instant articles
- ckeditor cleanups
- updated README.md

## [8.1.0-beta11](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-beta11) 2016-08-23
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-beta10...8.x-1.0-beta11)

- Configuration cleanup
- Paragraphs are closed on default
- Add tour for article creation and paragraph usage
- add nexx integration module
- fix focal point module

## [8.1.0-beta10](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-beta10) 2016-08-16
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-beta9...8.x-1.0-beta10)

- Security update for google analytics module
- Added google adsense module

## [8.1.0-beta9](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-beta9) 2016-07-28
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-beta8...8.x-1.0-beta9)

- Update to drupal 8.1.8
- Remove already merged dropzone patch.
- Add media_entity_slideshow patch, that makes it possible to update slideshows again.

## [8.1.0-beta8](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-beta8) 2016-07-28
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-beta7...8.x-1.0-beta8)

- Update to drupal 8.1.7
- Updated modules to most recent release
- Infinite module and theme are not installed by default
- Gallery is handled by slick
- Changed composer repository from https://packagist.drupal-composer.org to https://packages.drupal.org/8
  be warned, that if you require the distribution that you change this in your composer file as well 
  see [https://www.drupal.org/node/2718229](https://www.drupal.org/node/2718229)
- Installation of front end modules is handled by bower
- Improve test coverage

## [8.1.0-beta7](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-beta7) (2016-06-10)
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-beta6...8.x-1.0-beta7)
- Include media browser and dropzonejs
- Update metatag module to beta8
- Add default content

## [8.x-1.0-beta6](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.x-1.0-beta6) (2016-05-11)
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-beta5...8.x-1.0-beta6)

- Integrate video\_embed\_field
- Add default media paragraphs
- Add a file rename test
