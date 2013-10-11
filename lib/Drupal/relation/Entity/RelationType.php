<?php

/**
 * @file
 * Contains \Drupal\relation\Plugin\Core\Entity\RelationType.
 */

namespace Drupal\relation\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\relation\RelationTypeInterface;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Defines relation type entity
 *
 *  - relation_type (required): Relation type machine name (string).
 *  - label: Relation type human-readable name (string). Defaults to
 *    duplicating relation_type.
 *  - directional: whether relation is directional (boolean). Defaults to
 *    FALSE.
 *  - transitive: whether relation is transitive (boolean). Defaults to FALSE.
 *  - r_unique: whether relations of this type are unique (boolean). Defaults
 *    to FALSE.
 *  - min_arity: minimum number of entities in relations of this type
 *    (int >= 2). Defaults to 2.
 *  - max_arity: maximum number of entities in relations of this type
 *    (int >= min_arity). Defaults to 2.
 *  - source_bundles: array containing allowed bundle keys. This is used for
 *    both directional and non-directional relations. Bundle key arrays are
 *    of the form 'entity:bundle', eg. 'node:article', or 'entity:*' for all
 *    bundles of the type.
 *  - target_bundles: array containing arrays allowed target bundle keys.
 *    This is the same format as source_bundles, but is only used for
 *    directional relations.
 *
 * @EntityType(
 *   id = "relation_type",
 *   label = @Translation("Relation type"),
 *   module = "relation",
 *   controllers = {
 *     "storage" = "Drupal\relation\RelationTypeStorageController",
 *     "render" = "Drupal\Core\Entity\EntityRenderController",
 *     "form" = {
 *       "default" = "Drupal\relation\RelationTypeFormController",
 *       "edit" = "Drupal\relation\RelationTypeFormController"
 *     }
 *   },
 *   base_table = "relation_type",
 *   uri_callback = "relation_type_uri",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "relation_type",
 *     "label" = "label"
 *   }
 * )
 */
class RelationType extends ConfigEntityBase implements RelationTypeInterface {

  /**
   * The relation type ID.
   */
  public $relation_type;

  public function __construct(array $values) {
    if (empty($this->in_code_only) && empty($this->bundles_loaded)) {
      // If overridden or not exported at all, reset the bundles before
      // loading from the database to avoid duplication.
      $this->source_bundles = array();
      $this->target_bundles = array();
      foreach (db_query('SELECT relation_type, entity_type, bundle, r_index FROM {relation_bundles} WHERE relation_type = :relation_type', array(':relation_type' => $this->relation_type)) as $record) {
        $endpoint = $record->r_index ? 'target_bundles' : 'source_bundles';
        $this->{$endpoint}[] = "$record->entity_type:$record->bundle";
      }
      // Do not run this twice. ctools static caches the types but runs the
      // subrecord callback on the whole cache, every already loaded relation
      // type.
      $this->bundles_loaded = TRUE;
    }

    parent::__construct($values, 'relation_type');
  }

  /**
   * Implements Drupal\Core\Entity\EntityInterface::id().
   */
  public function id() {
    return $this->relation_type;
  }

  /**
   * Overrides Drupal\Core\Entity\Entity::label().
   */
  public function label($langcode = NULL) {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    if (!isset($this->relation_type)) {
      throw new EntityMalformedException('Bundle property must be set on relation_type entities.');
    }

    if (empty($this->label)) {
      $this->label = $this->relation_type;
    }

    // Directional relations should have a reverse label, but if they don't,
    // or if they are symmetric:
    if (empty($this->reverse_label)) {
      $this->reverse_label = $this->label;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageControllerInterface $storage_controller, $update = TRUE) {
    if ($update) {
      // Remove all existing bundles from the relation type before re-adding.
      db_delete('relation_bundles')
        ->condition('relation_type', $this->relation_type)
        ->execute();
    }

    $query = db_insert('relation_bundles')
      ->fields(array('relation_type', 'entity_type', 'bundle', 'r_index'));

    // Source bundles
    foreach ($this->source_bundles as $entity_bundles) {
      list($entity_type, $bundle) = explode(':', $entity_bundles, 2);
      $query->values(array($this->relation_type, $entity_type, $bundle, 0));
    }

    // Target Bundles
    if ($this->directional) {
      foreach ($this->target_bundles as $entity_bundles) {
        list($entity_type, $bundle) = explode(':', $entity_bundles, 2);
        $query->values(array($this->relation_type, $entity_type, $bundle, 1));
      }
    }
    $query->execute();

    // Ensure endpoints field is attached to relation type.
    relation_add_endpoint_field($this->id());
  }
}
