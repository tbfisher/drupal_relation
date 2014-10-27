<?php

/**
 * @file
 * Contains \Drupal\relation\RelationAccessController.
 */

namespace Drupal\relation;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access controller for Relations.
 */
class RelationAccessController extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($parent =  parent::checkAccess($entity, $operation, $langcode, $account)) {
      return $parent;
    }

    if ($operation === 'create' && $account->hasPermission('create relations')) {
      return TRUE;
    }
    else if ($operation === 'view' && $account->hasPermission('access relations')) {
      return TRUE;
    }
    else if ($operation === 'update' && $account->hasPermission('edit relations')) {
      return TRUE;
    }
    else if ($operation === 'delete' && $account->hasPermission('delete relations')) {
      return TRUE;
    }
  }
}