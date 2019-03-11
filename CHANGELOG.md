# Change Log

## [8.2.35](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.35) 2019-02-26
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.34...8.2.35)

Fixes an issue with the scheduled content view.

- Fix[Use revision based scheduler view in Thunder](https://www.drupal.org/project/thunder/issues/3030724)

## [8.2.34](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.34) 2019-02-21
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.33...8.2.34)

Release due to [SA-CORE-2019-003](https://www.drupal.org/sa-core-2019-003). Some small bugfixes go with this release as well.

- Do [Consider SA-CORE-2019-003 updates](https://www.drupal.org/project/thunder/issues/3034656)
- Do [Add scheduler access test](https://www.drupal.org/project/thunder/issues/3028105)
- Fix [Remove unlock choice from delete confirm form](https://www.drupal.org/project/thunder/issues/3025821)
- Fix [Error while adding new translation and having content_moderation enabled](https://www.drupal.org/project/thunder/issues/3029401)
- Fix [Daily tests are failing](https://www.drupal.org/project/thunder/issues/3029977) 


## [8.2.33](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.33) 2019-01-24
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.32...8.2.33)

The scheduler content moderation integration module that we introduced with the last release now provides the required scheduler patch by itself.
That means, that we had to remove the patch from the distribution, otherwise installation will fail.

Additionally, we fix an update bug from the last release. While the update was correctly done in the release, the update was always marked as failed. To check, if your update
was successful, you can take a look at your media entity form-displays. You should have the inline form mode on your media entities.
For more information on this take a look at the corresponding drupal.org issue: https://www.drupal.org/project/thunder/issues/3027152

No features have been added.

- Fix [After update to 8.x-2.32 Pending Thunder updates message persists](https://www.drupal.org/project/thunder/issues/3027152)
- Do [Move scheduler patch to integration module](https://www.drupal.org/project/thunder/issues/3027869)  

## [8.2.32](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.32) 2019-01-17
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.31...8.2.32)

With this release we fix a bug that might occur when using composer to update the AMP module dependencies and a drush import-config issue.
The media edit pages get to look a lot nicer and the content moderation integration was tweaked a bit.

- Fix [Call to a member function setDisplayOptions() on null (Drupal 8.7.x)](https://www.drupal.org/project/thunder/issues/3021430)
- Fix [Error message saving translation](https://www.drupal.org/project/thunder/issues/3019638)
- Fix [Password Policy module cannot be installed by drush config-import](https://www.drupal.org/project/thunder/issues/3025702)
- Fix [The masterminds/html5 dependency gets updated to incompatible version](https://www.drupal.org/project/thunder/issues/3025743)
- Do [Update and cleanup travis integration](https://www.drupal.org/project/thunder/issues/3021420)
- Do [Structure media edit pages](https://www.drupal.org/project/thunder/issues/3018703)
- Do [Add testing of deployment](https://www.drupal.org/project/thunder/issues/3025101)
- Do [Use scheduler_content_moderation_integration](https://www.drupal.org/project/thunder/issues/3025705)

## [8.2.31](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.31) 2018-12-18
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.30...8.2.31)

We fixed and improved several topics regarding the content moderation feature and started to reorganize the distribution 
in preparation for the alpha release of Thunder 3.0. No new features have been added.

- Fix [Cannot update to thunder 8.2.30 with composer](https://www.drupal.org/project/thunder/issues/3019353)
- Fix [WSOD on scheduled nodes after saving](https://www.drupal.org/project/thunder/issues/3018530)
- Fix [Wrong access check for revision reset](https://www.drupal.org/project/thunder/issues/3019596)
- Fix [As a restricted editor I am not be able to edit scheduled nodes](https://www.drupal.org/project/thunder/issues/3020284)
- Do [Demo users should be also authors of demo nodes](https://www.drupal.org/project/thunder/issues/3008594)
- Do [Move optional config from Thunder base modules into distro folder](https://www.drupal.org/project/thunder/issues/3018523)
- Do [Add demo tags, that are connected to our articles](https://www.drupal.org/project/thunder/issues/3008589)
- Do [Add a phpunit.xml.dist file to the profile](https://www.drupal.org/project/thunder/issues/3019694)
- Do [Add config_profile as a dev dependency](https://www.drupal.org/project/thunder/issues/3019992)

## [8.2.30](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.30) 2018-12-04
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.29...8.2.30)

Thunder gets a password policy. It is not activated by default. If you want to use the password policy, you have to 
enable the password policy module. 
Additionally we fix some scheduler bugs with this release and restrict the restricted editor role even more. Finally
some code cleanups have been made.

- Add [Password policy](https://www.drupal.org/project/thunder/issues/2986591)
- Fix [Scheduling to unpublish a node fails](https://www.drupal.org/project/thunder/issues/3016857)
- Fix [Restricted editor has administrative permissions](https://www.drupal.org/project/thunder/issues/3013934)
- Cleanup [Dependency namespacing in .info.yml file](https://www.drupal.org/project/thunder/issues/3005773)
- Cleanup [Don't use a fallback image](https://www.drupal.org/project/thunder/issues/3016936)
- Cleanup [WebDriverTestBase for Thunder JS Tests](https://www.drupal.org/project/thunder/issues/3016916)

## [8.2.29](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.29) 2018-11-21
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.28...8.2.29)

Bugfix release.

- Fix [AMP library not compatible with newest masterminds/html5](https://www.drupal.org/project/thunder/issues/3015230)
- Fix [Article integration tests started to fail](https://www.drupal.org/project/thunder/issues/3012271)

## [8.2.28](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.28) 2018-11-06
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.27...8.2.28)

This release contains a new version of the paragraphs module, which fixes a security issue. See https://www.drupal.org/sa-contrib-2018-073.
Additionally we had several small changes to testing, improved demo content and removed of unused code.

- Do [Update paragraphs module](https://www.drupal.org/project/thunder/issues/3011427)
- Do [Remove implementation of hook_library_info_alter() in thunder.profile](https://www.drupal.org/project/thunder/issues/3008779)
- Add [Default users for testing](https://www.drupal.org/project/thunder/issues/3005411)

## [8.2.27](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.27) 2018-10-18
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.26...8.2.27)

Update Drupal core and contrib modules. Thunder can now be installed from config.

- Do [Make Thunder ready for install from configuration](https://www.drupal.org/project/thunder/issues/3000140)
- Do [Update core and contrib](https://www.drupal.org/project/thunder/issues/3007539)

## [8.2.26](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.26) 2018-10-10
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.25...8.2.26)

Fixes a bug, that we introduced with the last release.

- Fix [Disabled content_moderation breaks Thunder](https://www.drupal.org/project/thunder/issues/3003428)

## [8.2.25](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.25) 2018-09-27
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.24...8.2.25)

With this release we introduce a content moderation integration. When Thunder is freshly installed, you will no longer have
a simple publish checkbox, but three moderation states to save into. This makes it possible to save a draft of an published
article without overwriting the published version.

Additionally it is possible to restrict the permission to publish an article to certain users. We introduced a new "restricted Editor" role
that is only able to create drafts, but not publish those drafts.

When updating from an earlier version of Thunder, you will not automatically get these changes, since it would break your
existing article workflow. If you want to enable the feature, just enable the content moderation module.

Since this functionality is based on Drupal core content moderation and workflow modules, it is possible to add as much
states and state changes to it as you like.

It is strongly recommended to also update to the most recent version of the Thunder admin theme.

Main changes since 8.2.24:

- Do [Be able to create and save a draft without changing the published article](https://www.drupal.org/project/thunder/issues/2820056)
- Fix [Update tests fail after content moderation merge](https://www.drupal.org/project/thunder/issues/3002190)

## [8.2.24](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.24) 2018-09-05
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.21...8.2.24)

With the release of Drupal 8.6 some features that were part of thunder for a long time have been integrated into Drupal
core. With this release we remove the duplicate functionality and rely on cores implementation:

- Core provides now status fields dor taxonomy terms. See https://www.drupal.org/project/drupal/issues/2930996
- Profiles can have have true dependencies, we do not need a workaround for that anymore. See https://www.drupal.org/project/drupal/issues/2952888
- Configurable redirects after installation. See https://www.drupal.org/project/drupal/issues/2776605

We added select2 as a new UX feature for select lists and implemented it for the tags selection. This improves
the auto completion, reordering and deletion of tags.

Additionally we removed several deprecations from code and updated to the newest version of required modules. The update
of the entity_browser module also lead to a small UX improvement regarding replacing of images in the image paragraph.
Instead of two clicks - one for removing the old image, and one to add a new image - you can now replace existing
images with one click.

Main changes since 8.2.21:

- Do [Update to Entity Browser 1.5 and use newly available replace button](https://www.drupal.org/project/thunder/issues/2980452)
- Do [Update to new diff release](https://www.drupal.org/project/thunder/issues/2987483)
- Do [Integrate select2 module](https://www.drupal.org/project/thunder/issues/2988112)
- Do [Prepare Thunder for Drupal 8.6 release](https://www.drupal.org/project/thunder/issues/2995568)
- Do [Use core functionality to define real profile dependencies](https://www.drupal.org/project/thunder/issues/2969454)
- Do [Remove custom code for redirect after installation](https://www.drupal.org/project/thunder/issues/2969459)
- Do [Remove funky optional config install code](https://www.drupal.org/project/thunder/issues/2972637)
- Fix [German installer test is failing on php5.6](https://www.drupal.org/project/thunder/issues/2989749)
- Fix [Reduce deprecation errors](https://www.drupal.org/project/thunder/issues/2986501)

## [8.2.23](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.23) 2018-08-02
## [8.2.22](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.22) 2018-08-02

8.2.22 and 8.2.23 were releases without changes. They were necessary to update the drupal.org tar-ball.

## [8.2.21](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.21) 2018-07-19
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.20...8.2.21)

The release fixes some testing issues, updates some modules and prepares thunder installations for the next admin theme 
release.
The next admin theme will have seven as a base theme. In preparation for this, we enable the seven theme for you. If 
this fails for some reason enable it manually before updating to the thunder_admin theme 1.0.0!

Changes since 8.2.20:

- Fix [thunder_updater breaks caching of toolbar for non-admin users](https://www.drupal.org/project/thunder/issues/2961673)
- Testing fix [Eslint configuration mismatch](https://www.drupal.org/project/thunder/issues/2979376)
- Testing fix [Remove hard coded fixture filename](https://www.drupal.org/project/thunder/issues/2981588)
- Do [Adopt new access_unpublished release](https://www.drupal.org/project/thunder/issues/2979843)
- Do [Prepare distribution for next admin theme version](https://www.drupal.org/project/thunder/issues/2982342)

## [8.2.20](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.20) 2018-06-13
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.19...8.2.20)

This contains the update to the most current paragraphs release. This also means that we switch to the
paragraphs "experimental" widget. The classic widget will still work, but it will not contain the Thunder specific
enhancements like adding paragraphs in between other paragraphs or splitting text paragraphs.
All the Thunder enhancements of paragraphs are now moved out of the distribution into the paragraphs_features module
and can all be enabled and disabled to your liking. While we suggest using the Thunder admin theme, we also made sure, 
that he paragraphs_features module works well with the seven theme.
On update, the paragraphs fields that we ship get automatically updated to the experimental widget and should behave,
as they did before. If you added some paragraph fields on your own, they will stay on the classic widget and will lose
the add in between button. To re-enable the functionality, you will have to manually change the paragraph widget to
experimental and enable the options you need in the widget settings. Options we provide are:

- Enable confirmation on paragraphs remove
- Enable add in between buttons
- Enable split text for text paragraphs

Changes since 8.2.19:

- Do [[META] Move to Paragraphs experimental Widget](https://www.drupal.org/project/thunder/issues/2908887)
- Fix [Do not disable "Autocollapse" and "Collapse / Edit all" options](https://www.drupal.org/project/thunder/issues/2979306)

## [8.2.19](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.19) 2018-06-11
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.18...8.2.19)

Many small bug fixes, that accumulated over time.

- Fix [Paragraphs fields are not translatable](https://www.drupal.org/project/thunder/issues/2961422)
- Fix [Cannot install nexx module version 2](https://www.drupal.org/project/thunder/issues/2965904)
- Fix [Remove unneeded CSS from Thunder profile](https://www.drupal.org/project/thunder/issues/2918085)
- Fix [Make installing of ivw_intergration to be independent of channel vocabulary](https://www.drupal.org/project/thunder/issues/2972658)
- Fix [thunder_update_8112() fails if one of the modules listed in the update is not enabled](https://www.drupal.org/project/thunder/issues/2937285)
- Fix [Upscale twitter image style](https://www.drupal.org/project/thunder/issues/2952793)
- Fix [Term overview page looks broken](https://www.drupal.org/project/thunder/issues/2978093)
- Do [Update core make file to use current drupal release](https://www.drupal.org/project/thunder/issues/2978813)
- Do [Update redirect module](https://www.drupal.org/project/thunder/issues/2977757)
- Go back to lullabot amp library and partially revert [Prepare thunder for Drupal 8.5](https://www.drupal.org/project/thunder/issues/2948955)

## [8.2.18](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.18) 2018-04-26
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.17...8.2.18)

Updating drupal-org-core.make to use Drupal core 8.5.3 which is a security release.
See: https://www.drupal.org/sa-core-2018-004

## [8.2.17](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.17) 2018-04-19
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.16...8.2.17)

Maintenance release with module and drupal core update. Some smaller test fixes and update refactoring.

- Do [Update core and modules](https://www.drupal.org/project/thunder/issues/2961993)
- Fix [Tests fail due to deleted pinterest pin](https://www.drupal.org/project/thunder/issues/2961787)
- Fix [Move thunder_post_update_ensure_config_selector_installed into hook_update_N](https://www.drupal.org/project/thunder/issues/2958735)

## [8.2.16](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.16) 2018-03-28
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.15...8.2.16)

This is a release to reflect the availablity of a new highly critical Drupal release. Everyone using Thunder or Drupal
should update as soon as possible. This release does not contain any code changes. The only difference is the updated
drush make files that lead to a new tar-ball on drupal.org which will contain the security patch for drupal.
For more information about the Drupal release see: https://www.drupal.org/psa-2018-001

## [8.2.15](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.15) 2018-03-08
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.14...8.2.15)

Drupal 8.5.0 release fixes and test fixes. Also starting with this release we will have hard dependencies on the
config selector module, which is required for delivering configuration depending on activated modules.
Special note on Drupal 8.5.0 and the Google AMP integration: The lullabot AMP library is currently not compatible with
the drupal 8.5 dependencies, for this reason we provide the pc-magas/amp library instead.
Since we do not ship core patches anymore, we do not lock Drupal core anymore.

- Fix [Config Selector and Thunder Updater are required by Thunder](https://www.drupal.org/project/thunder/issues/2947051)
- Do [Enable config checks for more modules](https://www.drupal.org/project/thunder/issues/2948617)
- Do [Prepare thunder for Drupal 8.5](https://www.drupal.org/project/thunder/issues/2948955)


## [8.2.14](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.14) 2018-02-22
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.13...8.2.14)

Security update for Drupal core: https://www.drupal.org/SA-CORE-2018-001, additionally several tests have been fixed.
No features were added in this release.

## [8.2.13](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.13) 2018-01-18
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.12...8.2.13)

Thunder now contains a configurable length indicator for text fields. You can attach a length indicator to any text
field, it will show an indication, if the text length is within a given range. We implemented it for the SEO text field.
Additionally we added the redirect module, to automatically add redirects when URLs of articles have changed. As usual
several test improvements have been made.

- Add [[UX] Field length indicator without setting a hard limit](https://www.drupal.org/project/thunder/issues/2931731)
- Add [Update testing using UpdatePathTestBase](https://www.drupal.org/node/2927525)
- Fix [Previous article paths get not redirected to the current url](https://www.drupal.org/project/thunder/issues/2925486)
- Fix [Daily tests are failing with Drush 9](https://www.drupal.org/project/thunder/issues/2936777)
- Do [Update core and contrib](https://www.drupal.org/node/2934289)

## [8.2.12](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.12) 2017-12-20
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.11...8.2.12)

We added a new feature to split paragraphs into two. This was contributed by Telekurier Online Medien GmbH.
Additionally, we fixed the lazy loading of galleries that were below the visible area and improved the liveblog
integration. PHP notices have been removed and links that are not available for a role are not displayed anymore.
Test coverage has been improved to also check for errors on installation and unnecessary optional config installs
have been removed

- Fix [PHP Notice while adding image](https://www.drupal.org/node/2923350)
- Fix [Add liveblog page is not aligned with article design](https://www.drupal.org/node/2924063)
- Fix [Thunder installs all optional config after a module install](https://www.drupal.org/node/2931007)
- Fix [Gallery images below the visible area do not get loaded](https://www.drupal.org/node/2926501)
- Fix [Configuration menu - many useless menu items for SEO role](https://www.drupal.org/node/2828407)
- Change [Use selenium chrome docker image for tests](https://www.drupal.org/node/2924324)
- Change [Make update generation more generic for thunder_updater](https://www.drupal.org/node/2924323)
- Add [Integrate paragraphs split modul](https://www.drupal.org/node/2915666)
- Add [Evaluate drupal database log on automated tests](https://www.drupal.org/node/2923637)

## [8.2.11](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.11) 2017-12-11

This release does not contain any changes. It was necessary to update the tar ball on drupal.org that contained a
version of config_update with a security flaw.
This does not concern people installing and updating thunder with composer, drush or manually. It is only relevant
for people downloading the tar ball from drupal.org.

## [8.2.10](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.10) 2017-11-15
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.9...8.2.10)

This is the release, where we removed lots of dependencies. Additionally we added inline form errors and the
possibility to configure if the remove button for files is shown in the medie entity form or not.

- Add [Integrate inline form errors](https://www.drupal.org/node/2915435)
- Change [Revert "Removing the 'Remove' button of image widgets"](https://www.drupal.org/node/2907100)
- Change [[META] Decouple modules from Thunder](https://www.drupal.org/node/2919194)

## [8.2.9](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.9) 2017-10-11
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.8...8.2.9)

This release mostly fixes problems that occurred with the Drupal 8.4 update. Additionally several tests had to be
updated because of new contrib module versions.

Updating to Drupal 8.4 requires Drush 8.1.12 or newer, we recommend to use Drush 8.1.15 or newer. Additional information
can be found in [Drupal 8.4.0 Changelog](https://www.drupal.org/project/drupal/releases/8.4.0).

All changes and fixes in this release:

- Fix [Update Thunder to Drupal 8.4](https://www.drupal.org/node/2899242)
- Fix [Enabling content translation module results in RuntimeException](https://www.drupal.org/node/2904413)
- Fix [Live blog tests flip flop](https://www.drupal.org/node/2908456)
- Fix [Cron failing because of simple_sitemap](https://www.drupal.org/node/2913792)
- Fix [Failing tests for Device Preview integration](https://www.drupal.org/node/2915158)
- Change [Update media_entity_pinterest to 1.0-beta2](https://www.drupal.org/node/2915378)

## [8.2.8](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.8) 2017-09-25
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.7...8.2.8)

With this release we have improved support for the Seven theme, fixed few issues related to updates of modules and testing tools.
Additionally, we have improved Checklist for updates and Paragraphs integration.

All changes and fixes in this release:

- Add [Thunder does not support seven theme correctly](https://www.drupal.org/node/2901160)
- Change [Break up checkboxes into releases.](https://www.drupal.org/node/2905081)
- Change [Hard to differentiate Instagram and Twitter paragraph after adding](https://www.drupal.org/node/2899620)
- Fix [Pinterest paragraph is not enabled for Taxonomy term pages](https://www.drupal.org/node/2902034)
- Fix [Typo fix @ thunder_updater](https://www.drupal.org/node/2910627)
- Fix [Fix responsive_preview integration tests](https://www.drupal.org/node/2910773)
- Fix [Update entity browser to version 8.x-1.3](https://www.drupal.org/node/2910831)

## [8.2.7](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.7) 2017-08-28
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.6...8.2.7)

This release notably fixes a data loss bug, where changes in an inline entity form where not submitted when collapsing
the paragraph containing the inline entity form. The fix saves remote entities on collapsing a paragraph, this means,
that changes on a referenced entity are saved before the referencing article is changed!

This might be unexpected behaviour, but we renamed the collapse button to "Collapse and save" to reflect this change.

All changes and fixes in this release:

- Fix [Library not loaded in entity_browser form](https://www.drupal.org/node/2900431)
- Change [Remove the 'Remove' button of image widgets](https://www.drupal.org/node/2900663)
- Change [Mark Infinite theme and module as hidden](https://www.drupal.org/node/2901282)
- Add [Test modules if they are reinstallable](https://www.drupal.org/node/2899669)
- Fix [Paragraphs add in between buttons styling is not good](https://www.drupal.org/node/2899917)
- Fix [Saving of collapsed paragraphs with referenced fields doesn't work](https://www.drupal.org/node/2900626)

## [8.2.6](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.6) 2017-08-08
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.5...8.2.6)

- [Add pinterest paragraph](https://www.drupal.org/node/2899059)
- [Add in between paragraph loading notification](https://www.drupal.org/node/2899034)
- Fixes [Cannot reinstall thunder riddle integration](https://www.drupal.org/node/2899661)
- Fixes [Config Error on Liveblog reinstall](https://www.drupal.org/node/2879436)

## [8.2.5](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.5) 2017-07-26
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.4...8.2.5)

- Addition of the content lock module to prevent concurrent editing of articles.
- Fix missing installation of empty fields module on updates
- Fixed installation instructions.

## [8.2.4](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.4) 2017-07-17
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.3...8.2.4)

With this release we are introducing a new admin theme, while there are many obvious visual changes, we also introduced
some usability improvements.

### New features:

- The save button is now fix at the bottom of the screen, no more scrolling down long paragraph lists!
- All paragraphs have a compact, yet helpful preview mode. The preview also has the same height now for every
  paragraph type, which is easier to read and also easier to handle.
- We removed lot of form clutter, forms are much cleaner, margins and paddings are harmonized, unneeded borders
  around field groups are removed.
- Images and other media can be directly edited in the paragraph now, no mor extra click on that edit button,
  no more distracting pop up windows!
- The help texts are now hidden behind small buttons with question marks – so they don’t disturb the nice view but are
  there if you need them
- The sidebar handling for mobile devices has been improved. You can now open and close the bar when you use a small
  device

But this is all just the beginning. We will continue to improve the authoring experience step by step in the future.


### Some hints:

We try to update your paragraphs as good as possible, but if you have heavily modified your system, some paragraphs
will not be looking as we intended. If you want to provide a better look for your custom paragraphs you can do the
following:

For custom paragraph types, custom display settings "Preview" should be enabled and adjusted.
For media bundles we have provided "Paragraph preview" view mode. In order to have proper look for media paragraph,
in paragraph "Preview" view mode for media entity field "Rendered entity" formatter should be selected with view mode
"Paragraph preview". As an example, you can take a look at default image paragraph provided by Thunder.

For text fields that should be displayed in a preview of the paragraph, we are suggesting to use "Trimmed" the text
formatter with max 600 characters. As an example, you can take a look at default text paragraph provided by Thunder.

Additionally the new form display mode "Inline" has been added for media bundles. This form display mode is used for
displaying of inline entity form for media entities in paragraphs. As an example, you can take a look at default image
paragraph provided by Thunder.

If you would like to keep the old behaviour, just require "drupal/thunder_admin": "~1.0" in your root composer.json.

### Thank you:

Many thanks to all people involved in the Thunder authoring experience task force:

Jeannette Mayer, Jessica Simon, Claudia Herwig, Andreas Nickel, Steffen Schlaer, Nico Davis, Maria Pecenka,
Berta Leinweber, Miriam Fuchs, Franziska Fey

Very special thanks to Andreas Krauzberger and Andreas Sahle for being the masterminds and designers of all of this!

## [8.2.3](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.3) 2017-07-03
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.2...8.2.3)

This release adds no new functionality or bug fixes. Similar to release 8.2.1 we require more modules as soft
dependencies. This time we decoupled the checklistapi module.

- Decoupled checklistapi module.
- Refactored updater.

## [8.2.2](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.2) 2017-06-27

[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.1...8.2.2)

- Bump drupal core version in drush make file.

## [8.2.1](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.1) 2017-06-13
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.2.0...8.2.1)

- Decouple the shariff module, so that it can be disabled and removed.
- Fix a notice in Facebook Instant Articles integration
- Update drupal core and contrib modules.

## [8.2.0](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.2.0) 2017-06-01
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.1.5...8.2.0)

Version 2.0 adds new functionality and improved updating to Thunder. We bumped the major version to 2 because there are
incompatibilities in the deployment. We removed the dependency on npm and bower for downloading javascript libraries.
To be able to install Thunder with composer you will have to add

    {
         "type": "composer",
         "url": "https://asset-packagist.org"
    }

to your repositories section in your composer.json and the extra section of the same file should look like this:

    "extra": {
        "installer-types": ["bower-asset"],
        "installer-paths": {
            "docroot/core": ["type:drupal-core"],
            "docroot/libraries/{$name}": [
                "type:drupal-library",
                "type:bower-asset"
            ],
            "docroot/modules/contrib/{$name}": ["type:drupal-module"],
            "docroot/profiles/contrib/{$name}": ["type:drupal-profile"],
            "docroot/themes/contrib/{$name}": ["type:drupal-theme"],
            "drush/contrib/{$name}": ["type:drupal-drush"]
        },
        "enable-patching": true
    },

Also remove this line from the post-install-cmd and post-update-cmd sections:

    "Thunder\\composer\\ScriptHandler::deployLibraries"

You can see those changes in the 2.x branch of [thunder project](https://github.com/BurdaMagazinOrg/thunder-project).

The following features have been added:

- Liveblog
- Responsive Preview
- Access unpublished
- Improved Riddle integration
- Social Buttons
- Diff integration
- Google AMP integration
- Use composer asset-packagist repository instead of npm to download frontend libraries
- Improved Tests
- Improved Instagram preview
- Thunder Updater, provides information on what got updated and what needs manual intervention


## [8.1.5](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.5) 2017-06-01
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.4...8.x-1.5)

- Updates to current module versions
- Remove patches for media_entity_instagram, entity_reference_revisions, better_normalizers and blazy, these are now merged into the corresponding modules
- This also means, that those modules are not anymore version locked in the composer file.

## [8.1.4](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.4) 2017-04-20
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.3...8.x-1.4)

- Security update to Drupal 8.3.1
- catching up with contrib module updates
- Fixes https://www.drupal.org/node/2869222 (Deinstall Thunder Base Theme creates WSOD and reinstalling is not possible)

## [8.1.3](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.3) 2017-04-10
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.2...8.x-1.3)

- Update to drupal 8.3
- Updates for slick, blazy, slick_media, crop, simple_sitemap
- Added Drupal 8.3 compatibility patches for blazy, better_normalizers and entity_reference_revisions

## [8.1.2](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.2) 2017-03-28
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.1...8.x-1.2)

- Update Linkit to 4.3 because of a security release on [2017-03-21](https://www.drupal.org/project/linkit/releases/8.x-4.3)
- Update of contrib modules
- Set a fixed version in build-thunder.make to get Thunder up and running on simplytest.me

## [8.1.1](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.1) 2017-03-20
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0...8.x-1.1)

- Update of contrib modules
- Fixing coding style issues
- Adding new tours (they will just appear for new installations)

## [8.1.0](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0) 2017-01-30
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-rc6...8.x-1.0)

Release of thunder 8.1.0. This is almost identical to the rc6. We fixed a small css bug, and added a fixed the version for
entity_reference_revisions to the one of rc6.

See also:

- [Set entity_reference_revision to a strict version](https://www.drupal.org/node/2848067#comment-11899804)
- [Set entity_reference_revision to a strict version](https://www.drupal.org/node/2848066#comment-11899801)

## [8.1.0-rc6](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-rc6) 2017-01-25
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-rc5...8.x-1.0-rc6)

This release candidate mainly improves the updater service and updates most contrib modules to the newest versions.
An exception to the updates are the blazy and slick modules, which do not work properly after update.
Additionally we now use the pecl yaml extension when validating our yml files, since it is more strict then the previously
used Symfony component.
lat but not least, we introduced fixed creation dates for the demo articles to prevent having articles with exactly the same creation dates.

## [8.1.0-rc5](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-rc5) 2016-12-01
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-rc4...8.x-1.0-rc5)

rc4 introduced a bug where part of the configuration could be invalid after updating under some circumstances. This release just fixes this update bug.
If you already upgraded to rc4 you can check if your update has the problem by exporting your configuration (do a drush config-export) and checking if the following files in the config directory are valid:

- entity_browser.browser.gallery_browser.yml
- entity_browser.browser.multiple_image_browser.yml

I you do not have these files, it is ok, if you have them open them and check that they do not look like this:

entity_browser.browser.gallery_browser.yml

```
display_configuration:
  auto_open: true
```

entity_browser.browser.multiple_image_browser.yml

```
widgets:
  7d7f8f45-f628-48a3-84a8-c962c73f39e8:
    settings:
      auto_select: true
  89532aea-140d-4b9e-96f4-2aa489c095cb:
    settings:
      auto_select: true
```

As you can see, those configurations would be incomplete. If your Files look similar to the other entity_browser.browser.* config files, everything is fine.


## [8.1.0-rc4](https://github.com/BurdaMagazinOrg/thunder-distribution/tree/8.1.0-rc4) 2016-12-01
[Full Changelog](https://github.com/BurdaMagazinOrg/thunder-distribution/compare/8.x-1.0-rc2...8.x-1.0-rc4)

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

## [8.1.0-rc3]
skipped release

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
