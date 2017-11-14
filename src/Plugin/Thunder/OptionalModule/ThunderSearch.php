<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

/**
 * Thunder Search integration.
 *
 * @ThunderOptionalModule(
 *   id = "thunder_search",
 *   label = @Translation("Thunder Search integration"),
 *   description = @Translation("Better search experience for editors and users"),
 *   type = "module",
 *   standardlyEnabled = 1,
 *   weight = -1
 * )
 */
class ThunderSearch extends AbstractOptionalModule {}
