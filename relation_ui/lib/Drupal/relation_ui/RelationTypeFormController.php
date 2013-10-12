<?php

/**
 * @file
 * Definition of Drupal\relation_ui\RelationTypeFormController.
 */

namespace Drupal\relation_ui;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityFormController;

/**
 * Form controller for relation edit form.
 */
class RelationTypeFormController extends EntityFormController {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);

    $relation_type = $this->entity;
    $form['#attached']['css'] = array(
      drupal_get_path('module', 'relation_ui') . '/relation_ui.css',
    );
    $form['labels'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('relation-type-form-table'),
      ),
      '#suffix' => '<div class="clearfix"></div>',
    );
    $form['labels']['name'] = array( // use 'name' for /misc/machine-name.js
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#description' => t('Display name of the relation type. This is also used as the predicate in natural language formatters (ie. if A is related to B, you get "A [label] B")'),
      '#default_value' => $relation_type->label,
      '#size' => 40,
      '#required'  => TRUE,
    );
    $form['labels']['relation_type'] = array(
      '#type' => 'machine_name',
      '#default_value' => $relation_type->relation_type,
      '#maxlength' => 32,
      '#disabled' => $relation_type->relation_type,
      '#machine_name' => array(
        'source' => array('labels', 'name'),
        'exists' => 'relation_type_load',
      ),
    );
    $form['labels']['reverse_label'] = array(
      '#type' => 'textfield',
      '#size' => 40,
      '#title' => t('Reverse label'),
      '#description'   => t('Reverse label of the relation type. This is used as the predicate by formatters of directional relations, when you need to display the reverse direction (ie. from the target entity to the source entity). If this is not supplied, the forward label is used.'),
      '#default_value' => $relation_type->reverse_label,
      '#states' => array(
        'visible' => array(
          ':input[name="directional"]' => array('checked' => TRUE),
          ':input[name="advanced[max_arity]"]' => array('!value' => '1'),
        ),
        'required' => array(
          ':input[name="directional"]' => array('checked' => TRUE),
          ':input[name="advanced[max_arity]"]' => array('!value' => '1'),
        ),
      ),
    );
    $form['directional'] = array(
      '#type'           => 'checkbox',
      '#title'          => 'Directional',
      '#description'   => t('A directional relation is one that does not imply the same relation in the reverse direction. For example, a "likes" relation is directional (A likes B does not neccesarily mean B likes A), whereas a "similar to" relation is non-directional (A similar to B implies B similar to A. Non-directional relations are also known as symmetric relations.'),
      '#default_value'  => $relation_type->directional,
      '#states' => array(
        'invisible' => array(
          ':input[name="advanced[max_arity]"]' => array('value' => '1'),
        ),
      ),
    );
    // More advanced options, hide by default.
    $form['advanced'] = array(
      '#type' => 'fieldset',
      '#title' => t('Advanced options'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#weight' => 50,
      '#tree' => TRUE,
    );
    $form['advanced']['transitive'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Transitive'),
      '#description'   => t('A transitive relation implies that the relation passes through intermediate entities (ie. A=>B and B=>C implies that A=>C). For example "Ancestor" is transitive: your ancestor\'s ancestor is also your ancestor. But a "Parent" relation is non-transitive: your parent\'s parent is not your parent, but your grand-parent.'),
      '#default_value'  => $relation_type->transitive,
      '#states' => array(
        'invisible' => array(
          ':input[name="advanced[max_arity]"]' => array('value' => '1'),
        ),
      ),
    );
    $form['advanced']['r_unique'] = array(
      '#type'           => 'checkbox',
      '#title'          => t('Unique'),
      '#description'    => t('Whether relations of this type are unique (ie. they can not contain exactly the same end points as other relations of this type).'),
      '#default_value'  => $relation_type->r_unique,
    );
    // these should probably be changed to numerical (validated) textfields.
    $options = array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8');
    $form['advanced']['min_arity'] = array(
      '#type' => 'select',
      '#title' => t('Minimum Arity'),
      '#options' => $options,
      '#description' => t('Minimum number of entities joined by relations of this type (e.g. three siblings in one relation). <em>In nearly all cases you will want to leave this set to 2</em>.'),
      '#default_value' => $relation_type->min_arity ? $relation_type->min_arity : 2,
    );

    $options = array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '0' => t('Infinite'));
    $form['advanced']['max_arity'] = array(
      '#type' => 'select',
      '#title' => t('Maximum Arity'),
      '#options' => $options,
      '#description' => t('Maximum number of entities joined by relations of this type. <em>In nearly all cases you will want to leave this set to 2</em>.'),
      '#default_value' => isset($relation_type->max_arity) ? $relation_type->max_arity : 2,
    );
    $counter = 0;
    $entity_info = entity_get_info();
    foreach (entity_get_bundles() as $entity_type => $bundles) {
      $counter += 2;
      $entity_label = $entity_info[$entity_type]['label'];
      $options_bundles[$entity_label]["$entity_type:*"] = 'all ' . $entity_label . ' bundles';
      foreach ($bundles as $bundle_id => $bundle) {
        $options_bundles[$entity_label]["$entity_type:$bundle_id"] = $bundle['label'];
        $counter++;
      }
    }
    $form['endpoints'] = array(
      '#type' => 'container',
      '#attributes' => array(
        'class' => array('relation-type-form-table'),
      ),
      '#suffix' => '<div class="clearfix"></div>',
    );
    $form['endpoints']['source_bundles'] = array(
      '#type'          => 'select',
      '#title'         => t('Available source bundles'),
      '#options'       => $options_bundles,
      '#size'          => max(12, $counter),
      '#default_value' => $relation_type->source_bundles,
      '#multiple'      => TRUE,
      '#required'      => TRUE,
      '#description'   => 'Bundles that are not selected will not be available as sources for directional, or end points of non-directional relations relations. Ctrl+click to select multiple. Note that selecting all bundles also include bundles not yet created for that entity type.',
    );
    $form['endpoints']['target_bundles'] = array(
      '#type'          => 'select',
      '#title'         => t('Available target bundles'),
      '#options'       => $options_bundles,
      '#size'          => max(12, $counter),
      '#default_value' => $relation_type->target_bundles,
      '#multiple'      => TRUE,
      '#description'   => 'Bundles that are not selected will not be available as targets for directional relations. Ctrl+click to select multiple.',
      '#states' => array(
        'visible' => array(
          ':input[name="directional"]' => array('checked' => TRUE),
          ':input[name="advanced[max_arity]"]' => array('!value' => '1'),
        ),
        'required' => array(
          ':input[name="directional"]' => array('checked' => TRUE),
          ':input[name="advanced[max_arity]"]' => array('!value' => '1'),
        ),
      ),
    );

    return parent::form($form, $form_state, $relation_type);
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::validate().
   */
  function validate(array $form, array &$form_state) {
    $max_arity = $form_state['values']['advanced']['max_arity'];
    $min_arity = $form_state['values']['advanced']['min_arity'];
    // Empty max arity indicates infinite arity
    if ($max_arity && $min_arity > $max_arity) {
      form_set_error('min_arity', t('Minimum arity must be less than or equal to maximum arity.'));
    }
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityFormController::save().
   */
  function save(array $form, array &$form_state) {
    $relation_type = $this->entity;

    if ($relation_type->save()) {
      drupal_set_message(t('The %relation_type relation type has been saved.', array('%relation_type' => $relation_type->relation_type)));
      $uri = $relation_type->uri();
      $form_state['redirect'] = $uri['path'];
    }
    else {
      drupal_set_message(t('Error saving relation type.', 'error'));
    }
  }
}
