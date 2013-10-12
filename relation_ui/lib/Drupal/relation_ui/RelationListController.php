<?php

/**
 * @file
 * Contains \Drupal\relation_ui\RelationListController.
 */

namespace Drupal\relation_ui;

use Drupal\Component\Utility\String;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListController;
use Drupal\Core\Entity\EntityControllerInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of relation types.
 *
 * @todo: add filters
 */
class RelationListController extends EntityListController implements EntityControllerInterface {

  /**
   * The url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a RelationListController object.
   *
   * @param string $entity_type
   *   The type of entity to be listed.
   * @param array $entity_info
   *   An array of entity info for the entity type.
   * @param \Drupal\Core\Entity\EntityStorageControllerInterface $storage
   *   The entity storage controller class.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke hooks on.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator service.
   */
  public function __construct($entity_type, array $entity_info, EntityStorageControllerInterface $storage, ModuleHandlerInterface $module_handler, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $entity_info, $storage, $module_handler);
    $this->urlGenerator = $url_generator;
  }
  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, $entity_type, array $entity_info) {
    return new static(
      $entity_type,
      $entity_info,
      $container->get('entity.manager')->getStorageController($entity_type),
      $container->get('module_handler'),
      $container->get('url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Title');
    $header['type'] = t('Type');
    $header['relation'] = t('Relation');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = l(t('Relation') . ' ' . $entity->get('rid')->value, 'relation/' . $entity->get('rid')->value);
    $row['type'] = $entity->get('relation_type')->value;
    // Sort entities by their type
    foreach ($entity->get('endpoints')->getValue() as $endpoint) {
      $entities[$endpoint['entity_type']][] = $endpoint['entity_id'];
    }
    foreach ($entities as $type => $ids) {
      foreach (entity_load_multiple($type, $ids) as $endpoint_entity) {
        $uri = $endpoint_entity->uri();
        $relation_entities[] = l($endpoint_entity->label(), $uri['path']);
      }
    }
    $relation_type = relation_type_load($entity->get('relation_type')->value);
    $endpoint_separator = $relation_type->directional ? " → " : " ↔ ";
    $row['relation'] = implode($endpoint_separator, $relation_entities);
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    // Place the edit operation after the operations added by field_ui.module
    // which have the weights 15, 20, 25.
    if (isset($operations['edit'])) {
      $operations['edit']['weight'] = 30;
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['#empty'] = t('No relations available.');
    return $build;
  }

}
