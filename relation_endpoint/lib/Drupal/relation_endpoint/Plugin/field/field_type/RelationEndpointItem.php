<?php

/**
 * @file
 * Contains \Drupal\relation_endpoint\Plugin\field\field_type\RelationEndpointItem.
 */

namespace Drupal\relation_endpoint\Plugin\field\field_type;

use Drupal\Core\Entity\Annotation\FieldType;
use Drupal\Core\Annotation\Translation;
use Drupal\field\Plugin\Type\FieldType\ConfigFieldItemBase;
use Drupal\field\FieldInterface;

/**
 * Plugin implementation of the 'relation_endpoint' field type.
 *
 * @FieldType(
 *   id = "relation_endpoint",
 *   label = @Translation("Relation endpoint"),
 *   description = @Translation("This field contains the endpoints of the relation"),
 *   instance_settings = {
 *   },
 *   default_widget = "relation_endpoint",
 *   default_formatter = "relation_endpoint"
 * )
 */
class RelationEndpointItem extends ConfigFieldItemBase {

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

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldInterface $field) {
    return array(
      'columns' => array(
        'entity_type' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
          'description' => 'Entity_type of this relation end-point.',
        ),
        'entity_id' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Entity_id of this relation end-point.',
        ),
        'r_index' => array(
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'The index of this row in this relation. The highest index in the relation is stored as "arity" in the relation table.',
        ),
      ),
      'indexes' => array(
        'relation' => array('entity_type', 'entity_id', 'r_index'),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('entity_id')->getValue();
    return $value === NULL || $value === '';
  }
}
