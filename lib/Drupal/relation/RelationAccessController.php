<?php

/**
 * @file
 * Contains \Drupal\relation\RelationAccessController.
 */

namespace Drupal\relation;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access controller for Relations.
 */
class RelationAccessController extends EntityAccessController {
  /**
   * {@inheritdoc}
   */
  public function access(EntityInterface $entity, $operation, $langcode = Language::LANGCODE_DEFAULT, AccountInterface $account = NULL) {
    if (user_access('administer relations', $account)) {
      return TRUE;
    }
    return parent::access($entity, $operation, $langcode, $account);
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation === 'create' && user_access('create relations', $account)) {
      return TRUE;
    }
    else if ($operation === 'view' && user_access('access relations', $account)) {
      return TRUE;
    }
    else if ($operation === 'update' && user_access('edit relations', $account)) {
      return TRUE;
    }
    else if ($operation === 'delete' && user_access('delete relations', $account)) {
      return TRUE;
    }
  }
}