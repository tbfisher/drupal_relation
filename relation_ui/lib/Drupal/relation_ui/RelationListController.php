<?php

/**
 * @file
 * Contains \Drupal\relation_ui\RelationListController.
 */

namespace Drupal\relation_ui;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListController;

/**
 * Provides a listing of relation types.
 *
 * @todo: add filters
 */
class RelationListController extends EntityListController {

  /**
   * The url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Title');
    $header['relation_type'] = t('Type');
    $header['relation'] = t('Relation');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $url = $entity->urlInfo();
    $row['label'] = \Drupal::l($entity->label(), $url['route_name'], $url['route_parameters'], $url['options']);

    $bundle = entity_load($entity->getEntityType()->getBundleEntityType(), $entity->bundle());
    $bundle_url = $bundle->urlInfo();
    $row['relation_type'] = \Drupal::l($bundle->label(), $bundle_url['route_name'], $bundle_url['route_parameters'], $bundle_url['options']);

    // Sort entities by their type
    foreach ($entity->endpoints as $endpoint) {
      $entities[$endpoint->entity_type][] = $endpoint->entity_id;
    }
    foreach ($entities as $type => $ids) {
      foreach (entity_load_multiple($type, $ids) as $endpoint_entity) {
        $endpoint_url = $endpoint_entity->urlInfo();
        $relation_entities[] = \Drupal::l($endpoint_entity->label(), $endpoint_url['route_name'], $endpoint_url['route_parameters'], $endpoint_url['options']);
      }
    }
    $endpoint_separator = $bundle->directional ? " → " : " ↔ ";
    $row['relation'] = implode($endpoint_separator, $relation_entities);
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#empty'] = t('No relations exist.');
    return $build;
  }

}
