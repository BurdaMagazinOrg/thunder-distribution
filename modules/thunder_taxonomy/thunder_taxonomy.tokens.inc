<?php

use \Drupal\taxonomy\Entity\Term;
use \Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function thunder_taxonomy_token_info() {

  $termType = array(
    'name' => t('Terms'),
    'description' => t('Tokens related to individual terms.'),
    'needs-data' => 'term',
  );

  // Thunder tokens for terms.
  $term['thunder-term-parents-all'] = array(
    'name' => t('Parents all'),
    'description' => t("Parents all."),
    'type' => 'array',
  );

  return array(
    'types' => array('term' => $termType),
    'tokens' => array('term' => $term),
  );
}

/**
 * Implements hook_tokens().
 */
function thunder_taxonomy_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {

  $replacements = array();

  if ($type == 'term' && !empty($data['term'])) {
    /** @var \Drupal\node\NodeInterface $node */
    $term = $data['term'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'thunder-term-parents-all':

          $replacements[$original] = thunder_taxonomy_get_all_parents($term);
          break;
      }
    }

    if ($parents_tokens = \Drupal::token()->findWithPrefix($tokens, 'thunder-term-parents-all')) {

      if ($parents = thunder_taxonomy_get_all_parents($term)) {

        $replacements += \Drupal::token()
          ->generate('array', $parents_tokens, array('array' => $parents), $options, $bubbleable_metadata);
      }
    }
  }

  return $replacements;
}

/**
 * Get all parents without the root term.
 *
 * @param Term $term
 *   The term to get the parents.
 *
 * @return array
 *   Array of terms.
 */
function thunder_taxonomy_get_all_parents(Term $term) {

  $parents_tokens = [];

  if ($term) {
    $parents = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadAllParents($term->id());

    $parents = array_reverse(array_values($parents));

    if (count($parents) == 1) {
      $parents_tokens = [];
    }
    elseif (count($parents) > 1) {
      array_shift($parents);

      foreach ($parents as $parent) {
        $parents_tokens[] = $parent->getName();
      }

    }
  }
  return $parents_tokens;
}
