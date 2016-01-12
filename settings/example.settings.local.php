<?php
// enable dev services
$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';

// disable render cache
$settings['cache']['bins']['render'] = 'cache.backend.null';
$config['system.logging']['error_level'] = 'verbose';

// disable css/js preprocessing
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;

// provide stage file proxy url if needed
#$config['stage_file_proxy.settings']['origin'] = '';
