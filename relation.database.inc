<?php

/**
 * @file
 * Database query functions.
 */

/**
 * Handler class for entity relations.
 *
 * Properites are public for the same as EntityFieldQuery. Please use the
 * utility methods instead of directly changing them.
 */
class RelationQuery {

  /**
   * Constructor for RelationQuery.
   */
  function __construct($entity_type, $entity_id) {
    $entity = reset(entity_load($entity_type, array($entity_id)));
    list($id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
    $this->entity_id = $id;
    $this->entity_bundle = $bundle;
    $this->entity_type = $entity_type;
  }

  /**
   * The list of predicates the relation should have.
   */
  public $predicates = array();

  /**
   * The index of the endpoint given in the constructor.
   */
  public $index = NULL;

  /**
   * Start of the query range.
   */
  public $start = 0;

  /**
   * Length of the query range.
   */
  public $length = 50;

  /**
   * Whether to return the number of related entities only.
   */
  public $count = FALSE;

  /**
   * Restrict the query to a given predicate.
   *
   * @return RelationQuery
   */
  public function predicate(array $predicate) {
    $this->predicates = array($predicate);
    return $this;
  }

  /**
   * Restrict the query to the given array of predicates.
   *
   * @return RelationQuery
   */
  public function predicates($predicates) {
    $this->predicates = $predicates;
    return $this;
  }

  /**
   * The index of the endpoint given in the constructor.
   *
   * @return RelationQuery
   */
  public function index($index) {
    $this->index = $index;
    return $this;
  }

  /**
   * Limit the query to a range.
   *
   * @return RelationQuery
   */
  public function range($start, $length) {
    $this->from = $start;
    $this->limit = $length;
    return $this;
  }

  /**
   * Return a count query result, instead of select.
   *
   * @return RelationQuery
   */
  public function count() {
    $this->count = TRUE;
    return $this;
  }

  /**
   * Execute the query.
   *
   * @return
   *   Either the number of entities if this a count query or the loaded
   *   entities as returned by entity_load().
   */
  public function execute() {
    $function = variable_get('relation_storage', 'relation_sql') . '_relation_query';
    return $function($this);
  }
}

/**
 * SQL query to get all relations tied to an entity.
 *
 * @param $entity_type
 *   The type of the entity (eg. node).
 * @param $entity_id
 *   The numerical entity id.
 * @param $predicates
 *   Array of relation types to look for.
 * @param $index
 *   The index of the entity in the relation, ie. for finding only relations
 *   for which the given entity is a target endpoint.
 *
 * @return
 *   Array of relation entity objects (which contain all the entities that they
 *   relate). Array is empty if no relations are found.
 */
function relation_sql_relation_query($relationQuery) {
  $query = db_select('relation_data', 'rd');
  $query->addField('r', 'relation_id');
  $query->innerJoin('relation', 'r', 'r.relation_id = rd.relation_id');
  $query->condition('rd.entity_id', $relationQuery->entity_id);
  if ($relationQuery->predicates) {
    $query->condition('r.predicate', $relationQuery->predicates);
  }
  if (isset($relationQuery->index)) {
    $query->condition('rd.index', $relationQuery->index);
  }
  if ($relationQuery->count) {
    $query = $query->countQuery();
    return $query->execute()->fetchField();
  }
  $relation_ids = $query->execute()->fetchCol();
  return $relation_ids ? entity_load('relation', $relation_ids) : array();
}
