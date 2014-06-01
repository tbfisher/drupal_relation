<?php

/**
 * @file
 * Definition of \Drupal\relation\RelationForm.
 */

namespace Drupal\relation;

use Drupal\Core\Entity\ContentEntityForm;

/**
 * Form object for relation edit form.
 */
class RelationForm extends ContentEntityForm {
  /**
   * Overrides Drupal\Core\Entity\EntityForm::actions().
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
