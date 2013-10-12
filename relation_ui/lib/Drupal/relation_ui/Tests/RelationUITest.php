<?php

/**
 * @file
 * Definition of Drupal\relation_ui\Tests\RelationUITest.
 */

namespace Drupal\relation_ui\Tests;

use Drupal\relation\Tests\RelationTestBase;

/**
 * Tests Relation UI.
 *
 * Check that relation administration interface works.
 */
class RelationUITest extends RelationTestBase {

  public static $modules = array('relation', 'relation_ui', 'node');

  public static function getInfo() {
    return array(
      'name' => 'Relation UI test',
      'description' => 'Tests Relation UI.',
      'group' => 'Relation',
    );
  }

  function setUp() {
    // This is necessary for the ->sort('created', 'DESC') test.
    $this->sleep = TRUE;
    parent::setUp();
  }

  /**
   * Tests deletion of a relation.
   */
  function testRelationDelete() {
    $relations = relation_query('node', $this->node1->nid)
      ->sort('created', 'DESC')
      ->execute();
    $relation = $relations[$this->rid_directional];

    $this->drupalPost("relation/$relation->rid/delete", array(), t('Delete'));
    $arg = array(':rid' => $relation->rid);
    $this->assertFalse((bool) db_query_range('SELECT * FROM {relation} WHERE rid = :rid', 0, 1, $arg)->fetchField(), 'Nothing in relation table after delete.');
    $this->assertFalse((bool) db_query_range('SELECT * FROM {relation_revision} WHERE rid = :rid', 0, 1, $arg)->fetchField(), 'Nothing in relation revision table after delete.');
    $skeleton_relation = entity_create('relation', array($relation->rid, $relation->vid, $relation->relation_type));
    field_attach_load('relation', array($relation->rid => $skeleton_relation));
    $this->assertIdentical($skeleton_relation->endpoints, array(), t('Field data not present after delete'));

    // Try deleting the content types.
    $this->drupalGet("admin/structure/relation/manage/$this->relation_type_symmetric/delete");
    $num_relations = 1;
    // See relation_type_delete_confirm() in relation_ui.module
    $this->assertRaw(format_plural($num_relations, 'The %label relation type is used by 1 relation on your site. If you remove this relation type, you will not be able to edit  %label relations and they may not display correctly.', 'The %label relation type is used by @count relations on your site. If you remove %label, you will not be able to edit %label relations and they may not display correctly.', array('%label' => $this->relation_types['symmetric']['label'], '@count' => $num_relations)), 'Correct number of relations found (1) for ' . $this->relation_types['symmetric']['label'] . ' relation type.');
  }

  /**
   * Tests endpoint field settings.
   */
  function testRelationEndpointsField() {
    $field_label = $this->randomName();
    $edit = array(
      'instance[label]' => $field_label,
    );
    $this->drupalPost('admin/structure/relation/manage/symmetric/fields/endpoints', $edit, t('Save settings'));
    $this->assertText(t('Saved @label configuration.', array('@label' => $field_label)));

    $this->drupalGet('admin/structure/relation/manage/symmetric/fields');
    $this->assertFieldByXPath('//table[@id="field-overview"]//td[1]', $field_label, t('Endpoints field label appears to be changed in the overview table.'));
  }
}
