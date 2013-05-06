<?php

/**
 * @file
 * Contains \Drupal\relation\Plugin\Core\Entity\Relation.
 */

namespace Drupal\relation\Plugin\Core\Entity;

use Drupal\relation\RelationInterface;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;

/**
 * Defines relation entity
 *
 * @EntityType(
 *   id = "relation",
 *   label = @Translation("Relation"),
 *   module = "relation",
 *   controllers = {
 *     "storage" = "Drupal\relation\RelationStorageController",
 *     "render" = "Drupal\Core\Entity\EntityRenderController",
 *     "form" = {
 *       "default" = "Drupal\relation\RelationFormController"
 *     }
 *   },
 *   base_table = "relation",
 *   revision_table = "relation_revision",
 *   uri_callback = "relation_uri",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "rid",
 *     "revision" = "vid",
 *     "bundle" = "relation_type",
 *     "label" = "rid"
 *   },
 *   bundle_keys = {
 *     "bundle" = "relation_type"
 *   },
 *   route_base_path = "admin/structure/relation/manage/{bundle}"
 * )
 */
class Relation extends Entity implements RelationInterface {

  /**
   * The relation ID.
   */
  public $rid;

  /**
   * The relation revision ID.
   */
  public $vid;

  /**
   * The relation type (bundle).
   */
  public $relation_type;

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->rid;
  }

  /**
   * Overrides Drupal\Core\Entity\Entity::getRevisionId().
   */
  public function getRevisionId() {
    return $this->vid;
  }

  /**
   * Overrides Drupal\Core\Entity\Entity::getRevisionId().
   */
  public function bundle() {
    return $this->relation_type;
  }

  /**
   * Overrides Drupal\Core\Entity\Entity::label().
   */
  public function label($langcode = NULL) {
    return t('Relation @id', array('@id' => $this->id()));
  }

  /**
   * Return endpoints as loaded entities.
   *
   * @param $entity_type
   *   (optional) Filter endpoints by entity type. Return all endpoint entities
   *   if empty.
   */
  function endpoints_load($entity_type = NULL) {
    $entities = array();
    foreach ($this->endpoints[LANGUAGE_NOT_SPECIFIED] as $endpoint) {
      if (!empty($entity_type) && $endpoint['entity_type'] != $entity_type) {
        continue;
      }
      $entities[$endpoint['entity_type']][] = $endpoint['entity_id'];
    }
    if (empty($entities)) {
      return FALSE;
    }
    foreach ($entities as $entity_type => $ids) {
      $entities[$entity_type] = entity_load_multiple($entity_type, $ids);
    }
    return $entities;
  }

  /**
   * Gets the label of the relation type of the given relation
   *
   * @param $relation
   *   A relation object.
   * @param $reverse
   *   optional: whether to get the reverse label (boolean).
   *
   * @return string|NULL
   *   The label of the relation type, or NULL if the relation type
   *   does not exist.
   */
  function relation_type_label($reverse = FALSE) {
    $relation_type = relation_type_load($this->bundle());
    if ($relation_type) {
      return ($relation_type->directional && $reverse) ? $relation_type->reverse_label : $relation_type->label;
    }
  }
}
