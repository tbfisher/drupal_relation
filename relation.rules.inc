<?php

/**
 * Callback for creating a relation, in the form Rules wants it.
 */
function relation_rules_create($values = array()) {
  return relation_create($values['relation_type'], array());
}

/**
 * Access callback for creating a relation. For now, everyone has permission to
 * trigger the creation of a relation.
 */
function relation_rules_access($op, $entity = NULL, $account = NULL) {
  return TRUE;
}

/**
 * Endpoint property getter callback.
 */
function relation_rules_get_endpoints($relation, array $options, $property_name, $entity_type) {
  return $relation->endpoints[LANGUAGE_NONE];
}

/**
 * Endpoint property setter callback.
 *
 * @param $data
 *   The relation object that we are going to modify.
 * @param $name
 *   Name of the provided Rules variable.
 * @param $endpoint_wrappers
 *   Array of entity wrappers that we are going to add to the relation object.
 */
function relation_rules_set_endpoints(&$relation = NULL, $name = NULL, $endpoint_wrappers = NULL) {
  // Check that we are creating a new relation. Updating existing relations
  // aren't supported.
  if (isset($relation->rid) || empty($endpoint_wrappers)) {
    return;
  }

  for ($i = 0; $i < count($endpoint_wrappers); $i++) {
    $entity = $endpoint_wrappers[$i]->value();
    $id_key = $endpoint_wrappers[$i]->entityKey('id');
    $bundle_key = $endpoint_wrappers[$i]->entityKey('bundle');
    if (isset($entity->{$id_key})) {
      $relation->endpoints[LANGUAGE_NONE][] = array(
        'entity_type' => $endpoint_wrappers[$i]->type(),
        'entity_id' => $entity->{$id_key},
        'entity_bundle' => $entity->{$bundle_key},
        'r_index' => $i,
      );
    }
  }
}
