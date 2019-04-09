<?php

namespace Drupal\thunder_article\Plugin\metatag\Group;

use Drupal\metatag\Plugin\metatag\Group\GroupBase;

/**
 * The basic group.
 *
 * @MetatagGroup(
 *   id = "thunder_article",
 *   label = @Translation("Thunder Article"),
 *   description = @Translation("Curated list of metatags for the Thunder article edit page."),
 *   weight = 1
 * )
 */
class Thunder extends GroupBase {
  // Inherits everything from Base.
}
