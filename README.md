# Thunder

[![Build Status](https://travis-ci.org/BurdaMagazinOrg/thunder-distribution.svg?branch=8.x-1.x)](https://travis-ci.org/BurdaMagazinOrg/thunder-distribution)

Thunder is a Drupal 8 distribution for professional publishers. It consists of the current Drupal 8 functionality, lots of useful modules from publishers and industry partners, and an environment which makes it easy to install, deploy and add new functionality.

## What's installed by default?

* Admin Toolbar
* Better Normalizers
* Blazy
* Dropzonejs
* Entity API
* Entity Browser
* Field Group
* Focal Point
* Inline Entity Form
* Libraries API
* Linkit - Enriched linking experience
* Metatag
* Media Entiy
* Media entity image
* Media entity slideshow
* Media entity Instagram
* Media entity twitter
* Paragraphs
* Pathauto
* Scheduler
* Simple XML sitemap
* Slick
* Slick media
* Token
* Video Embed Field

Plus some base configuration:

* A channel taxonomy
* An article content type with some base fields
* Image, gallery, social media and video media types
* A set of commonly used paragraph types
    * Gallery
    * Instagram
    * Image
    * Twitter
    * Video
* Pre configured role for editorial staff
 
## What can be activated optionally?

* Google AdSense integration
* Google Analytics
* Riddle Marketplace
* IVW
* Nexx Video Player
* Facebook Instant Articles


[Here](https://burdamagazinorg.gitbooks.io/thunder/content/) you can find more information about these modules.

## Installation

Download thunder from the [distribution page](https://www.drupal.org/project/thunder) or just do:

```
drush dl thunder
```

If you want to install Thunder with composer (recommended) do the following:

```
composer create-project 'burdamagazinorg/thunder-project:~8.1.0' MYPROJECT --stability dev  --no-interaction
```

For further information follow the [install instructions](https://www.drupal.org/documentation/install). 

## Development

For Development information please take a look at [docs/development.md](docs/development.md).
