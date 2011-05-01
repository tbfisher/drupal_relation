<?php


class relation_handler_relationship extends views_handler_relationship {
}

class relation_handler_join extends views_join {
  /**
   * Build the SQL for the join this object represents.
   */
  function build_join($select_query, $table, $view_query) {
    $directional = $this->definition['directional'];
    // Join the left table with the entity type to the relation_data table.
    $left = $view_query->get_table_info($this->left_table);
    $entity_type_left = $this->definition['entity_type_left'];
    $conditions = "$left[alias].$this->left_field = %alias.entity_id AND %alias.entity_type = '$entity_type_left'";
    if ($directional) {
      $conditions .= " AND %alias.r_index = 0";
    }
    $l = $left_join_alias = $select_query->innerJoin("relation_data", NULL, $conditions);

    // Execute a self-join.
    $entity_type_right = $this->definition['entity_type_right'];
    $conditions = "%alias.rid = $l.rid AND %alias.r_index != $l.r_index AND %alias.entity_type = '$entity_type_right'";
    $r = $right_join_alias = $select_query->innerJoin("relation_data", NULL, $conditions);

    // Join the right table to the relation_data table.
    $conditions = "%alias.$this->field = $r.entity_id";
    $select_query->innerJoin($this->table, $table['alias'], $conditions);
  }
}
