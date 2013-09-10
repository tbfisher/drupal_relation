<?php

/**
 * @file
 * Contains \Drupal\relation_endpoint\Plugin\field\formatter\RelationEndpointFormatter.
 */

namespace Drupal\relation_endpoint\Plugin\field\formatter;

use Drupal\field\Annotation\FieldFormatter;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Field\FieldInterface;
use Drupal\Core\Entity\Field\FieldItemInterface;
use Drupal\field\Plugin\Type\Formatter\FormatterBase;

/**
 * Plugin implementation of the 'link' formatter.
 *
 * @FieldFormatter(
 *   id = "relation_endpoint",
 *   label = @Translation("Endpoints table"),
 *   field_types = {
 *     "relation_endpoint"
 *   },
 *   settings = {
 *   }
 * )
 */
class RelationEndpointFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {
    $elements = parent::settingsForm($form, $form_state);

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $view_modes_settings = $instance['display'][$view_mode]['settings']['view_modes'];
    foreach (_relation_endpoint_get_endpoint_entity_types($instance) as $endpoint_entity_type => $v) {
      $items[] = "$endpoint_entity_type: " . $view_modes_settings[$endpoint_entity_type];
    }
    return theme('item_list', array('items' => $items));
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(EntityInterface $entity, $langcode, FieldInterface $items) {
    $list_items = array();
//     TODO: fixme
//     foreach ($endpoints as $delta => $endpoint) {
//       $entity_info = entity_get_info($endpoint['entity_type']);
//       $entity = entity_load($endpoint['entity_type'], $endpoint['entity_id']);
//       $uri = $entity->uri();
//       $list_items[$delta] = array(l($entity->label(), $uri['path'], $uri['options']), $entity_info['label']);
//     }
    $headers = array(
      array('data' => t('Entity')),
      array('data' => t('Entity type')),
    );
    return array(
      '#theme' => 'table',
      '#header' => $headers,
      '#rows' => $list_items,
    );
  }
}
