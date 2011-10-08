<?php

/**
 * Exports relation types, display them in a form. Used by ctools export.
 */
function relation_export_relation_type($form, &$form_state, $type) {
  $relation_type = relation_relation_type_load($type->relation_type);
  drupal_set_title(check_plain($type->label));
  $code = relation_relation_type_export($relation_type);
  $lines = substr_count($code, "\n");

  $form['export'] = array(
    '#title' => t('Export data'),
    '#type' => 'textarea',
    '#value' => $code,
    '#rows' => $lines,
    '#description' => t('Copy the export text and paste it into another myobj using the import function.'),
  );
  return $form;
}

/**
 * Loads a single relation type for export.
 */
function relation_relation_type_load($type) {
  ctools_include('export');
  $result = ctools_export_load_object('relation_type', 'names', array($type));
  if (isset($result[$type])) {
    return $result[$type];
  }
}

/**
 * Exports a relation type.
 */
function relation_relation_type_export($relation_type, $indent = '') {
  ctools_include('export');
  $additional2 = array(
    'source_bundles' => $relation_type->source_bundles,
    'target_bundles' => $relation_type->target_bundles,
  );
  $output = ctools_export_object('relation_type', $relation_type, $indent, NULL, array(), $additional2);
  return $output;
}
