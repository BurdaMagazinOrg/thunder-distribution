<?php

/**
 * @file
 * Contains \Thunder\ScriptHandler.
 */

namespace Thunder;

use Composer\Script\Event;
use Thunder\Installer\ThunderLibraryInstaller;

class ScriptHandler {

  public static function initializeInstaller(Event $event) {

    require_once dirname(__FILE__) . '/Installer/ThunderLibraryInstaller.php';

    $event->getComposer()
      ->getInstallationManager()
      ->addInstaller(new ThunderLibraryInstaller($event->getIO(), $event->getComposer()));
  }


}