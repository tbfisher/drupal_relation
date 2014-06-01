<?php

/**
 * @file
 * Contains \Drupal\relation\Entity\Relation.
 */

namespace Drupal\relation\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\Language;
use Drupal\relation\RelationInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Field\FieldDefinition;

/**
 * Defines relation entity
 *
 * @EntityType(
 *   id = "relation",
 *   label = @Translation("Relation"),
 *   bundle_label = @Translation("Relation type"),
 *   module = "relation",
 *   controllers = {
 *     "access" = "Drupal\relation\RelationAccessController",
 *     "storage" = "Drupal\relation\RelationStorageController",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\relation\RelationListController",
 *     "form" = {
 *       "default" = "Drupal\relation\RelationFormController",
 *       "edit" = "Drupal\relation\RelationFormController",
 *       "delete" = "Drupal\relation\Form\RelationDeleteConfirm"
 *     },
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
 *   links = {
 *     "admin-form" = "relation.type_edit",
 *     "edit-form" = "relation.edit",
 *     "delete-form" = "relation.delete_confirm",
 *   },
 *   bundle_entity_type = "relation_type",
 *   admin_permission = "administer relations",
 *   permission_granularity = "bundle"
 * )
 */
class Relation extends ContentEntityBase implements RelationInterface {
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

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['rid'] = FieldDefinition::create('integer')
      ->setLabel(t('Relation ID'))
      ->setDescription(t('The relation ID.'))
      ->setReadOnly(TRUE);

    $fields['vid'] = FieldDefinition::create('integer')
      ->setLabel(t('Revision ID'))
      ->setDescription(t('The relation revision ID.'))
      ->setReadOnly(TRUE);

    $fields['relation_type'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The relation type.'))
      ->setSetting('target_type', 'relation_type')
      ->setReadOnly(TRUE);

    $fields['uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the relation author.'))
      ->setSettings(array(
        'target_type' => 'user',
        'default_value' => 0,
      ));

    $fields['created'] = FieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the relation was created.'));

    $fields['changed'] = FieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the relation was last edited.'));

    $fields['arity'] = FieldDefinition::create('integer')
      ->setLabel(t('ArityD'))
      ->setDescription(t('Number of endpoints on the Relation.'));

    // Langcode here so edit form saves properly.
    $fields['langcode'] = FieldDefinition::create('language')
      ->setLabel(t('Language code'))
      ->setDescription(t('The relation dummy language code.'));

    return $fields;
  }

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::preSave().
   */
  public function preSave(EntityStorageInterface $storage) {
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

    foreach ($this->endpoints as $endpoint) {
      $entities[$endpoint->entity_type][$endpoint->entity_id] = $endpoint->entity_type;
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

  /**
   * Overrides ContentEntityBase::uuid().
   */
  public function uuid() {
    // We don't have uuid (yet at least)
    return NULL;
  }
}
