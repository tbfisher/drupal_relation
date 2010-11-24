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
function relation_field_is_empty() {
  return FALSE;
}

/**
 * Implements hook_field_insert().
 */
function relation_field_insert($entity_type, $entity, $field, $instance, $langcode, &$items) {
  list($entity_id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
  $insert1 = db_insert('relation')->fields(array('predicate'));
  $insert2 = db_insert('relation_data')->fields(array('relation_id', 'entity_type', 'entity_id'));
  foreach ($items as $item) {
    if (!empty($item['entity_id'])) {
      $relation_id = $insert1->values(array($field['field_name']))->execute();
      $insert2->values(array($relation_id, $item['entity_type'], $item['entity_id']));
      $insert2->values(array($relation_id, $entity_type, $entity_id));
    }
  }
  $insert2->execute();
  $items = array();
}

/**
 * Implements hook_field_update().
 */
function relation_field_update($entity_type, $entity, $field, $instance, $langcode, &$items) {
  relation_field_delete($entity_type, $entity, $field, $instance, $langcode, $items);
  relation_field_insert($entity_type, $entity, $field, $instance, $langcode, $items);
}

/**
 * Implements hook_field_delete().
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
 * Implements hook_field_load().
 */
function relation_field_load($entity_type, $entities, $field, $instances, $langcode, &$items, $age) {
  foreach ($entities as $entity) {
    list($id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
    $entity_ids[] = $id;
  }
  $query = db_select('relation', 'base')
    ->condition('l.entity_type', $entity_type)
    ->condition('l.entity_id', $entity_ids);
  _relation_entity_query_helper($query);
  foreach ($query->execute() as $item) {
    $items[$item->left_entity_id][] = array(
      'relation_id' => $item->relation_id,
      'predicate' => $item->predicate,
      'entity_id' => $item->right_entity_id,
      'entity_type' => $item->right_entity_type,
    );
  }
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
  $element['#type'] = 'fieldset';
  $element['entity_type'] = array(
    '#type' => 'select',
    '#title' => t('Entity type'),
    '#options' => drupal_map_assoc(array_keys(entity_get_info())),
    '#default_value' => isset($items[$delta]) ? $items[$delta]['entity_type'] : '',
  );
  $element['entity_id'] = array(
    '#title' => t('Entity ID'),
    '#type' => 'textfield',
    '#default_value' => isset($items[$delta]) ? $items[$delta]['entity_id'] : '',
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
  $list = array();
  foreach ($items as $item) {
    $uri = entity_uri($item['entity_type'], $item['entity']);
    $list[] = l($item['entity_type'], $uri['path'], $uri['options']);
  }
  return array(
    '#theme' => 'item_list',
    '#items' => $list,
  );
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
