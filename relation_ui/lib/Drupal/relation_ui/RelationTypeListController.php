<?php

/**
 * @file
 * Contains \Drupal\relation_ui\RelationTypeListController.
 */

namespace Drupal\relation_ui;

use Drupal\Component\Utility\String;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityListController;
use Drupal\Core\Entity\EntityControllerInterface;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of relation types.
 */
class RelationTypeListController extends ConfigEntityListController implements EntityControllerInterface {

  /**
   * The url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a NodeTypeFormController object.
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
    $build['#empty'] = t('No relation types available. <a href="@link">Add relation type</a>.', array(
      '@link' => $this->urlGenerator->generateFromPath('admin/structure/relation/add'),
    ));
    return $build;
  }

}
