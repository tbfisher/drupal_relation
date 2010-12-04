<?php
// $Id$

/**
 * @file
 * A field storing arbitrary relations between entities.
 */

/**
 * Implements hook_field_info().
 */
function relation_field_info() {
  return array(
    'relation' => array(
      'label' => t('Relation'),
      'description' => t('Stores relationships between entities.'),
      'settings' => array(
        'allowed_values' => '',
        'allowed_values_function' => '',
      ),
      'default_widget' => 'relation_default',
      'default_formatter' => 'relation_default',
    ),
  );
}

/**
 * Implements hook_field_is_empty().
 */
function relation_field_is_empty($item, $field) {
  return empty($item['relation_id']);
}

/**
 * Implements hook_field_presave().
 */
function relation_field_presave($entity_type, $entity, $field, $instance, $langcode, &$items) {
  $field_name = $field['field_name'];
  list($id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);

  if (isset($entity->relation_recursion)) {
    return;
  }

  $relations = array();
  foreach ($items as $item) {
    if (!isset($item['relation_id'])) {
      $item['relation_id'] = db_next_id();
    }
    $relations[$item['entity_type']][$item['relation_id']] = $item['entity_id'];
  }

  foreach ($relations as $target_type => $source_relations) {
    $targets = entity_load($target_type, $source_relations);
    // @todo The else condition must not happen; implement hook_field_validate().
    if (!$targets) {
      continue;
    }
    foreach ($targets as $target_id => $target) {
      // A new field instance on existing entities contains no structure yet.
      if (!isset($target->{$field_name}[$langcode])) {
        $target->{$field_name}[$langcode] = array();
      }
      $target_items = &$target->{$field_name}[$langcode];

      // Transform current relations on the target into a lookup map keyed by
      // relation_id.
      $target_relations = array();
      foreach ($target_items as $delta => $item) {
        $target_relations[$item['relation_id']] = array(
          'delta' => $delta,
          'item' => $item,
        );
      }
      // Add a new relation, if it does not exist yet.
      foreach ($source_relations as $relation_id => $target_entity_id) {
        if (!isset($target_relations[$relation_id])) {
          $target_items[] = array(
            'relation_id' => $relation_id,
            'entity_type' => $entity_type,
            'entity_id' => $id,
            // @todo Add optional revision_id handling.
            // 'revision_id' => $vid,
            'revision_id' => NULL,
          );
        }
      }
      // Prevent infinite recursion; ENTITY_save() calls hook_field_presave().
      $target->relation_recursion = TRUE;
      // @todo Entity module's entity_save() does not handle core entities yet.
      // @see http://drupal.org/node/988780
      $result = entity_save($target_type, $target);
      if ($result === FALSE && function_exists($target_type . '_save')) {
        $function = $target_type . '_save';
        $function($target);
      }
      unset($target->relation_recursion);
    }
  }
}

/**
 * Implements hook_field_delete().
 *
 * This hook is invoked when an entity is deleted. All entities that are
 * referencing the deleted entity need to be updated to remove the relation(s).
 */
function relation_field_delete($entity_type, $entity, $field, $instance, $langcode, &$items) {
  list($entity_id) = entity_extract_ids($entity_type, $entity);
  $result = db_query('SELECT relation_id FROM {relation_data} WHERE entity_type = :entity_type AND entity_id = :entity_id', array(
    ':entity_type' => $entity_type,
    ':entity_id' => $entity_id,
  ));
  foreach ($result as $row) {
    db_delete('relation')->condition('relation_id', $row->relation_id)->execute();
    db_delete('relation_data')->condition('relation_id', $row->relation_id)->execute();
  }
}

/**
 * Implements hook_field_instance_delete().
 *
 * This hook is invoked after a field instance has been marked for deletion.
 * This means that an entire relation endpoint has been removed, so all
 * relations to entities of the field instance need to be deleted.
 */
function relation_field_instance_delete($instance) {
  // @todo
}

/**
 * Implements hook_field_widget_info().
 */
function relation_field_widget_info() {
  return array(
    'relation_default' => array(
      'label' => t('Relation selector'),
      'field types' => array('relation'),
    ),
  );
}

/**
 * Implements hook_field_widget_form().
 */
function relation_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {
  if (!isset($items[$delta])) {
    $items[$delta] = array(
      'entity_type' => '',
      'entity_id' => '',
      'relation_id' => NULL,
    );
  }

  $element['#type'] = 'fieldset';
  $element['entity_type'] = array(
    '#type' => 'select',
    '#title' => t('Entity type'),
    '#options' => drupal_map_assoc(array_keys($field['bundles'])),
    '#default_value' => $items[$delta]['entity_type'],
  );
  $element['entity_id'] = array(
    '#title' => t('Entity ID'),
    '#type' => 'textfield',
    '#default_value' => $items[$delta]['entity_id'],
  );
  $element['relation_id'] = array(
    '#type' => 'value',
    '#value' => $items[$delta]['relation_id'],
  );
  return $element;
}

/**
 * Implements hook_field_formatter_info().
 */
function relation_field_formatter_info() {
  return array(
    'relation_default' => array(
      'label' => t('Default'),
      'field types' => array('relation'),
    ),
  );
}

/**
 * Implements hook_field_formatter_view().
 */
function relation_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $links = array();
  foreach ($items as $item) {
    $class = $item['entity_type'] . '-' . $item['entity_id'];
    $uri = entity_uri($item['entity_type'], $item['entity']);
    $links[$class] = array(
      'title' => entity_label($item['entity_type'], $item['entity']),
      'href' => $uri['path'],
    ) + $uri;
  }
  if ($links) {
    $element[0] = array(
      '#theme' => 'links',
      '#links' => $links,
    );
    return $element;
  }
}

/**
 * Implements hook_field_formatter_prepare_view().
 */
function relation_field_formatter_prepare_view($entity_type, $entities, $field, $instances, $langcode, &$items, $displays) {
  $entities_to_load = array();
  foreach ($items as $key => $item) {
    foreach ($item as $delta => $value) {
      $entities_to_load[$value['entity_type']][] = $value['entity_id'];
      $lookup[$value['entity_type']][$value['entity_id']][] = array($key, $delta);
    }
  }
  foreach ($entities_to_load as $entity_type => $ids) {
    $entities = entity_load($entity_type, $ids);
    foreach ($entities as $entity_id => $entity) {
      foreach ($lookup[$entity_type][$entity_id] as $data) {
        $items[$data[0]][$data[1]]['entity'] = $entity;
      }
    }
  }
}
