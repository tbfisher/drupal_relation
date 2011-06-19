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
    'relation_rules_create_binary_relation' => array(
      'label' => t('Create a binary relation'),
      'group' => t('Relation'),
      'named parameter' => FALSE,
      'parameter' => array(
        'predicate' => array(
           'type' => 'text',
           'label' => t('Predicate'),
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
    'relation_rules_load' => array(
      'label' => t('Loads a relation from a relation id'),
      'group' => t('Relation'),
      'named parameter' => FALSE,
      'parameter' => array(
        'rid' => array(
          'type' => 'integer',
          'label' => t('Relation id'),
        ),
      ),
      'provides' => array(
        'relation_loaded' => array(
          'type' => 'relation',
          'label' => t('Loaded relation'),
          'save' => TRUE,
        ),
      ),
    ),
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

function relation_rules_create($values = array()) {
  // Check for unqiue flag, if relation endpoints already exist, return FALSE
  $relation_type = relation_type_load($values['relation_type']);
  if ($relation_type->r_unique) {
    if (relation_relation_exists(array(), $values['relation_type'])) {
      return FALSE;
    }
  }

  global $user;
  $relation = new stdClass();
  $relation->is_new = TRUE;
  $relation->relation_type = $values['relation_type'];
  $relation->uid = $user->uid;
  $relation->endpoints[LANGUAGE_NONE] = NULL;
  return $relation;
}

/**
 * Rules action callback for creating a binary relation.
 */
function relation_rules_create_binary_relation($predicate, $endpoint1, $endpoint2, $state, $element) {
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
  return relation_load($entity);
 /* $relation_rids = relation_query($entity->type(), $entity->getIdentifier())
    ->entityCondition('bundle', $predicate)
    ->execute();
  $relations = relation_load_multiple(array_keys($relation_rids));
  return array('relation_loaded' => $relations);*/
}
