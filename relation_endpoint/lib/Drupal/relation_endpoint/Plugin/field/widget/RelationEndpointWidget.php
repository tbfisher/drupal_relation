<?php

/**
 * @file
 * Definition of Drupal\relation_endpoint\Plugin\field\widget\RelationEndpointWidget.
 *
 * TODO: Figure out if there is easier way to say "no we don't have edit widget"
 */

namespace Drupal\relation_endpoint\Plugin\field\widget;

use Drupal\field\Annotation\FieldWidget;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Field\FieldItemListInterface;
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
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    return array();
  }
}
