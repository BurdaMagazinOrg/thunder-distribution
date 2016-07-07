<?php

namespace Thunder\Installer;


use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;

/**
 * Class ThunderLibraryInstaller.
 */
class ThunderLibraryInstaller extends LibraryInstaller {

  protected $packageTypes;

  /**
   * {@inheritdoc}
   */
  public function getInstallPath(PackageInterface $package) {

    $drupalLibraries = ['enyo/dropzone'];

    if (in_array($package->getPrettyName(), $drupalLibraries)) {
      return getcwd() . '/docroot/libraries/' . explode('/', $package->getPrettyName())[1];
    }

    return parent::getInstallPath($package);

  }

}
