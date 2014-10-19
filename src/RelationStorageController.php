<?php

/**
 * @file
 * Contains \Drupal\relation\RelationStorageController.
 */

namespace Drupal\relation;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;

/**
 * Relation controller class
 *
 * This extends the DatabaseStorageController class, adding required special
 * handling for relation revisions, very similar to what's being done with
 * nodes.
 */

class RelationStorageController extends SqlContentEntityStorage {
  public function create(array $values = array()) {
    $account = \Drupal::currentUser();
    $values += array(
      'created' => REQUEST_TIME,
      'uid' => $account->id(),
    );
    return parent::create($values);
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
