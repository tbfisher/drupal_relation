<?php

/**
 * Implementation of hook_rules_action_info().
 */
function relation_rules_action_info() {
  return array(
    'relation_create_two_ary_relation' => array(
      'label' => t('Creata a 2-ary relation'),
      'group' => t('Relation'),
      'named parameter' => FALSE,
      'parameter' => array(
        'predicate' => array(
           'type' => 'text',
           'options list' => 'relation_get_bundles',
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
      'callbacks' => array(
        //'form_alter' => 'relation_rules_action_form_alter',
      ),
    ),
  );
}

function relation_get_bundles() {
  return array('event_galleries' => 'event galleries');
}

function relation_create_two_ary_relation($predicate, $endpoint1, $endpoint2, $state, $element) {
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
