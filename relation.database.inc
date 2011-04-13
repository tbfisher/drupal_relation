<?php

/**
 * Interface for relation handlers.
 */
interface RelationQueryInterface {

  public function getRelated($entity, $type);

  function init($left, $right); // sets types

}

/**
 * Handler class for entity relations.
 */
class RelationQuery implements RelationQueryInterface {

  function __construct() {
  }

  function init($left, $right) {
  }

  /**
   * Entity is a fully loaded entity (node, user, term, etc.)
   * Type is the predicate.
   */
  public function getRelated($entity, $type) {
    return NULL;
  }
}
