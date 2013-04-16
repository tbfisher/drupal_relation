<?php

/**
 * @file
* Contains \Drupal\relation\RelationTypeStorageController.
*/

namespace Drupal\relation;

use Drupal\Core\Entity\DatabaseStorageController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;

/**
 * Relation type controller class
 */
class RelationTypeStorageController extends DatabaseStorageController {
  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::create().
   */
  public function create(array $values) {
    $values += array(
      'label' => '',
      'reverse_label' => '',
      'min_arity' => 2,
      'max_arity' => 2,
      'directional' => FALSE,
      'transitive' => FALSE,
      'r_unique' => FALSE,
      'source_bundles' => array(),
      'target_bundles' => array(),
      // required because relation_type is a non-auto incremementing primary.
      // drupal_write_record assumes $relation_type is already saved because 
      // $this->relation_type is set.
      'enforceIsNew' => TRUE,
    );
    return parent::create($values)->getBCEntity();
  }

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::preSave().
   */
  protected function preSave(EntityInterface $relation_type) {
    if (!isset($relation_type->relation_type)) {
      throw new EntityStorageException('Bundle property must be set on relation_type entities.');
    }

    if (empty($relation_type->label)) {
      $relation_type->label = $relation_type->relation_type;
    }

    // Directional relations should have a reverse label, but if they don't,
    // or if they are symmetric:
    if (empty($relation_type->reverse_label)) {
      $relation_type->reverse_label = $relation_type->label;
    }
  }

  /**
   * Implements \Drupal\Core\Entity\EntityStorageControllerInterface::postSave().
   */
  protected function postSave(EntityInterface $relation_type, $update) {
    if ($update) {
    // Remove all existing bundles from the relation type before re-adding.
      db_delete('relation_bundles')
        ->condition('relation_type', $relation_type->relation_type)
        ->execute();
    }

    $query = db_insert('relation_bundles')
      ->fields(array('relation_type', 'entity_type', 'bundle', 'r_index'));

    // Source bundles
    foreach ($relation_type->source_bundles as $entity_bundles) {
      list($entity_type, $bundle) = explode(':', $entity_bundles, 2);
      $query->values(array($relation_type->relation_type, $entity_type, $bundle, 0));
    }

    // Target Bundles
    if ($relation_type->directional) {
      foreach ($relation_type->target_bundles as $entity_bundles) {
        list($entity_type, $bundle) = explode(':', $entity_bundles, 2);
        $query->values(array($relation_type->relation_type, $entity_type, $bundle, 1));
      }
    }
    $query->execute();

    // Ensure an instance of endpoints exists on the relation type
    if (!drupal_static('relation_install') && !field_read_instance('relation', 'endpoints', $relation_type->id())) {
      $instance = array(
        'field_name' => 'endpoints',
        'entity_type' => 'relation',
        'bundle' => $relation_type->id(),
      );
      field_create_instance($instance);
    }
  }
}