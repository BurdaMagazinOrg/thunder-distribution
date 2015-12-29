<?php
// settings for use with travis
$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'root',
  'password' => '',
  'prefix' => '',
  'host' => '127.0.0.1',
  'port' => 3306,
  'namespace' => 'Drupal\Core\Database\Driver\mysql',
  'driver' => 'mysql',
);


$config_directories['active'] = '../config/active';
$config_directories['staging'] = '../config/staging';

$settings['hash_salt'] = 'change me!';
