<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

/**
 * Thunder Search integration.
 *
 * @ThunderOptionalModule(
 *   id = "search_api",
 *   label = @Translation("Thunder Search integration"),
 *   description = @Translation("Better search experience for editors and users"),
 *   type = "module",
 *   weight = -1
 * )
 */
class ThunderSearch extends AbstractOptionalModule {}
