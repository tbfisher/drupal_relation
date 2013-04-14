<?php

/**
 * @file
 * Contains \Drupal\relation\Plugin\Core\Entity\RelationType.
 */

namespace Drupal\relation\Plugin\Core\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines relation type entity
 *
 * @Plugin(
 *   id = "relation_type",
 *   label = @Translation("Relation type"),
 *   module = "relation",
 *   controller_class = "Drupal\relation\RelationTypeStorageController",
 *   base_table = "relation_type",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "relation_type",
 *     "label" = "label"
 *   }
 * )
 */
class RelationType extends Entity implements ContentEntityInterface {

  /**
   * The relation type ID.
   */
  public $relation_type;

  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

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
}