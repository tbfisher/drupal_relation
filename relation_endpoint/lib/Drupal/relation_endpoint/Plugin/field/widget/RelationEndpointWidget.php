<?php

/**
 * @file
 * Definition of Drupal\relation_endpoint\Plugin\field\widget\RelationEndpointWidget.
 */

namespace Drupal\relation_endpoint\Plugin\field\widget;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;
use Drupal\field\Plugin\Type\Widget\WidgetBase;

/**
 * Plugin implementation of the 'relation_endpoint' widget.
 *
 * @Plugin(
 *   id = "relation_endpoint",
 *   module = "relation_endpoint",
 *   label = @Translation("Endpoints table"),
 *   field_types = {
 *     "relation_endpoint",
 *   },
 *   multiple_values = TRUE
 * )
 */
class RelationEndpointWidget extends WidgetBase {
  /**
   * Implements Drupal\field\Plugin\Type\Widget\WidgetInterface::formElement().
   */
  public function formElement(array $items, $delta, array $element, $langcode, array &$form, array &$form_state) {
    foreach ($items as $delta => $item) {
      foreach (array('entity_type', 'entity_id') as $column) {
        $element[$delta][$column] = array(
          '#type' => 'value',
          '#value' => $item[$column],
        );
      }
    }

    return array('link_list' => _relation_endpoint_field_create_html_table($items));
  }
}