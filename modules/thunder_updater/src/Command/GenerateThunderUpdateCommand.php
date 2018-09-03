<?php

namespace Drupal\thunder_updater\Command;

use Drupal\Console\Command\Shared\ConfirmationTrait;
use Drupal\Console\Command\Shared\ModuleTrait;
use Drupal\Console\Extension\Manager;
use Drupal\Console\Utils\Site;
use Drupal\thunder_updater\Generator\ThunderUpdateGenerator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Style\DrupalStyle;

/**
 * Generate Thunder update command class.
 *
 * TODO: Add support for version option, where entry in checklist will be added.
 *
 * @Drupal\Console\Annotations\DrupalCommand (
 *     extension="thunder_updater",
 *     extensionType="module"
 * )
 */
class GenerateThunderUpdateCommand extends Command {

  use ModuleTrait;
  use ConfirmationTrait;

  /**
   * Extension manager.
   *
   * Extension manager is needed for ModuleTrait.
   *
   * @var \Drupal\Console\Extension\Manager
   */
  protected $extensionManager;

  /**
   * Update generator for Thunder update hook.
   *
   * @var \Drupal\thunder_updater\Generator\ThunderUpdateGenerator
   */
  protected $generator;

  /**
   * Site.
   *
   * @var \Drupal\Console\Utils\Site
   */
  protected $site;

  /**
   * GenerateThunderUpdateCommand constructor.
   *
   * @param \Drupal\Console\Extension\Manager $extension_manager
   *   Extension manager.
   * @param \Drupal\thunder_updater\Generator\ThunderUpdateGenerator $generator
   *   Thunder update generator.
   * @param \Drupal\Console\Utils\Site $site
   *   Site.
   */
  public function __construct(
    Manager $extension_manager,
    ThunderUpdateGenerator $generator,
    Site $site
  ) {
    $this->extensionManager = $extension_manager;
    $this->generator = $generator;
    $this->site = $site;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('generate:thunder:update')
      ->setDescription($this->trans('commands.generate.thunder.update.description'))
      ->addOption(
        'module',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.thunder.update.options.module')
      )
      ->addOption(
        'update-n',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.thunder.update.options.update-n')
      )
      ->addOption(
        'description',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.thunder.update.options.description')
      )
      ->addOption(
        'success-message',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.thunder.update.options.success-message')
      )
      ->addOption(
        'failure-message',
        NULL,
        InputOption::VALUE_REQUIRED,
        $this->trans('commands.generate.thunder.update.options.failure-message')
      )
      ->addOption(
        'include-modules',
        NULL,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.thunder.update.options.include-modules'),
        ''
      )
      ->addOption(
        'filter-modules',
        NULL,
        InputOption::VALUE_OPTIONAL,
        $this->trans('commands.generate.thunder.update.options.filter-modules'),
        ''
      )
      ->setAliases(['gtu']);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    // @see use Drupal\Console\Command\Shared\ConfirmationTrait::confirmOperation
    if (!$this->confirmOperation()) {
      return 1;
    }

    $module = $input->getOption('module');
    $description = $input->getOption('description');
    $success_message = $input->getOption('success-message');
    $failure_message = $input->getOption('failure-message');
    $include_modules = $input->getOption('include-modules');
    $filter_modules = $input->getOption('filter-modules');

    $update_number = $input->getOption('update-n');
    $last_update_number = $this->getLastUpdate($module);
    if ($update_number <= $last_update_number) {
      throw new \InvalidArgumentException(
        sprintf(
          $this->trans('commands.generate.thunder.update.messages.wrong-update-n'),
          $update_number
        )
      );
    }

    if ($this->generator->generateUpdate($module, $update_number, $include_modules, $filter_modules)) {
      $this->generator->generateHook($module, $update_number, $description);
      $this->generator->generateChecklist($module, $update_number, $description, $success_message, $failure_message);

      $io->info($this->trans('commands.generate.thunder.update.messages.success'));
    }
    else {
      $io->info($this->trans('commands.generate.thunder.update.messages.no-update'));
    }

    return 0;
  }

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $io = new DrupalStyle($input, $output);

    $this->site->loadLegacyFile('/core/includes/update.inc');
    $this->site->loadLegacyFile('/core/includes/schema.inc');

    $module = $input->getOption('module');
    $update_number = $input->getOption('update-n');
    $description = $input->getOption('description');
    $success_message = $input->getOption('success-message');
    $failure_message = $input->getOption('failure-message');

    // If at least one required value is requested by wizard, then request
    // optional values too.
    $use_wizard_for_optional = empty($module) || empty($update_number) || empty($description) || empty($success_message) || empty($failure_message);

    // Get module name where update will be saved.
    if (!$module) {
      // @see Drupal\Console\Command\Shared\ModuleTrait::moduleQuestion
      $module = $this->moduleQuestion($io);
      $input->setOption('module', $module);
    }

    // Get Update N number.
    $last_update_number = $this->getLastUpdate($module);
    $next_update_number = $last_update_number ? ($last_update_number + 1) : 8001;
    if (!$update_number) {
      $update_number = $io->ask(
        $this->trans('commands.generate.thunder.update.questions.update-n'),
        $next_update_number,
        function ($update_number) use ($last_update_number) {
          if (!is_numeric($update_number)) {
            throw new \InvalidArgumentException(
              sprintf(
                $this->trans('commands.generate.thunder.update.messages.wrong-update-n'),
                $update_number
              )
            );
          }
          else {
            if ($update_number <= $last_update_number) {
              throw new \InvalidArgumentException(
                sprintf(
                  $this->trans('commands.generate.thunder.update.messages.wrong-update-n'),
                  $update_number
                )
              );
            }
            return $update_number;
          }
        }
      );

      $input->setOption('update-n', $update_number);
    }

    // Get description from wizard.
    if (!$description) {
      $description = $io->ask(
        $this->trans('commands.generate.thunder.update.questions.description'),
        $this->trans('commands.generate.thunder.update.defaults.description')
      );
      $input->setOption('description', $description);
    }

    // Get success message for checklist.
    if (!$success_message) {
      $success_message = $io->ask(
        $this->trans('commands.generate.thunder.update.questions.success-message'),
        $this->trans('commands.generate.thunder.update.defaults.success-message')
      );
      $input->setOption('success-message', $success_message);
    }

    // Get failure message for checklist.
    if (!$failure_message) {
      $failure_message = $io->ask(
        $this->trans('commands.generate.thunder.update.questions.failure-message'),
        $this->trans('commands.generate.thunder.update.defaults.failure-message')
      );
      $input->setOption('failure-message', $failure_message);
    }

    // Get list of modules that are included in update.
    $include_modules = $input->getOption('include-modules');
    if (!$include_modules && $use_wizard_for_optional) {
      $include_modules = $io->ask(
        $this->trans('commands.generate.thunder.update.questions.include-modules'),
        ' '
      );
      $input->setOption('include-modules', trim($include_modules));
    }

    // Get filter regex from wizard.
    $filter_modules = $input->getOption('filter-modules');
    if (!$filter_modules && $use_wizard_for_optional) {
      $filter_modules = $io->ask(
        $this->trans('commands.generate.thunder.update.questions.filter-modules'),
        ' '
      );
      $input->setOption('filter-modules', trim($filter_modules));
    }
  }

  /**
   * Get last update number.
   *
   * @param string $module
   *   Module name where update hook will placed.
   *
   * @return array|bool|mixed
   *   Returns next update hook number.
   */
  protected function getLastUpdate($module) {
    $this->site->loadLegacyFile('/core/includes/schema.inc');

    return drupal_get_installed_schema_version($module);
  }

}
