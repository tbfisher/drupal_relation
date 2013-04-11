<?php

/**
 * @file
 * Definition of Drupal\relation\RelationFormController.
 */

namespace Drupal\relation;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityFormController;

/**
 * Form controller for relation edit form.
 */
class RelationFormController extends EntityFormController {
  /**
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   */
  protected function actions(array $form, array &$form_state) {
    $element = parent::actions($form, $form_state);
    $element['delete']['#access'] = user_access('delete relations');
    return $element;
  }
  
  function save(array $form, array &$form_state) {    
    $relation = $this->getEntity($form_state);
    $relation->save();
    $uri = $relation->uri();
    $form_state['redirect'] = $uri['path'];
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