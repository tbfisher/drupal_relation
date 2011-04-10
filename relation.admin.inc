<?php

/**
 * List all relation predicates.
 */
function relation_list() {
  $relation = relation_entity_info();
  $headers = array(t('Name'));
  $rows = array();
  foreach ($relation['relation']['bundles'] as $name => $bundle) {
    $url = url('admin/structure/relation/edit/' . $name);
    $link = t('<a href="!url">@label</a>', array('!url' => $url, '@label' => $bundle['label']));
    $rows[] = array($link);
  }
  $output = array(
    '#theme' => 'table',
    '#header' => $headers,
    '#rows' => $rows,
  );
  return $output;
}

/**
 * Relation bundle settings page.
 *
 * @param $predicate
 *   Relation type machine name. If this is not provided, assume that we're
 *   creating a new relation type.
 */
function relation_config_page($form, $form_state, $predicate = '') {
  $form['#write_record_keys'] = array();
  if ($predicate) {
    $relation_type = relation_type_load($predicate);
    if ($relation_type) {
      $form['#write_record_keys'][] = 'predicate';
    }
  }
  if (empty($relation_type)) {
    $relation_type = (object) array(
      'predicate' => $predicate,
      'label' => '',
      'bundles' => array(),
      'directional' => FALSE,
      'transitive' => FALSE,
      'min_arity' => 2,
      'max_arity' => 2,
      'source_bundles' => array(),
      'target_bundles' => array(),
    );
  }
  $predicate = $relation_type->predicate;
  $form['predicate'] = array(
    '#type'          => 'machine_name',
    '#title'         => t('Machine Name'),
    '#description'   => t('Machine name (predicate) of the relation'),
    '#default_value' => $predicate,
    '#required'      => TRUE,
    '#disabled'      => $predicate,
    '#machine_name' => array(
      'exists' => 'relation_type_load',
    ),
  );
  $form['label'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Label'),
    '#description'   => t('Display name of the relation'),
    '#default_value' => $relation_type->label,
    '#required'      => TRUE,
  );
  $form['directional'] = array(
    '#type'           => 'checkbox',
    '#title'          => 'Directional',
    '#default_value'  => $relation_type->directional,
  );
  $form['transitive'] = array(
    '#type'           => 'checkbox',
    '#title'          => 'Transitive',
    '#default_value'  => $relation_type->transitive,
  );
  // these should probably be changed to numerical (validated) textfields.
  $options = array('2' => '2', '3' => '3', '4' => '4', '5' => '5', '8' => '8');
  $form['min_arity'] = array(
    '#type' => 'select',
    '#title' => t('Minimum Arity'),
    '#options' => $options,
    '#description' => t('Minimum number of entities joined by the relation (e.g. three siblings in one relation). <em>In nearly all cases you will want to leave this set to 2</em>.'),
    '#default_value' => $relation_type->min_arity ? $relation_type->min_arity : 2,
    '#states' => array(
      'disabled' => array(   // action to take.
        ':input[name="directional"]' => array('checked' => TRUE),
      ),
    ),
  );

  $options = array('2' => '2', '3' => '3', '4' => '4', '5' => '5', '8' => '8', '0' => t('Infinite'));
  $form['max_arity'] = array(
    '#type' => 'select',
    '#title' => t('Maximum Arity'),
    '#options' => $options,
    '#description' => t('Maximum number of entities joined by the relation. <em>In nearly all cases you will want to leave this set to 2</em>.'),
    '#default_value' => $relation_type->max_arity ? $relation_type->max_arity : 2,
    '#states' => array(
      'disabled' => array(   // action to take.
        ':input[name="directional"]' => array('checked' => TRUE),
      ),
    ),
  );
  $counter = 0;
  foreach (module_invoke_all('entity_info') as $entity_type => $entity) {
    if (isset($entity['bundles'])) {
      foreach ($entity['bundles'] as $bundle_id => $bundle) {
        $bundles[$entity['label']]["$entity_type:$bundle_id"] = $bundle['label'];
        $counter++;
      }
    }
    else {
      $bundles[$entity['label']]["$entity_type:$entity_type"] = $entity['label'];
        $counter++;
    }
  }
  $form['source_bundles'] = array(
    '#type'          => 'select',
    '#title'         => 'Available source bundles',
    '#options'       => $bundles,
    '#size'          => max(12, $counter),
    '#default_value' => $relation_type->source_bundles,
    '#multiple'      => TRUE,
    '#description'   => 'Bundles that are not selected will not be available as sources for directional, or end points of non-directional relations relations. Ctrl+click to select multiple.',
  );
  $form['target_bundles'] = array(
    '#type'          => 'select',
    '#title'         => 'Available target bundles',
    '#options'       => $bundles,
    '#size'          => max(12, $counter),
    '#default_value' => $relation_type->target_bundles,
    '#multiple'      => TRUE,
    '#description'   => 'Bundles that are not selected will not be available as targets for directional relations. Ctrl+click to select multiple.',
    '#states' => array(
      '!visible' => array(   // action to take.
        ':input[name="directional"]' => array('checked' => FALSE),
      ),
    ),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save')
  );
  return $form;
}

/**
 * Submit data from bundle settings page.
 */
function relation_config_page_submit($form, &$form_state) {
  $predicate = $form_state['values']['predicate'];
  $min_arity = $form_state['values']['directional'] ? 2 : $form_state['values']['min_arity'];
  $max_arity = $form_state['values']['directional'] ? 2 : $form_state['values']['max_arity'];
  $record = array(
    'predicate'   => $predicate,
    'min_arity'   => $min_arity,
    'max_arity'   => $max_arity,
    'label' => $form_state['values']['label'],
    'directional' => $form_state['values']['directional'],
    'transitive' => $form_state['values']['transitive'],
    'source_bundles' => $form_state['values']['source_bundles'],
    'target_bundles' => $form_state['values']['target_bundles'],
  );
  relation_type_save($record, $form['#write_record_keys']);
  $form_state['redirect'] = "admin/structure/relation/edit/$predicate";
}
