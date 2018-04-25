<?php

/**
 * @file
 * Post update functions for Thunder.
 */

/**
 * Switch to paragraphs experimental widget.
 */
function thunder_post_update_switch_to_paragraphs_experimental_widget() {
  /** @var \Drupal\thunder_updater\Updater $thunderUpdater */
  $thunderUpdater = \Drupal::service('thunder_updater');

  /** @var \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler */
  $moduleHandler = \Drupal::moduleHandler();

  $successful = TRUE;

  if (
    !$thunderUpdater->installModules(['thunder__thunder_update_8120' => 'default_content'])
    || !$thunderUpdater->installModules(['thunder__thunder_update_8120' => 'better_normalizers'])
    || !$thunderUpdater->installModules(['thunder__thunder_update_8120' => 'paragraphs_features'])
  ) {
    $successful = FALSE;
  }

  if ($successful) {
    /** @var \Drupal\default_content\Importer $contentImporter */
    $contentImporter = \Drupal::service('default_content.importer');
    try {
      $contentImporter->importContent('thunder_paragraphs');
    }
    catch (Exception $e) {
      $successful = FALSE;
    }
  }

  if ($successful) {
    if (!$thunderUpdater->executeUpdates([['thunder', 'thunder__thunder_update_8120']])) {
      $successful = FALSE;
    }

    if ($moduleHandler->moduleExists('thunder_liveblog')) {
      if (!$thunderUpdater->executeUpdates([['thunder_liveblog', 'thunder__thunder_update_8120']])) {
        $successful = FALSE;
      }
    }
  }

  if ($successful) {
    /** @var \Drupal\Core\Config\ConfigFactoryInterface $configFactory */
    $configFactory = \Drupal::configFactory();

    $config = $configFactory->getEditable('editor.editor.basic_html');
    $items = $config->get('settings.toolbar.rows.0');
    if (!empty($items) && is_array($items)) {
      $last_item = &$items[count($items) - 1];
      if (!empty($last_item['name']) && $last_item['name'] === 'Tools' && ($split_text_index = array_search('SplitTextBefore', $last_item['items'])) !== FALSE) {
        array_splice($last_item['items'], $split_text_index, 1);
        $config->set('settings.toolbar.rows.0', $items)->save();

        $thunderUpdater->logger()->info('Split before option is successfully removed from Basic HTML Editor.');
      }
      else {
        $thunderUpdater->logger()->info('Update did not found split before option from Basic HTML Editor.');
      }
    }
    else {
      $thunderUpdater->logger()->info('Update did not found split before option from Basic HTML Editor.');
    }

    // Uninstall paragraph_split_text module.
    if ($moduleHandler->moduleExists('paragraph_split_text')) {
      /** @var \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller */
      $moduleInstaller = \Drupal::service('module_installer');

      if ($moduleInstaller->uninstall(['paragraph_split_text'])) {
        $thunderUpdater->logger()->info('Module "paragraph_split_text" is successfully removed.');
      }
      else {
        $thunderUpdater->logger()->warning('Module "paragraph_split_text" is not removed.');
        $successful = FALSE;
      }
    }
  }

  if ($successful) {
    $thunderUpdater->checklist()->markUpdatesSuccessful(['thunder__thunder_update_8120']);
  }
  else {
    $thunderUpdater->checklist()->markUpdatesFailed(['thunder__thunder_update_8120']);
  }

  // Output logged messages to related chanel of update execution.
  return $thunderUpdater->logger()->output();
}
