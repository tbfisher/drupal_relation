<?php

function relation_query($entity_type, $entity) {
  return new RelationQuery($entity_type, $entity);
}

/**
 * Handler class for entity relations.
 */
class RelationQuery {

  function __construct($entity_type, $entity) {
    list($id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
    $this->entity_id = $id;
    $this->entity_bundle = $bundle;
    $this->entity_type = $entity_type;
  }

  // Array of predicates to look for relations with.
  public $predicates = array();

  // index of the entity in the relation
  public $index = NULL;

  // default limits
  public $start = 0;

  public $length = 50;

  // whether to return a count query
  public $count = FALSE;

  /**
   * Defines the first row of the query to be returned.
   */
  public function predicates($predicates) {
    $this->predicates = $predicates;
    return $this;
  }

  /**
   * Defines the first row of the query to be returned.
   */
  public function index($index) {
    $this->index = $index;
    return $this;
  }

  /**
   * Limit the query to a range.
   */
  public function range($start, $length) {
    $this->from = $start;
    $this->limit = $length;
    return $this;
  }

  /**
   * Return a count query result, instead of select.
   */
  public function count() {
    $this->count = TRUE;
    return $this;
  }

  /**
   * Execute the query.
   */
  public function execute() {
    return relation_sql_relation_query($this);
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
