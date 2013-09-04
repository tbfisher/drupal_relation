<?php

/**
 * @file
 * Contains \Drupal\relation_endpoint\Plugin\field\formatter\RelationEndpointFullFormatter.
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
 *   id = "relation_endpoint_full",
 *   label = @Translation("Full entities list"),
 *   field_types = {
 *     "relation_endpoint"
 *   },
 *   settings = {
 *   }
 * )
 */
class RelationEndpointFullFormatter extends FormatterBase {

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
    $endpoint_entity_type = '';
    $multiple = TRUE;
    foreach ($items as $delta => $endpoint) {
      if (!$endpoint_entity_type) {
        $endpoint_entity_type = $endpoint['entity_type'];
      }
      if ($endpoint_entity_type == $endpoint['entity_type']) {
        $entity_ids[] = $endpoint['entity_id'];
      }
      else {
        $multiple = FALSE;
        break;
      }
    }
    $view_mode = isset($display['settings']['view_modes'][$endpoint_entity_type]) ? $display['settings']['view_modes'][$endpoint_entity_type] : 'full';
    if ($multiple) {
      $entities = entity_load($endpoint_entity_type, $entity_ids);
      if (function_exists('entity_view')) {
        return array(entity_view($endpoint_entity_type, $entities, $view_mode));
      }
      $function = $endpoint_entity_type . '_view_multiple';
      if (function_exists($function)) {
        return array($function($entities, $view_mode));
      }
    }
    $build = array();
    foreach ($items as $delta => $endpoint) {
      if ($multiple) {
        $entity = $entities[$endpoint['entity_id']];
      }
      else {
        $entities = entity_load($endpoint['entity_type'], array($endpoint['entity_id']));
        $entity = reset($entities);
      }
      if (function_exists('entity_view')) {
        $build[$delta] = entity_view($endpoint['entity_type'], array($entity), $view_mode);
      }
      else {
        $function = $endpoint['entity_type'] . '_view';
        $build[$delta] = $function($entity, $view_mode);
      }
    }
    return $build;
  }
}
