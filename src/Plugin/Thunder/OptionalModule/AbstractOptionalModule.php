<?php

namespace Drupal\thunder\Plugin\Thunder\OptionalModule;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AbstractOptionalModule.
 */
abstract class AbstractOptionalModule extends PluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs display plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param EntityTypeManagerInterface $entityTypeManager
   *   The entity manager.
   * @param ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
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
      $container->get('config.factory')
    );
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * @param array $formValues
   */
  public function submitForm(array $formValues) {
  }

  /**
   * @param $entityType
   * @param $entityBundle
   * @param $fieldName
   * @param $fieldLabel
   * @param $fieldType
   * @param $fieldWidget
   */
  protected function addField($entityType, $entityBundle, $fieldName, $fieldLabel, $fieldType, $fieldWidget) {

    $fieldStorage = $this->entityTypeManager
      ->getStorage('field_storage_config')
      ->load("$entityType.$fieldName");
    if (empty($fieldStorage)) {
      $fieldStorageDefinition = array(
        'field_name' => $fieldName,
        'entity_type' => $entityType,
        'type' => $fieldType,
      );
      $fieldStorage = $this->entityTypeManager
        ->getStorage('field_storage_config')
        ->create($fieldStorageDefinition);
      $fieldStorage->save();
    }
    $fieldDefinition = array(
      'label' => $fieldLabel,
      'field_name' => $fieldStorage->getName(),
      'entity_type' => $entityType,
      'bundle' => $entityBundle,
      'settings' => [
        'display_default' => '1',
        'display_field' => '1',
      ],
    );
    $field = entity_create('field_config', $fieldDefinition);
    $field->save();
    entity_get_form_display($entityType, $entityBundle, 'default')
      ->setComponent($fieldName, array(
        'type' => $fieldWidget,
      ))
      ->save();
  }

  /**
   * @return int
   */
  public function isStandardlyEnabled() {
    return 0;
  }

}
