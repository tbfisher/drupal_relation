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
}

