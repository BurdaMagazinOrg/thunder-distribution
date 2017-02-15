# Tag-based cache invalidation for Varnish

This is guide on how to setup Varnish in order to use effective cache invalidation. The idea behind it is that cache tags provided by Drupal 8 are used to invalidate cache (sometimes this action will be called "purge" in the following documentation). In order to achieve tag-based cache invalidation a few modules have to be installed and configured to work in combination with customized cache invalidation scripts provided for Varnish.

### Requirements

1. Varnish service
2. Purge Drupal 8 module (https://www.drupal.org/project/purge)

#### Setup Varnish

The first step is to setup Varnish to accept commands provided by Purge module. At first, we will add the list of servers (IPs) that are allowed to do cache invalidation. That are usually your Drupal 8 servers. The reason for whitelisting Drupal 8 servers is to avoid possible DOS attacks from public IP addresses. At the beginning of the Varnish script file the following code should be added:
```varnish
# Whitelist of Purger servers.
acl purgers {
    "127.0.0.1";
    # Add any other IP addresses that your Drupal 8 runs on and that you
    # want to allow cache invalidation requests from. For example:
    # "192.168.1.0"/24;
}
```
The provided example will whitelist only localhost server to do invalidation of cache.

After that, we need to add a script that will actually handle cache invalidation. Following script code should be added in ```vcl_recv``` subroutine:
```varnish
# Only allow BAN requests from whitelisted IP addresses, listed in the 'purgers' ACL.
if (req.method == "BAN") {
  # Check is client IP whitelisted for cache invalidation.
  if (!client.ip ~ purgers) {
	return (synth(403, "Not allowed."));
  }

  # Logic for the ban, using the Purge-Cache-Tags header. For more info
  # see https://github.com/geerlingguy/drupal-vm/issues/397.
  if (req.http.Purge-Cache-Tags) {
	ban("obj.http.Purge-Cache-Tags ~ " + req.http.Purge-Cache-Tags);
  }
  else {
	return (synth(403, "Purge-Cache-Tags header missing."));
  }

  # Throw a synthetic page so the request won't go to the backend.
  return (synth(200, "Ban added."));
}
```
The following script will accept "BAN" commands from Drupal 8 Purge module and process it accordingly.

After these changes varnish can be restarted and it's ready to accept cache invalidation requests from Drupal 8 Purge module.


#### Install Drupal 8 Purge modules

Purge module provides functionality to expose cache tags in the header of the response. Varnish by default will keep header saved for every cache entry and that information will be used later to invalidate cache entries. You can download purge from https://www.drupal.org/project/purge and additional purge_purger_http is required. It can be downloaded from https://www.drupal.org/project/purge_purger_http. To download them over drush simple execute the following command:
```bash
drush dl purge, purge_purger_http
```

After that enable following modules:
- Purge (purge) - base Purge module
- Purge Tokens (purge_tokens) - required to replace generic cache tag tokens
- Purge UI (purge_ui) - user interface for Purge configuration pages
- Late runtime processor (purge_processor_lateruntime) - purge process, it will trigger cache invalidation on any core cache invalidation (fe. article save, media entity save, etc.)
- Core tags queuer (purge_queuer_coretags) - provides queue core cache tag invalidation
- Generic HTTP Purger (purge_purger_http) - makes BAN request, to execute cache invalidation for Varnish
- Generic HTTP Tags Header (purge_purger_http_tagsheader) - exposes required header for Varnish

To enable modules over drush, execute following command:
```bash
drush en purge, purge_tokens, purge_ui, purge_processor_lateruntime, purge_queuer_coretags, purge_purger_http, purge_purger_http_tagsheader
```

After these modules are enabled, Drupal should provide the ```Purge-Cache-Tags``` header. That header property contains all cache tags for the loaded page.

#### Setup Drupal site to use Purge

On the Drupal 8 site open: Configuration -> Development -> Performance page (```admin/config/development/performance```). Enable caching and set it really high, ideally max period (1 year). Save configuration and after that open Purge configuration page (```admin/config/development/performance/purge```).

On that page do following configuration:
1. Click "Add Purger"
2. Choose "HTTP Bundled Purger"
3. Click "Add"
4. HTTP Bundled Purger will be added with generic name
5. Click drop-down button and choose "Configuration"
6. Set "Name" for Purger (fe. Varnish Bundled HTTP Purger)
7. Adjust Hostname and Port to match your Varnish server
8. Click "Headers"
9. Create Header - Name: ```Purge-Cache-Tags``` - Value: ```[invalidations:separated_pipe]```
10. Click "Save Configuration"

With this created Purger for Varnish, everything should work.
