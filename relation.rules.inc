<?php

/**
 * Implementation of hook_rules_action_info().
 */
function relation_rules_action_info() {
  return array(
    // Optimally, we would like to have one generic action that can create
    // relation entities with N number of endpoints, depending on available
    // entities in the exectuing rule. Unfortunately, there isn't an easy way
    // of doing that with the Rules API.
    'relation_rules_create_two_ary_relation' => array(
      'label' => t('Create a 2-ary relation'),
      'group' => t('Relation'),
      'named parameter' => FALSE,
      'parameter' => array(
        'predicate' => array(
           'type' => 'text',
           'options list' => 'relation_rules_get_type_options',
           'restriction' => 'input',
        ),
        'endpoint1' => array(
          'type' => 'entity',
          'label' => t('Endpoint entity 1'),
          'restriction' => 'selector',
        ),
        'endpoint2' => array(
          'type' => 'entity',
          'label' => t('Endpoint entity 2'),
          'restriction' => 'selector',
        ),
      ),

      'provides' => array(
        'relation_created' => array(
          'type' => 'entity',
          'label' => t('The created relation'),
          'save' => TRUE,
        ),
      ),
    ),
    // Action for loading relations from an available entity.
    'relation_rules_load' => array(
      'label' => t('Load relations'),
      'group' => t('Relation'),
      'named parameter' => FALSE,
      'parameter' => array(
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
    ),
  );
}

/**
 * Options list callback for fetching relation types.
 */
function relation_rules_get_type_options() {
  $options = array();
  $types = relation_get_types();
  foreach ($types as $type) {
    $options[$type->predicate] = $type->label;
  }
  return $options;
}

/**
 * Rules action callback for creating a 2-ary relation.
 */
function relation_rules_create_two_ary_relation($predicate, $endpoint1, $endpoint2, $state, $element) {
  $endpoints = array(
    array(
      'entity_type' => $endpoint1->type(),
      'entity_id' => $endpoint1->getIdentifier()
    ),
    array(
      'entity_type' => $endpoint2->type(),
      'entity_id' => $endpoint2->getIdentifier()
    ),
  );
  $rid = relation_create($predicate, $endpoints);
  $relation = relation_load($rid);
  return array('relation_created' => $relation);
}

/**
 * Rules action callback for loading relations.
 */
function relation_rules_load($entity, $state, $element) {
  $relation_rids = relation_query($entity->type(), $entity->getIdentifier())->execute();
  $relations = relation_multiple_load(array_keys($relation_rids));
  return array('relation_loaded' => $relations);
}
