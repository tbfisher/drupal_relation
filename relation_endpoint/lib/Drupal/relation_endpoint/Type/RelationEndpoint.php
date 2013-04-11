<?php

/**
 * @file
 * Contains \Drupal\relation_endpoint\Type\RelationEndpoint.
 */

namespace Drupal\relation_endpoint\Type;

use Drupal\Core\Entity\Field\FieldItemBase;

/**
 * Defines the 'relation_endpoint' entity field item.
 */
class RelationEndpoint extends FieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * Implements ComplexDataInterface::getPropertyDefinitions().
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