<?php

/**
 * @file
 * Contains \Drupal\relation\Plugin\Core\Entity\Relation.
 */

namespace Drupal\relation\Entity;

use Drupal\Core\Language\Language;
use Drupal\relation\RelationInterface;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityNG;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines relation entity
 *
 * @EntityType(
 *   id = "relation",
 *   label = @Translation("Relation"),
 *   module = "relation",
 *   controllers = {
 *     "access" = "Drupal\relation\RelationAccessController",
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
 *   }
 * )
 */
class Relation extends EntityNG implements RelationInterface {
  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->get('rid')->value;
  }

  /**
   * Overrides Drupal\Core\Entity\Entity::getRevisionId().
   */
  public function getRevisionId() {
    return $this->get('vid')->value;
  }

  /**
   * Overrides Drupal\Core\Entity\Entity::label().
   */
  public function label($langcode = NULL) {
    return t('Relation @id', array('@id' => $this->id()));
  }

  public static function baseFieldDefinitions($entity_type) {
    $properties['rid'] = array(
      'label' => t('Node ID'),
      'description' => t('The node ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['vid'] = array(
      'label' => t('Revision ID'),
      'description' => t('The relation revision ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['relation_type'] = array(
      'label' => t('Type'),
      'description' => t('The relation type.'),
      'type' => 'string_field',
      'read-only' => TRUE,
    );
    $properties['uid'] = array(
      'label' => t('User ID'),
      'description' => t('The user ID of the relation author.'),
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['created'] = array(
      'label' => t('Created'),
      'description' => t('The time that the relation was created.'),
      'type' => 'integer_field',
    );
    $properties['changed'] = array(
      'label' => t('Changed'),
      'description' => t('The time that the relation was last changed.'),
      'type' => 'integer_field',
    );
    $properties['arity'] = array(
      'label' => t('Arity'),
      'description' => t('Number of endpoints on the Relation.'),
      'type' => 'integer_field',
    );
    return $properties;
  }

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::preSave().
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    $this->changed = REQUEST_TIME;
    $this->arity = count($this->endpoints);
  }

  /**
   * Filters endpoints by entity type.
   *
   * Suitable for direct usage with entity_load_multiple().
   *
   * Example:
   *
   * @code
   * $endpoints = $relation->endpoints();
   * $users = entity_load_multiple('user', $endpoints['user']);
   * @endcode
   *
   * Sample return value:
   *
   * @code
   * array(
   *   "node" => array(5),
   *   "user" => array(2),
   * );
   * @endcode
   *
   * @return array
   *   An array where keys are entity type, and values are arrays containing
   *   entity IDs of endpoints.
   */
  function endpoints() {
    $entities = array();

    foreach (field_get_items($this, 'endpoints') as $endpoint) {
      $entities[$endpoint['entity_type']][$endpoint['entity_id']] = $endpoint['entity_id'];
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
