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
 * )
 */
class Relation extends Entity implements ContentEntityInterface {

}