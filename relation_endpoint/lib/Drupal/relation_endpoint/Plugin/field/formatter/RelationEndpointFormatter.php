<?php

/**
 * @file
 * Contains \Drupal\relation_endpoint\Plugin\field\formatter\RelationEndpointFormatter.
 */

namespace Drupal\relation_endpoint\Plugin\field\formatter;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Field\FieldInterface;
use Drupal\Core\Entity\Field\FieldItemListInterface;
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
  public function viewElements(FieldItemListInterface $items) {
    $rows = array();

    $header = array(
      array('data' => t('Entity type')),
      array('data' => t('Entity ID')),
      array('data' => t('Label')),
    );

    foreach ($items as $item) {
      $t = array('@entity_type' => $item->entity_type, '@entity_id' => $item->entity_id);
      $entity_info = \Drupal::entityManager()->getDefinition($item->entity_type);
      if ($entity_info && $entity = entity_load($item->entity_type, $item->entity_id)) {
        $label = $entity->label();
        $uri = $entity->uri();

        $label = (!empty($label) && strlen($label) > 0) ? $label : t('Untitled', $t);
        $label = $uri['path'] ? l($label, $uri['path']) : $label;
      } else {
        $label = t('Deleted');
      }

      $rows[] = array($item->entity_type, $item->entity_id, $label);
    }

    return array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    );
  }
}
