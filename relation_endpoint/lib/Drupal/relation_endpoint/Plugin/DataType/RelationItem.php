<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\Plugin\DataType\RelationItem.
 */

namespace Drupal\Core\Entity\Plugin\DataType;

use Drupal\Core\TypedData\Annotation\DataType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'relation' entity field item.
 *
 * @DataType(
 *   id = "relation_field",
 *   label = @Translation("Relation field item"),
 *   description = @Translation("An entity field containing a RelationItem value.")
 * )
 */
class RelationItem extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['entity_type'] = array(
        'type' => 'string',
        'label' => t('Entity_type of this relation end-point.'),
      );
      static::$propertyDefinitions['entity_id'] = array(
        'type' => 'integer',
        'label' => t('Entity_id of this relation end-point.'),
      );
      static::$propertyDefinitions['r_index'] = array(
        'type' => 'integer',
        'label' => t('The index of this row in this relation.'),
      );
    }
    return static::$propertyDefinitions;
  }
}