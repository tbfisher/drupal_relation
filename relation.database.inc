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
   * The list of entity keys.
   */
  public $entityKeys = array();

  /**
   * The list of predicates the relation should have.
   */
  public $predicates = array();

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
   * List of order clauses.
   */
  public $order = array();

  /**
   * Constructor for RelationQuery.
   */
  function __construct($entity_type, $entity_id, $index = NULL) {
    if (isset($entity_type)) {
      $this->related($entity_type, $entity_id);
    }
  }

  /**
   * Add a related entity to the query.
   */
  function related($entity_type, $entity_id, $index = NULL) {
    $this->entityKeys[] = array(
      'entity_type' => $entity_type,
      'entity_id' => $entity_id,
      'index' => $index,
    );
    return $this;
  }

  /**
   * Restrict the query to a given predicate.
   *
   * @return RelationQuery
   */
  public function predicate($predicate) {
    return $this->predicates(array($predicate));
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
   * Orders the result set by relation columns.
   */
  public function order($field, $direction = 'ASC') {
    $this->order[] = array(
      'field' => $field,
      'direction' => $direction,
    );
    return $this;
  }

  /**
   * Limit the query to a range.
   *
   * @return RelationQuery
   */
  public function range($start, $length) {
    $this->start = $start;
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
  $query = db_select('relation_data', 'rd0');
  $query->addField('r', 'rid');
  $query->innerJoin('relation', 'r', 'r.rid = rd0.rid');
  foreach ($relationQuery->entityKeys as $key => $entity_key) {
    $alias = "rd$key";
    if ($key) {
      $query->innerJoin('relation_data', $alias, "r.rid = $alias.rid");
    }
    $query->condition("$alias.entity_type", $entity_key['entity_type']);
    $query->condition("$alias.entity_id", $entity_key['entity_id']);
    if (isset($entity_key['index'])) {
      $query->condition("$alias.index", $entity_key['index']);
    }
  }
  if ($relationQuery->predicates) {
    $query->condition('r.predicate', $relationQuery->predicates);
  }
  if ($relationQuery->count) {
    return $query->groupBy('r.rid')->countQuery()->execute()->fetchField();
  }
  $query->range($relationQuery->start, $relationQuery->length);
  foreach ($relationQuery->order as $order) {
    $query->orderBy('r.' . $order['field'], $order['direction']);
  }
  $rids = $query->execute()->fetchCol();
  return $rids ? entity_load('relation', $rids) : array();
}
