<?php
/**
 * @file
 */

require_once "phing/Task.php";

class DrupalDbSettingsTask extends Task
{

    private $dbName = null;
    private $dbUser = null;
    private $dbPassword = null;
    private $name = null;


    private $settingsFile = null;

    /**
     * @param null $dbName
     *
     * @return DrupalSettingsTask
     */
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;

        return $this;
    }

    /**
     * @param null $dbPassword
     *
     * @return DrupalSettingsTask
     */
    public function setDbPassword($dbPassword)
    {
        $this->dbPassword = $dbPassword;

        return $this;
    }

    /**
     * @param null $dbUser
     *
     * @return DrupalSettingsTask
     */
    public function setDbUser($dbUser)
    {
        $this->dbUser = $dbUser;

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
     * @param null $name
     */
    public function setName($name) {
        $this->name = $name;
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
\$databases['$this->name']['default'] = array (
  'database' => '$this->dbName',
  'username' => '$this->dbUser',
  'password' => '$this->dbPassword',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

EOT;

        file_put_contents($this->settingsFile, $text, FILE_APPEND);

    }
}