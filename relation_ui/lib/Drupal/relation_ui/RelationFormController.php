<?php

/**
 * @file
 * Definition of Drupal\relation_ui\RelationFormController.
 */

namespace Drupal\relation_ui;

use Drupal\Core\Entity\EntityFormController;

/**
 * Form controller for relation edit form.
 */
class RelationFormController extends EntityFormController {
  /**
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   */
  protected function actions(array $form, array &$form_state) {
    $relation = $form_state['build_info']['callback_object']->entity;
    $element = parent::actions($form, $form_state);
    $element['delete']['#access'] = $relation->access('delete');
    return $element;
  }

  function save(array $form, array &$form_state) {
    $relation = $this->getEntity($form_state);
    $relation->save();
    $uri = $relation->uri();
    $form_state['redirect'] = $uri['path'];
  }

  public function form(array $form, array &$form_state) {
    $relation = $this->entity;

    if ($this->operation == 'edit') {
      drupal_set_title(t('<em>Editing</em> @label', array('@label' => $relation->label())), PASS_THROUGH);
    }

    return parent::form($form, $form_state, $relation);
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::delete().
   */
  public function delete(array $form, array &$form_state) {
    $destination = array();
    if (isset($_GET['destination'])) {
      $destination = drupal_get_destination();
      unset($_GET['destination']);
    }
    $relation = $this->getEntity($form_state);
    $form_state['redirect'] = array('relation/' . $relation->id() . '/delete', array('query' => $destination));
  }
}
