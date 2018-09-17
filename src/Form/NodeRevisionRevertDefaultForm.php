<?php

namespace Drupal\thunder\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\node\Form\NodeRevisionRevertForm;

/**
 * Provides a form for reverting a node revision.
 *
 * @internal
 */
class NodeRevisionRevertDefaultForm extends NodeRevisionRevertForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'node_revision_revert_default_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to revert to the default revision from %revision-date?', ['%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime())]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.node.edit_form', ['node' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {

    $default_revision = $this->getDefaultRevisionId($node);

    $this->revision = $this->nodeStorage->loadRevision($default_revision);
    $form = ConfirmFormBase::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultRevisionId($entity_id) {
    $result = $this->nodeStorage->getQuery()
      ->currentRevision()
      ->condition('nid', $entity_id)
      // No access check is performed here since this is an API function and
      // should return the same ID regardless of the current user.
      ->accessCheck(FALSE)
      ->execute();
    if ($result) {
      return key($result);
    }
  }

}
