<?php

/**
 * @file
 * Contains \Drupal\relation\RelationStorageController.
 */

namespace Drupal\relation;

use Drupal\Core\Entity\DatabaseStorageController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Relation controller class
 *
 * This extends the DatabaseStorageController class, adding required special
 * handling for relation revisions, very similar to what's being done with
 * nodes.
 */
class RelationStorageController extends DatabaseStorageController {
  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::create().
   */
  public function create(array $values) {
    $values += array(
      'created' => REQUEST_TIME,
      'uid' => $GLOBALS['user']->uid,
    );
    return parent::create($values)->getBCEntity();
  }

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::preSave().
   */
  protected function preSave(EntityInterface $relation) {
    $relation->changed = REQUEST_TIME;
    $endpoints = field_get_items($relation, 'endpoints');
    $relation->arity = count($endpoints);
  }

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::buildQuery().
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    // Ensure that uid is taken from the {relation} table
    $query = parent::buildQuery($ids, $revision_id);
    $fields =& $query->getFields();
    $fields['uid']['table'] = 'base';
    $query->addField('revision', 'uid', 'revision_uid');
    $fields['changed']['table'] = 'base';
    $query->addField('revision', 'changed', 'changed');
    return $query;
  }
}
