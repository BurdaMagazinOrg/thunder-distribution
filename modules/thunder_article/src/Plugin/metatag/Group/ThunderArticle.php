<?php

namespace Drupal\thunder_article\Plugin\metatag\Group;

use Drupal\metatag\Plugin\metatag\Group\GroupBase;

/**
 * Curated list of metatags for the Thunder article edit page.
 *
 * @MetatagGroup(
 *   id = "thunder_article",
 *   label = @Translation("Thunder Article"),
 *   description = @Translation("Curated list of metatags for the Thunder article edit page."),
 *   weight = 1
 * )
 */
class ThunderArticle extends GroupBase {}
