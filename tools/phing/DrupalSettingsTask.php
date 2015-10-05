<?php
/**
 * @file
 */

require_once "phing/Task.php";

class DrupalSettingsTask extends Task
{

    private $activeDir = null;
    private $stagingDir = null;

    private $settingsFile = null;

    /**
     * @param null $configDir
     *
     * @return DrupalSettingsTask
     */
    public function setActiveDir($configDir)
    {
        $this->activeDir = $configDir;

        return $this;
    }

    /**
     * @param null $settingsFile
     *
     * @return DrupalSettingsTask
     */
    public function setSettingsFile($settingsFile)
    {
        $this->settingsFile = $settingsFile;

        return $this;
    }

    /**
     * @param null $stagingDir
     *
     * @return DrupalSettingsTask
     */
    public function setStagingDir($stagingDir)
    {
        $this->stagingDir = $stagingDir;

        return $this;
    }


    /**
     * The init method: Do init steps.
     */
    public function init()
    {
        // nothing to do here
    }

    /**
     * The main entry point method.
     */
    public function main()
    {

        $text = <<< EOT
if (file_exists(__DIR__ . '/settings.local.php')) {
   include __DIR__ . '/settings.local.php';
}
\$settings['install_profile'] = 'standard';
\$config_directories['active'] = '$this->activeDir';
\$config_directories['staging'] = '$this->stagingDir';
\$settings['hash_salt'] = 'foo';

EOT;

        file_put_contents($this->settingsFile, $text, FILE_APPEND);

    }
}