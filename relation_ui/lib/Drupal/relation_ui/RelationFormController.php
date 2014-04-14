<?php

/**
 * @file
 * Definition of Drupal\relation_ui\RelationFormController.
 */

namespace Drupal\relation_ui;

use Drupal\Core\Entity\ContentEntityFormController;

/**
 * Form controller for relation edit form.
 */
class RelationFormController extends ContentEntityFormController {
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
    $form_state['redirect_route'] = $relation->urlInfo();
  }

  public function form(array $form, array &$form_state) {
    $relation = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = t('<em>Editing</em> @label', array('@label' => $relation->label()));;
    }

    return parent::form($form, $form_state, $relation);
  }
}
