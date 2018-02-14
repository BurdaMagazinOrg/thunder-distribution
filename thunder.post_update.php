<?php

/**
 * @file
 * Post update functions for Thunder.
 */

/**
 * Install the Configuration Selector module.
 */
function thunder_post_update_install_config_selector() {
  \Drupal::service('module_installer')->install(['config_selector']);
}
