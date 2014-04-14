<?php

/**
 * @file
 * Contains \Drupal\relation_ui\RelationTypeListController.
 */

namespace Drupal\relation_ui;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityControllerInterface;

/**
 * Provides a listing of relation types.
 */
class RelationTypeListController extends ConfigEntityListBuilder implements EntityControllerInterface {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = array(
      'data' => $this->getLabel($entity),
      'class' => array('menu-label'),
    );
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#empty'] = t('No relation types exist. <a href="@link">Add relation type</a>.', array(
      '@link' => \Drupal::url('relation_ui.type_add'),
    ));
    return $build;
  }

}
