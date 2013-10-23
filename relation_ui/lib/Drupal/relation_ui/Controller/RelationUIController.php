<?php

/**
 * @file
 * Contains \Drupal\relation_ui\Controller\RelationController.
 */

namespace Drupal\relation_ui\Controller;

use Drupal\Component\Utility\String;
use Drupal\Core\Controller\ControllerBase;
use Drupal\relation\RelationInterface;

/**
 * Returns responses for Relation UI routes.
 */
class RelationUIController extends ControllerBase {
  /**
   * Displays a relation.
   *
   * @param \Drupal\relation\RelationInterface $relation
   *   The relation to display.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function page(RelationInterface $relation) {
    return $this->entityManager()->getRenderController('relation')->view($relation);
  }

  /**
   * The _title_callback for the relation_ui.view route.
   *
   * @param RelationInterface $relation
   *   A relation entity.
   *
   * @return string
   *   The page title.
   */
  public function pageTitle(RelationInterface $relation) {
    return String::checkPlain($relation->label());
  }
}