<?php
// provide stage file proxy url if needed
#$config['stage_file_proxy.settings']['origin'] = '';


/**
 * provide basic auth for dev sites
 * change username and password
 */
/* delete this line to enable basic auth for dev  and stage
$username = 'Username';
$password = '1Kdb.snIe8u';

// Make sure Drush keeps working.
// Modified from function drush_verify_cli()
$cli = (php_sapi_name() == 'cli');

$isAcquiaDev = (isset($_ENV['AH_NON_PRODUCTION']) && $_ENV['AH_NON_PRODUCTION']);

// PASSWORD-PROTECT NON-PRODUCTION SITES (i.e. staging/dev)
$needsAuthentication = !$cli && $isAcquiaDev;

// exclude facebook bot from auth. IPs would be more secure, but much harder to implement
$needsAuthentication = $needsAuthentication && !preg_match('#^(?:facebookexternalhit/1[.]1|Facebot$)#', $_SERVER['HTTP_USER_AGENT']);

if ($needsAuthentication) {
  if (!(isset($_SERVER['PHP_AUTH_USER']) && ($_SERVER['PHP_AUTH_USER']==$username && $_SERVER['PHP_AUTH_PW']==$password))) {
    header('WWW-Authenticate: Basic realm="This site is protected"');
    header('HTTP/1.0 401 Unauthorized');
    // Fallback message when the user presses cancel / escape
    echo 'Access denied';
    exit;
  }
}
/**/
