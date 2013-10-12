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
 *     "storage" = "Drupal\Core\Config\Entity\ConfigStorageController",
 *     "render" = "Drupal\Core\Entity\EntityRenderController",
 *   },
 *   admin_permission = "administer relation types",
 *   config_prefix = "relation.type",
 *   bundle_of = "relation",
 *   entity_keys = {
 *     "id" = "relation_type",
 *     "label" = "label"
 *   }
 * )
 */
class RelationType extends ConfigEntityBase implements RelationTypeInterface {

  /**
   * The machine name of this relation type.
   *
   * @var string
   */
  public $relation_type;

  /**
   * The human-readable name of this type.
   *
   * @var string
   */
  public $label;

  /**
   * The reverse human-readable name of this type. Only used for directional relations.
   *
   * @var string
   */
  public $reverse_label;

  /**
   * Whether this relation type is directional. If not, all indexes are ignored.
   *
   * @var bool
   */
  public $directional  = FALSE;

  /**
   * Whether this relation type is transitive.
   *
   * @var bool
   */
  public $transitive  = FALSE;

  /**
   * Whether relations of this type are unique.
   *
   * @var bool
   */
  public $r_unique  = FALSE;

  /**
   * The minimum number of rows that can make up a relation of this type.
   *
   * @var int
   */
  public $min_arity  = 2;

  /**
   * The maximum number of rows that can make up a relation of this type. Similar to field cardinality.
   *
   * @var int
   */
  public $max_arity  = 2;

  /**
   * List of entity bundles that can be used as relation sources.
   *
   * @var array
   */
  public $source_bundles;

  /**
   * List of entity bundles that can be used as relation targets.
   *
   * @var array
   */
  public $target_bundles;

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
    parent::postSave($storage_controller, $update);

    // Ensure endpoints field is attached to relation type.
    relation_add_endpoint_field($this->id());
  }
}
