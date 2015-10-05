<?php

/**
 * @file
 * A Phing task to run Drush commands.
 */
require_once "phing/Task.php";

/**
 * A Drush CLI parameter.
 */
class DrushParam {

    /**
     * @var string The parameter's value.
     */
    protected $value;

    /**
     * Set the parameter value from a text element.
     *
     * @param mixed $str
     *   The value of the text element.
     */
    public function addText($str) {
        $this->value = (string) $str;
    }

    /**
     * Get the parameter's value.
     *
     * return string
     *   The parameter value.
     */
    public function getValue() {
        return $this->value;
    }

}

/**
 * A Drush CLI option.
 */
class DrushOption {

    /**
     * @var string The option's name.
     */
    protected $name;

    /**
     * @var string The option's value.
     */
    protected $value;

    /**
     * Set the option's name.
     *
     * @param string $str
     *   The option's name.
     */
    public function setName($str) {
        $this->name = (string) $str;
    }

    /**
     * Get the option's name.
     *
     * @return string
     *   The option's name.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set the option's value.
     *
     * @param string $str
     *   The option's value.
     */
    public function setValue($str) {
        $this->value = $str;
    }

    /**
     * Set the option's value from a text element.
     *
     * @param string $str
     *   The value of the text element.
     */
    public function addText($str) {
        $this->value = (string) $str;
    }

    /**
     * Get the option's value.
     *
     * @return string
     *   The option's value.
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @{inheritdoc}
     */
    public function toString() {
        $name  = $this->getName();
        $value = $this->getValue();
        $str = '--' . $name;
        if (!empty($value)) {
            $str .= '=' . $value;
        }
        return $str;
    }

}

/**
 * DrushTask
 *
 * Runs the Drush commad line tool.
 * See https://github.com/drush-ops/drush
 */
class DrushTask extends Task {

    /**
     * @var string The executed Drush command.
     */
    protected $command = NULL;

    /**
     * @var string Path the the Drush binary.
     */
    protected $bin = NULL;

    /**
     * @var string URI of the Drupal site to use.
     */
    protected $uri = NULL;

    /**
     * @var string Drupal root directory to use.
     */
    protected $root = NULL;

    /**
     * @var bool If set, assume 'yes' or 'no' as answer to all prompts.
     */
    protected $assume = NULL;

    /**
     * @var bool If true, simulate all relevant actions.
     */
    protected $simulate = FALSE;

    /**
     * @var bool Use the pipe option.
     */
    protected $pipe = FALSE;

    /**
     * @var array An array of DrushOption.
     */
    protected $options = array();
    /**
     * @var array Am array of DrushParam.
     */
    protected $params = array();

    /**
     * @var string The 'glue' characters used between each line of the returned
     *   output.
     */
    protected $returnGlue = "\n";

    /**
     * @var string The name of a Phing property to assign the Drush command's
     *   output to.
     */
    protected $returnProperty = NULL;

    /**
     * @var bool Display extra information avout the command.
     */
    protected $verbose = FALSE;

    /**
     * @var bool Should the build fail on Drush errors
     */
    protected $haltOnError = TRUE;

    /**
     * @var string The alias of the Drupal site to use.
     */
    protected $alias = NULL;

    /**
     * @var string Path to an additional config file to load.
     */
    protected $config = NULL;

    /**
     * @var string Specifies the list of paths where drush will search for alias
     *   files.
     */
    protected $aliasPath = NULL;

    /**
     * @var bool Whether or not to use color output.
     */
    protected $color = FALSE;

    /**
     * @var PhingFile Working directory.
     */
    protected $dir;

    /**
     * The Drush command to run.
     */
    public function setCommand($str) {
        $this->command = $str;
    }

    /**
     * Path the Drush executable.
     */
    public function setBin($str) {
        $this->bin = $str;
    }

    /**
     * Drupal root directory to use.
     */
    public function setRoot($str) {
        $this->root = $str;
    }

    /**
     * URI of the Drupal to use.
     */
    public function setUri($str) {
        $this->uri = $str;
    }

    /**
     * Assume 'yes' or 'no' to all prompts.
     */
    public function setAssume($var) {
        if (is_string($var)) {
            $this->assume = ($var === 'yes');
        }
        else {
            $this->assume = !!$var;
        }
    }

    /**
     * Simulate all relevant actions.
     */
    public function setSimulate($var) {
        if (is_string($var)) {
            $var = strtolower($var);
            $this->simulate = ($var === 'yes' || $var === 'true');
        }
        else {
            $this->simulate = !!$var;
        }
    }

    /**
     * Use the pipe option.
     */
    public function setPipe($var) {
        if (is_string($var)) {
            $var = strtolower($var);
            $this->pipe = ($var === 'yes' || $var === 'true');
        }
        else {
            $this->pipe = !!$var;
        }
    }

    /**
     * The 'glue' characters used between each line of the returned output.
     */
    public function setReturnGlue($str) {
        $this->returnGlue = (string) $str;
    }

    /**
     * The name of a Phing property to assign the Drush command's output to.
     */
    public function setReturnProperty($str) {
        $this->returnProperty = $str;
    }

    /**
     * Should the task fail on Drush error (non zero exit code).
     */
    public function setHaltOnError($var) {
        if (is_string($var)) {
            $var = strtolower($var);
            $this->haltOnError = ($var === 'yes' || $var === 'true');
        }
        else {
            $this->haltOnError = !!$var;
        }
    }

    /**
     * Parameters for the Drush command.
     */
    public function createParam() {
        $o = new DrushParam();
        $this->params[] = $o;
        return $o;
    }

    /**
     * Options for the Drush command.
     */
    public function createOption() {
        $o = new DrushOption();
        $this->options[] = $o;
        return $o;
    }

    /**
     * Display extra information about the command.
     */
    public function setVerbose($var) {
        if (is_string($var)) {
            $this->verbose = ($var === 'yes');
        }
        else {
            $this->verbose = !!$var;
        }
    }

    /**
     * Site alias.
     */
    public function setAlias($var) {
        if (is_string($var)) {
            $this->alias = $var;
        }
        else {
            $this->alias = NULL;
        }
    }

    /**
     * Path top an additional config file to load.
     */
    public function setConfig($var) {
        if (is_string($var) && !empty($var)) {
            $this->config = $var;
        }
        else {
            $this->config = NULL;
        }
    }

    /**
     * A list of paths where drush will search for alias files.
     */
    public function setAliasPath($var) {
        if (is_string($var) && !empty($var)) {
            $this->aliasPath = $var;
        }
        else {
            $this->aliasPath = NULL;
        }
    }

    /**
     * Whether or not to use color output.
     */
    public function setColor($var) {
        if (is_string($var) && !empty($var)) {
            $this->color = ($var === 'yes');
        }
        else {
            $this->color = (boolean) $var;
        }
    }

    /**
     * Specify the working directory for executing this command.
     *
     * @param PhingFile $dir Working directory
     *
     * @return void
     */
    public function setDir(PhingFile $dir) {
        $this->dir = $dir;
    }

    /**
     * Initialize the task.
     */
    public function init() {
        // Get default properties from project.
        $this->alias = $this->getProject()->getProperty('drush.alias');
        $this->root = $this->getProject()->getProperty('drush.root');
        $this->uri = $this->getProject()->getProperty('drush.uri');
        $this->bin = $this->getProject()->getProperty('drush.bin');
        $this->config = $this->getProject()->getProperty('drush.config');
        $this->aliasPath = $this->getProject()->getProperty('drush.alias-path');
        $this->color = $this->getProject()->getProperty('drush.color');
    }

    /**
     * The main entry point method.
     */
    public function main() {
        $command = array();

        $command[] = !empty($this->bin) ? $this->bin : 'drush';

        if (!empty($this->alias)) {
            $command[] = $this->alias;
        }

        if (empty($this->color)) {
            $option = new DrushOption();
            $option->setName('nocolor');
            $this->options[] = $option;
        }


        if (!empty($this->root)) {
            $option = new DrushOption();
            $option->setName('root');
            $option->addText($this->root);
            $this->options[] = $option;
        }

        if (!empty($this->uri)) {
            $option = new DrushOption();
            $option->setName('uri');
            $option->addText($this->uri);
            $this->options[] = $option;
        }

        if (!empty($this->config)) {
            $option = new DrushOption();
            $option->setName('config');
            $option->addText($this->config);
            $this->options[] = $option;
        }

        if (!empty($this->aliasPath)) {
            $option = new DrushOption();
            $option->setName('alias-path');
            $option->addText($this->uri);
            $this->options[] = $option;
        }

        if (is_bool($this->assume)) {
            $option = new DrushOption();
            $option->setName(($this->assume ? 'yes' : 'no'));
            $this->options[] = $option;
        }

        if ($this->simulate) {
            $option = new DrushOption();
            $option->setName('simulate');
            $this->options[] = $option;
        }

        if ($this->pipe) {
            $option = new DrushOption();
            $option->setName('pipe');
            $this->options[] = $option;
        }

        if ($this->verbose) {
            $option = new DrushOption();
            $option->setName('verbose');
            $this->options[] = $option;
        }

        foreach ($this->options as $option) {
            $command[] = $option->toString();
        }

        $command[] = $this->command;

        foreach ($this->params as $param) {
            $command[] = $param->getValue();
        }

        $command = implode(' ', $command);

        if ($this->dir !== NULL) {
            $currdir = getcwd();
            @chdir($this->dir->getPath());
        }

        // Execute Drush.
        $this->log("Executing '$command'...");
        $output = array();
        exec($command, $output, $return);

        if (isset($currdir)) {
            @chdir($currdir);
        }

        // Collect Drush output for display through Phing's log.
        foreach ($output as $line) {
            $this->log($line);
        }
        // Set value of the 'pipe' property.
        if (!empty($this->returnProperty)) {
            $this->getProject()->setProperty($this->returnProperty, implode($this->returnGlue, $output));
        }
        // Build fail.
        if ($this->haltOnError && $return != 0) {
            throw new BuildException("Drush exited with code $return");
        }
        return $return != 0;
    }

}