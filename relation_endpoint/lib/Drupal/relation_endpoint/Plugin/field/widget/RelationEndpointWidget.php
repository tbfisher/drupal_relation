<?php

/**
 * @file
 * Definition of Drupal\relation_endpoint\Plugin\field\widget\RelationEndpointWidget.
 */

namespace Drupal\relation_endpoint\Plugin\field\widget;

use Drupal\field\Annotation\FieldWidget;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Field\FieldInterface;
use Drupal\field\Plugin\Type\Widget\WidgetBase;

/**
 * Plugin implementation of the 'relation_endpoint' widget.
 *
 * @FieldWidget(
 *   id = "relation_endpoint",
 *   label = @Translation("No widget"),
 *   field_types = {
 *     "relation_endpoint",
 *   }
 * )
 */
class RelationEndpointWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldInterface $items, $delta, array $element, $langcode, array &$form, array &$form_state) {
    return array();
  }
}
