# Drupal settings for acquia cloud
When deploying to acquia cloud, you can use the example settings files in this directory to provide different settings for different stages.
Copy each file to a file named the same, but without the example prefix and change vthe values in those files as needed.

## settings.acquia.php
The main settings file, based on the standard acquia settings file. Will work on acquia cloud instances and includes the other setting files depending on the environment.
It countains a die() statement in front of the $hash_salt definition, please provide a good hash_salt! 
Replace the "<insert your acquia settings file>" placeholder with the actual settings path file as given by your original acquia settings php.

## settings.prod.php
This file will be included on the prod environment and might contain settings valid for prod only
  
## settings.dev.php
This file will be included on all non prod environments. The example contains a basic auth implementation, for simple access protection on dev and stage environments.

## settings.local.php
This file will be included on local installations. The example file includes development.services.yml to be able to use the cache.backend.null. It is also used to disable css/js aggregation 


