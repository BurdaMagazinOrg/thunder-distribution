<?php

namespace Drupal\thunder_media\Plugin\ImageEffect;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageInterface;
use Drupal\image\ConfigurableImageEffectBase;
use Drupal\image\Entity\ImageStyle;

/**
 * Resizes an image resource.
 *
 * @ImageEffect(
 *   id = "thunder_media_auto_aspect",
 *   label = @Translation("Auto Aspect"),
 *   description = @Translation("Use different effects depending on whether the image is landscape of portrait shaped. This re-uses other preset definitions, and just chooses between them based on the rule.")
 * )
 */
class AutoAspectEffect extends ConfigurableImageEffectBase {

  /**
   * {@inheritdoc}
   */
  public function applyEffect(ImageInterface $image) {
    $ratio_adjustment = isset($this->configuration['ratio_adjustment']) ? floatval($this->configuration['ratio_adjustment']) : 1;
    $aspect = $image->getWidth() / $image->getHeight();

    // Calculate orientation: width / height * adjustment. If > 1, it's wide.
    $style_name = (($aspect * $ratio_adjustment) > 1) ? $this->configuration['landscape'] : $this->configuration['portrait'];

    if (empty($style_name)) {
      // Do nothing. just return what we've got.
      return TRUE;
    }

    /* @var ImageStyle $style */
    $style = ImageStyle::load($style_name);
    if (empty($style)) {
      // Required preset has gone missing?
      return FALSE;
    }

    // Run the preset actions ourself.
    foreach ($style->getEffects() as $sub_effect) {
      /* @var ImageEffectInterface $sub_effect */
      $sub_effect->applyEffect($image);
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function transformDimensions(array &$dimensions, $uri) {
    if (!isset($dimensions['width']) || !isset($dimensions['height'])) {
      // We cannot know which preset would be executed and thus cannot know the
      // resulting dimensions, unless both styles return the same dimensions:
      $landscape_dimensions = $portrait_dimensions = $dimensions;

      /* @var ImageStyle $landscape_style */
      $landscape_style = ImageStyle::load($this->configuration['landscape']);
      $landscape_style->transformDimensions($landscape_dimensions, $uri);

      /* @var ImageStyle $portrait_style */
      $portrait_style = ImageStyle::load($this->configuration['portrait']);
      $portrait_style->transformDimensions($portrait_dimensions, $uri);

      if ($landscape_dimensions == $portrait_dimensions) {
        $dimensions = $landscape_dimensions;
      }
      else {
        $dimensions['width'] = $dimensions['height'] = NULL;
      }
    }
    else {
      $ratio_adjustment = isset($this->configuration['ratio_adjustment']) ? floatval($this->configuration['ratio_adjustment']) : 1;
      $aspect = $dimensions['width'] / $dimensions['height'];
      $style_name = (($aspect * $ratio_adjustment) > 1) ? $this->configuration['landscape'] : $this->configuration['portrait'];

      /* @var ImageStyle $style */
      $style = ImageStyle::load($style_name);
      $style->transformDimensions($dimensions, $uri);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    $summary = [
      '#theme' => 'image_resize_summary',
      '#data' => $this->configuration,
    ];
    $summary += parent::getSummary();

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'landscape' => NULL,
      'portrait' => NULL,
      'ratio_adjustment' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $image_styles = image_style_options(FALSE);
    $form['landscape'] = [
      '#type' => 'select',
      '#title' => $this->t('Landscape image style'),
      '#options' => $image_styles,
      '#default_value' => $this->configuration['landscape'],
      '#description' => $this->t('Select the image style for landscape images'),
      '#required' => TRUE,
    ];
    $form['portrait'] = [
      '#type' => 'select',
      '#title' => $this->t('Portrait'),
      '#options' => $image_styles,
      '#default_value' => $this->configuration['portrait'],
      '#description' => $this->t('Select the image style for portrait images'),
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['landscape'] = $form_state->getValue('landscape');
    $this->configuration['portrait'] = $form_state->getValue('portrait');
    $this->configuration['ratio_adjustment'] = 1;
  }

}
