<?php

namespace Drupal\Tests\thunder\Functional\Installer;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\UserSession;
use Drupal\Core\Site\Settings;
use Drupal\Core\Test\HttpClientMiddleware\TestHttpClientMiddleware;
use Drupal\FunctionalTests\Installer\InstallerTestBase;
use GuzzleHttp\HandlerStack;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests the interactive installer installing the standard profile.
 *
 * @group ThunderInstaller
 */
class ThunderInstallerTest extends InstallerTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->isInstalled = FALSE;

    $this->setupBaseUrl();

    $this->prepareDatabasePrefix();

    // Install Drupal test site.
    $this->prepareEnvironment();

    // Define information about the user 1 account.
    $this->rootUser = new UserSession([
      'uid' => 1,
      'name' => 'admin',
      'mail' => 'admin@example.com',
      'pass_raw' => $this->randomMachineName(),
    ]);

    // If any $settings are defined for this test, copy and prepare an actual
    // settings.php, so as to resemble a regular installation.
    if (!empty($this->settings)) {
      // Not using File API; a potential error must trigger a PHP warning.
      copy(DRUPAL_ROOT . '/sites/default/default.settings.php', DRUPAL_ROOT . '/' . $this->siteDirectory . '/settings.php');
      $this->writeSettings($this->settings);
    }

    // Note that FunctionalTestSetupTrait::installParameters() returns form
    // input values suitable for a programmed
    // \Drupal::formBuilder()->submitForm().
    // @see InstallerTestBase::translatePostValues()
    $this->parameters = $this->installParameters();

    // Set up a minimal container (required by BrowserTestBase). Set cookie and
    // server information so that XDebug works.
    // @see install_begin_request()
    $global_request = Request::createFromGlobals();
    $request = Request::create($GLOBALS['base_url'] . '/core/install.php', 'GET', [], $global_request->cookies->all(), [], $global_request->server->all());
    $this->container = new ContainerBuilder();
    $request_stack = new RequestStack();
    $request_stack->push($request);
    $this->container
      ->set('request_stack', $request_stack);
    $this->container
      ->setParameter('language.default_values', Language::$defaultValues);
    $this->container
      ->register('language.default', 'Drupal\Core\Language\LanguageDefault')
      ->addArgument('%language.default_values%');
    $this->container
      ->register('string_translation', 'Drupal\Core\StringTranslation\TranslationManager')
      ->addArgument(new Reference('language.default'));
    $this->container
      ->register('http_client', 'GuzzleHttp\Client')
      ->setFactory('http_client_factory:fromOptions');
    $this->container
      ->register('http_client_factory', 'Drupal\Core\Http\ClientFactory')
      ->setArguments([new Reference('http_handler_stack')]);
    $handler_stack = HandlerStack::create();
    $test_http_client_middleware = new TestHttpClientMiddleware();
    $handler_stack->push($test_http_client_middleware(), 'test.http_client.middleware');
    $this->container
      ->set('http_handler_stack', $handler_stack);

    $this->container
      ->set('app.root', DRUPAL_ROOT);
    \Drupal::setContainer($this->container);

    // Setup Mink.
    $this->initMink();

    // Set up the browser test output file.
    $this->initBrowserOutputFile();

    $this->visitInstaller();

    // Select language.
    $this->setUpLanguage();

    // Select profile.
    $this->setUpProfile();

    // Address the requirements problem screen, if any.
    $this->setUpRequirementsProblem();

    // Configure settings.
    $this->setUpSettings();

    // Configure site.
    $this->setUpSite();

    // Configure modules.
    $this->setUpModules();

    if ($this->isInstalled) {
      // Import new settings.php written by the installer.
      $request = Request::createFromGlobals();
      $class_loader = require $this->container->get('app.root') . '/autoload.php';
      Settings::initialize($this->container->get('app.root'), DrupalKernel::findSitePath($request), $class_loader);
      foreach ($GLOBALS['config_directories'] as $type => $path) {
        $this->configDirectories[$type] = $path;
      }

      // After writing settings.php, the installer removes write permissions
      // from the site directory. To allow drupal_generate_test_ua() to write
      // a file containing the private key for drupal_valid_test_ua(), the site
      // directory has to be writable.
      // BrowserTestBase::tearDown() will delete the entire test site directory.
      // Not using File API; a potential error must trigger a PHP warning.
      chmod($this->container->get('app.root') . '/' . $this->siteDirectory, 0777);
      $this->kernel = DrupalKernel::createFromRequest($request, $class_loader, 'prod', FALSE);
      $this->kernel->prepareLegacyRequest($request);
      $this->container = $this->kernel->getContainer();

      // Manually configure the test mail collector implementation to prevent
      // tests from sending out emails and collect them in state instead.
      $this->container->get('config.factory')
        ->getEditable('system.mail')
        ->set('interface.default', 'test_mail_collector')
        ->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpLanguage() {
    // Verify that the distribution name appears.
    $this->assertSession()->responseContains('thunder');
    // Verify that the "Choose profile" step does not appear.
    $this->assertSession()->pageTextNotContains('Choose profile');

    parent::setUpLanguage();
  }

  /**
   * {@inheritdoc}
   */
  protected function setUpProfile() {
    // This step is skipped, because there is a distribution profile.
  }

  /**
   * Final installer step: Configure site.
   */
  protected function setUpSite() {
    $edit = $this->translatePostValues($this->parameters['forms']['install_configure_form']);
    $this->drupalPostForm(NULL, $edit, $this->translations['Save and continue']);
    // If we've got to this point the site is installed using the regular
    // installation workflow.
  }

  /**
   * Setup modules -> subroutine of test setUp process.
   */
  protected function setUpModules() {
    $this->drupalPostForm(NULL, [], $this->translations['Save and continue']);
    $this->isInstalled = TRUE;
  }

  /**
   * Confirms that the installation succeeded.
   */
  public function testInstalled() {
    $this->assertSession()->addressEquals('?tour=1');
    $this->assertSession()->statusCodeEquals(200);
    // Confirm that we are logged-in after installation.
    $this->assertSession()->pageTextContains($this->rootUser->getAccountName());

    // Ensure demo content is installed.
    $this->assertSession()->pageTextContains('Burda Launches Open-Source CMS Thunder');
    $this->assertSession()->pageTextContains('Come to DrupalCon New Orleans');

    /** @var \Drupal\Core\Database\Query\SelectInterface $query */
    $query = \Drupal::database()->select('watchdog', 'w')
      ->condition('severity', 4, '<');

    // Check that there are no warnings in the log after installation.
    $this->assertEquals($query->countQuery()->execute()->fetchField(), 0);

  }

}
