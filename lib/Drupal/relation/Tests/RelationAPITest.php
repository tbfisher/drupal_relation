<?php

/**
 * @file
 * Definition of Drupal\relation\Tests\RelationAPITest.
 */

namespace Drupal\relation\Tests;

/**
 * Tests Relation API.
 *
 * Create nodes, add relations and verify that they are related.
 * This test suite also checks all methods available in RelationQuery.
 */
class RelationAPITest extends RelationTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Relation test',
      'description' => 'Tests Relation API.',
      'group' => 'Relation',
    );
  }

  function setUp() {
    // This is necessary for the ->propertyOrderBy('created', 'DESC') test.
    $this->sleep = TRUE;
    parent::setUp();
  }

  /**
   * Tests all available methods in RelationQuery.
   * Creates some nodes, add some relations and checks if they are related.
   */
  function testRelationQuery() {
    $relations = entity_load('relation', array_keys(relation_query('node', $this->node1->nid)->execute()));

    // Check that symmetric relation is correctly related to node 4.
    $this->assertEqual($relations[$this->rid_symmetric]->endpoints[LANGUAGE_NONE][1]['entity_id'], $this->node4->nid, 'Correct entity is related: ' . $relations[$this->rid_symmetric]->endpoints[LANGUAGE_NONE][1]['entity_id'] . '==' . $this->node4->nid);

    // Symmetric relation is Article 1 <--> Page 4
    $entity_keys = array(
      array('entity_type' => 'node', 'entity_id' => $this->node4->nid),
      array('entity_type' => 'node', 'entity_id' => $this->node4->nid),
    );
    $this->assertFalse(relation_relation_exists($entity_keys, 'symmetric'), 'node4 is not related to node4.');

    // Get relations for node 1, should return 3 relations.
    $count = count($relations);
    $this->assertEqual($count, 3);

    // Get number of relations for node 4, should return 6 relations.
    $count = relation_query('node', $this->node4->nid)
      ->count()
      ->execute();
    $this->assertEqual($count, 6);

    // Get number of relations for node 5, should return 2 relations.
    $count = relation_query('node', $this->node5->nid)
      ->count()
      ->execute();
    $this->assertEqual($count, 2);

    // Get relations between entities 2 and 5 (none).
    $count = relation_query('node', $this->node2->nid)
      ->related('node', $this->node5->nid)
      ->count()
      ->execute();
    $this->assertFalse($count);

    // Get directed relations for node 3 using index, should return 2 relations.
    // The other node 3 relation has an r_index 0.
    $relations = relation_query('node', $this->node3->nid, 1)
      ->execute();
    $this->assertEqual(count($relations), 3);
    $this->assertTrue(isset($relations[$this->rid_directional]), 'Got the correct directional relation for nid=3.');

    // Get relations between entities 2 and 3 (octopus).
    $relations = relation_query('node', $this->node2->nid)
      ->related('node', $this->node3->nid)
      ->execute();
    $count = count($relations);
    $this->assertEqual($count, 1);
    // Check that we have the correct relations
    $this->assertEqual(isset($relations[$this->rid_octopus]), 'Got one correct relation.');

    // Get relations for node 1 (symmetric, directional, octopus), limit to
    // directional and octopus with relation_type().
    $relations = relation_query('node', $this->node1->nid)
      ->propertyCondition('relation_type', array(
        $this->relation_types['directional']['relation_type'],
        $this->relation_types['octopus']['relation_type'],
      ))
      ->execute();
    $count = count($relations);
    $this->assertEqual($count, 2);
    // Check that we have the correct relations.
    $this->assertTrue(isset($relations[$this->rid_directional]), 'Got one correct relation.');
    $this->assertTrue(isset($relations[$this->rid_octopus]), 'Got a second one.');

    // Get last two relations for node 1.
    $relations = relation_query('node', $this->node1->nid)
      ->range(1, 2)
      ->propertyOrderBy('rid', 'ASC')
      ->execute();
    $count = count($relations);
    $this->assertEqual($count, 2);
    // Check that we have the correct relations.
    $this->assertTrue(isset($relations[$this->rid_directional]), 'Got one correct relation.');
    $this->assertTrue(isset($relations[$this->rid_octopus]), 'Got a second one.');

    // Get all relations on node 1 and sort them in reverse created order.
    $relations = relation_query('node', $this->node1->nid)
      ->propertyOrderBy('created', 'DESC')
      ->execute();
    $this->assertEqual(array_keys($relations), array($this->rid_octopus, $this->rid_directional, $this->rid_symmetric));
    
    // Create 10 more symmetric relations and verify that the count works with
    // double digit counts as well.
    for($i = 0; $i < 10; $i++) {
      $this->createRelationSymmetric();
    }
    $count = relation_query('node', $this->node4->nid)
      ->count()
      ->execute();
    $this->assertEqual($count, 16);        
  }

  /**
   * Tests relation types.
   */
  function testRelationTypes() {
    // Symmetric.
    $related = relation_get_related_entity('node', $this->node1->nid);
    $this->assertEqual($this->node4->nid, $related->nid);

    // Confirm this works once the related entity has been cached.
    $related = relation_get_related_entity('node', $this->node1->nid);
    $this->assertEqual($this->node4->nid, $related->nid);

    // Directional.
    // From Parent to Grandparent.
    $related = relation_get_related_entity('node', $this->node3->nid, $this->relation_types['directional']['relation_type'], 1);
    $this->assertEqual($this->node1->nid, $related->nid);
    // From Parent to Child.
    $related = relation_get_related_entity('node', $this->node3->nid, $this->relation_types['directional']['relation_type'], 0);
    $this->assertEqual($this->node4->nid, $related->nid);

    // Delete all relations related to node 4, then confirm that these can
    // no longer be found as related entities.
    $relations = relation_query('node', $this->node4->nid)->execute();
    foreach ($relations as $relation) {
      relation_delete($relation->rid);
    }
    $this->assertFalse(relation_get_related_entity('node', $this->node4->nid), 'The entity was not loaded after the relation was deleted.');
  }

  /**
   * Tests saving of relations.
   */
  function testRelationSave() {
    foreach ($this->relation_types as $value) {
      $relation_type = $value['relation_type'];
      $endpoints = $this->endpoints;
      if (isset($value['min_arity'])) {
        $endpoints = $value['min_arity'] == 1 ? $this->endpoints_unary : $this->endpoints_4;
      }
      if ($relation_type == 'directional_entitydifferent') {
        $endpoints = $this->endpoints_entitydifferent;
      }
      $relation = relation_create($relation_type, $endpoints);
      $rid = relation_save($relation);
      $this->assertTrue($rid, 'Relation created.');
      $count = count($relation->endpoints[LANGUAGE_NONE]);
      $this->assertEqual($count, count($endpoints));
      $this->assertEqual($relation->arity, count($endpoints));
      $this->assertEqual($relation->relation_type, $relation_type);
      foreach ($relation->endpoints[LANGUAGE_NONE] as $endpoint) {
        $need_ids[$endpoint['entity_id']] = TRUE;
      }
      foreach ($relation->endpoints[LANGUAGE_NONE] as $delta => $endpoint) {
        $this->assertEqual($endpoint['entity_type'], $endpoints[$delta]['entity_type'], 'The entity type is ' . $endpoints[$delta]['entity_type'] . ': ' . $endpoint['entity_type']);
        $this->assertTrue(isset($need_ids[$endpoint['entity_id']]), 'The entity ID is correct: ' . $need_ids[$endpoint['entity_id']]);
        unset($need_ids[$endpoint['entity_id']]);
      }
      $this->assertFalse($need_ids, 'All ids found.');
      // Confirm the rid in revision table.
      $revision = db_select('relation_revision', 'v')
          ->fields('v', array('rid'))
          ->condition('vid', $relation->vid)
          ->execute()
          ->fetchAllAssoc('rid');
      $this->assertTrue(array_key_exists($rid, $revision), 'Relation revision created.');
    }
  }

  /**
   * Tests relation delete.
   */
  function testRelationDelete() {
    // Invalid relations are deleted when any endpoint entity is deleted. 
    // Octopus relation is valid with 3 endpoints, currently it has 4.
    node_delete($this->node1->nid);
    $this->assertTrue(relation_load($this->rid_octopus, NULL, TRUE), 'Relation is not deleted.');
    node_delete($this->node2->nid);
    $this->assertFalse(relation_load($this->rid_octopus, NULL, TRUE), 'Relation is deleted.');
  }

  /**
   * Tests relation revisions.
   */
  function testRelationRevision() {
    $first_user = $this->drupalCreateUser(array('edit relations'));
    $second_user = $this->drupalCreateUser(array('edit relations'));

    $this->drupalLogin($first_user);
    $relation = relation_create($this->relation_type_octopus, $this->endpoints_4, $first_user);
    $rid = relation_save($relation, $first_user);
    $this->assertEqual($relation->uid, $first_user->uid);
    $vid = $relation->vid;

    // Relation should still be owned by the first user
    $this->drupalLogin($second_user);
    $relation = relation_load($rid);
    relation_save($relation, $second_user);
    $this->assertEqual($relation->uid, $first_user->uid);

    // Relation revision authors should not be identical though.
    $first_revision = relation_load($rid, $vid);
    $second_revision = relation_load($rid, $relation->vid);
    $this->assertNotIdentical($first_revision->revision_uid, $second_revision->revision_uid, 'Each revision has a distinct user.');
  }
}
