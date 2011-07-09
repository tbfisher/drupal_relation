<?php

/**
 * Implementation of hook_rules_action_info().
 */
function relation_rules_action_info() {
  return array(
    // Action for loading relations from an available entity.
/*    'relation_rules_load' => array(
      'label' => t('Load relations'),
      'group' => t('Relation'),
      'named parameter' => FALSE,
      'parameter' => array(
        // @todo: We would probably like to have a multiple select here and an
        // option for 'all'. Implementing hook_ACTION_NAME_form_alter() is
        // probably the best way.
        'predicate' => array(
           'type' => 'text',
           'label' => t('Predicate'),
           'options list' => 'relation_rules_get_type_options',
           'restriction' => 'input',
        ),
        'entity' => array(
          'type' => 'entity',
          'label' => t('Entity'),
          'restriction' => 'selector',
        ),
      ),
      'provides' => array(
        'relation_loaded' => array(
          'type' => 'list',
          'label' => t('Loaded relations'),
          'save' => TRUE,
        ),
      ),
    ),*/
  );
}

/**
 * Endpoint property getter function
 */
function relation_rules_get_endpoints($relation, array $options, $property_name, $entity_type) {
  return $relation->endpoints[LANGUAGE_NONE];
}

/**
 * $data is the relation, so we take the endpoints from the argument we get, and copy into the relation.
 */
function relation_rules_set_endpoints(&$data = NULL, $name = NULL, $endpoint_wrapper_list = NULL) {
  // Check that we are creating a new relation
  if (isset($data->rid)) {
    drupal_set_message('Manipulating existing relations is not supported.');
    // @todo We need to prevent a call to relation_save now
    return;
  }

  for ($i = 0; $i < count($endpoint_wrapper_list); $i++) {
    $entity = $endpoint_wrapper_list[$i]->value();
    $key = $endpoint_wrapper_list[$i]->entityKey('id');
    $data->endpoints[LANGUAGE_NONE][] = array(
      'entity_type' => $endpoint_wrapper_list[$i]->type(),
      'entity_id' => $entity->$key,
      'entity_bundle' => _entity_get_bundle($entity, $endpoint_wrapper_list[$i]->type()),
      'r_index' => $i,
    );
  }
}

/**
 * Returns the bundle name to which the arguments belong
 */
function _entity_get_bundle($entity, $entity_type) {
  $info = entity_get_info($entity_type);
  if (empty($info['entity keys']['bundle'])) {
    return $entity_type;
  } else {
    return $entity->{$info['entity keys']['bundle']};
  }
}

/**
 * Callback for creation of new relation entities.
 * @todo This isn't really rules speicfic, and should be merged into the main module
 */
function relation_rules_create($values = array()) {
  return relation_create($values['relation_type'], array());
}
