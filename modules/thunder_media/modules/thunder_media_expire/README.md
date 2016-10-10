# Thunder media expire

This module enables you to unpublish your media entites automatically by setting an expire field.

Instructions:
 - "Activate media expire" on admin/structure/media/manage/{media}
 - Specify an expire field
 - Optionally: You are able to provide a fallback entity for unpublished entities

Drupal checks on every cron-run if there are expired media elements. Additionally you can use "drush thunder-media-expire-check" for a manually check
