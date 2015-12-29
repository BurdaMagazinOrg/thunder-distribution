#!/bin/sh
#
# Cloud Hook: post-code-deploy
#
# The post-code-deploy hook is run whenever you use the Workflow page to
# deploy new code to an environment, either via drag-drop or by selecting
# an existing branch or tag from the Code drop-down list. See
# ../README.md for details.
#
# Usage: post-code-deploy site target-env source-branch deployed-tag repo-url
#                         repo-type

site="$1"
target_env="$2"
source_branch="$3"
deployed_tag="$4"
repo_url="$5"
repo_type="$6"

checkout_path="/var/www/html/${site}.${target_env}"
drush_cmd="drush8 --verbose @${site}.${target_env}"

${drush_cmd} sset system.maintenance_mode 1
${drush_cmd} config-import --source=${checkout_path}/config/staging --yes
${drush_cmd} updatedb --yes
${drush_cmd} config-import --source=${checkout_path}/config/staging --yes
${drush_cmd} sset system.maintenance_mode 0