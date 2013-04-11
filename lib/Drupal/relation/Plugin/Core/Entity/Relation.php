<?php

/**
 * @file
 * Contains \Drupal\relation\Plugin\Core\Entity\Relation.
 */

namespace Drupal\relation\Plugin\Core\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Entity;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines relation entity
 *
 * @Plugin(
 *   id = "relation",
 *   label = @Translation("Relation"),
 *   module = "relation",
 *   controller_class = "Drupal\relation\RelationStorageController",
 *   render_controller_class = "Drupal\Core\Entity\EntityRenderController",
 *   form_controller_class = {
 *     "default" = "Drupal\relation\RelationFormController"
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
class Relation extends Entity implements ContentEntityInterface {

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
}