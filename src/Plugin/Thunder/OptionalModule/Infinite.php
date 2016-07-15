<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

use Drupal\Core\Action\ActionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigInstallerInterface;

/**
 * Google Analytics.
 *
 * @ThunderOptionalModule(
 *   id = "infinite_article",
 *   label = @Translation("Infinite Theme"),
 * )
 */
class Infinite extends AbstractOptionalModule {

  protected $actionManager;

  protected $configInstaller;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory, ActionManager $actionManager, ConfigInstallerInterface $configInstaller) {

    $this->actionManager = $actionManager;

    $this->configInstaller = $configInstaller;

    parent::__construct($configuration, $plugin_id, $plugin_definition, $entityTypeManager, $configFactory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('plugin.manager.action'),
      $container->get('config.installer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array $formValues) {

    $this->configInstaller->installOptionalConfig();

    // Set infinite theme as default.
    $this->configFactory
      ->getEditable('system.theme')
      ->set('default', 'infinite')
      ->save(TRUE);

    // Ensure that footer block is pre-filled with lazy loading block.
    $articles = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'article',
    ]);

    $resetFooterAction = $this->actionManager->createInstance('node_reset_footer_blocks_action');
    $resetHeaderAction = $this->actionManager->createInstance('node_reset_footer_blocks_action');

    foreach ($articles as $article) {
      $resetFooterAction->execute($article);
      $resetHeaderAction->execute($article);
    }

    // Adding header and footer blocks to default article view.
    /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $display */
    $display = entity_load('entity_view_display',  'node.article.default');

    $display->setComponent('field_header_blocks', [
      'type' => 'entity_reference_entity_view',
      'label' => 'hidden',
      'settings' => [
        'view_mode' => 'default',
      ],
      'weight' => -1,
    ])->setComponent('field_footer_blocks', [
      'type' => 'entity_reference_entity_view',
      'label' => 'hidden',
      'settings' => [
        'view_mode' => 'default',
      ],
      'weight' => 2,
    ])->save();

    $display->save();

    $profilePath = drupal_get_path('profile', 'thunder');
    $this->configFactory
      ->getEditable('infinite.settings')
      ->set('logo.use_default', 0)
      ->set('logo.path', $profilePath . '/themes/thunder/images/thunder-logo-big.png')
      ->save(TRUE);
  }

}
