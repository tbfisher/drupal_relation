<?php

/**
 * @file
 * Views relationship support.
 */

class relation_handler_relationship extends views_handler_relationship {
  /**
   * Define r_index option.
   */
  function option_definition() {
    $options = parent::option_definition();
    $options['r_index'] = array('default' => -1);
    return $options;
  }

  /**
   * Let the user choose r_index.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);

    $options = $this->options_form_summary_options();
    if ($this->definition['directional']) {
      $form['r_index'] = array(
        '#type' => 'select',
        '#options' => $options,
        '#title' => t('Position of the relationship base'),
        '#default_value' => $this->options['r_index'],
        // check_plain()'d in the definition.
        '#description' => t('Select whether the entity you are adding the relationship to is source or target of !relation_type_label relation.', array('!relation_type_label' => $this->definition['label'])),
      );
    }
  }

  /**
   * Return the main options, which are shown in the summary title.
   */
  function options_form_summary_options() {
    return $this->definition['directional'] ? array(
      -1 => t('Any'),
      0 => t('Source'),
      1 => t('Target'),
    ) : array();
  }

  function query() {
    $field = field_info_field('endpoints');
    $relation_data_table_name = _field_sql_storage_tablename($field);
    $entity_id_field_name = _field_sql_storage_columnname('endpoints', 'entity_id');
    $entity_type_field_name = _field_sql_storage_columnname('endpoints', 'entity_type');
    $r_index_field_name = _field_sql_storage_columnname('endpoints', 'r_index');
    $relation_type = $this->definition['relation_type'];
    $entity_type_left = $this->definition['entity_type_left'];
    $this->ensure_my_table();
    $type = empty($this->options['required']) ? 'LEFT' : 'INNER';

    // Join the left table with the entity type to the endpoints field data table.
    $join = new views_join();
    $join->definition = array(
      'left_table' => $this->table_alias,
      'left_field' => $this->real_field,
      'table'      => $relation_data_table_name,
      'field'      => $entity_id_field_name,
      'type'       => $type,
      'extra'      => array(
        array(
          'field' => 'bundle',
          'value' => $relation_type,
        ),
        array(
          'field' => $entity_type_field_name,
          'value' => $entity_type_left,
        ),
      ),
    );
    if ($this->definition['directional'] && $this->options['r_index'] > -1) {
      $join->definition['extra'][] = array(
        'field' => $r_index_field_name,
        'value' => $this->options['r_index'],
      );
    }
    $join->construct();
    $join->adjusted = TRUE;
    $l = $this->query->add_table($relation_data_table_name, $this->relationship, $join);

    // Execute a self-join.
    $join = new views_join();
    $join->definition = array(
      'left_table' => $l,
      'left_field' => 'entity_id',
      'table'      => $relation_data_table_name,
      'field'      => 'entity_id',
      'type'       => $type,
      'extra'      => array(
        array(
          'field' => $entity_type_field_name,
          'value' => $this->definition['entity_type_right'],
        ),
      ),
    );
    $join->construct();
    $join->adjusted = TRUE;
    $r = $this->query->add_table($relation_data_table_name, $this->relationship, $join);

    $join = new views_join();
    $join->definition = array(
      'left_table' => $r,
      'left_field' => $entity_id_field_name,
      'table'      => $this->definition['base'],
      'field'      => $this->definition['base field'],
      'type'       => $type,
    );
    // This technically should be on an earlier extra but this works just
    // fine.
    if ($this->definition['entity_type_left'] == $this->definition['entity_type_right']) {
      $join->definition['extra'] = "$l.$r_index_field_name != $r.$r_index_field_name";
    }
    $join->construct();
    $join->adjusted = TRUE;
    // use a short alias for this:
    $alias = $this->definition['base'] . '_' . $this->table;
    $this->alias = $this->query->add_relationship($alias, $join, $this->definition['base'], $this->relationship);
  }
}

class relation_handler_base_left_join extends views_join {
  /**
   * Build the SQL for the join this object represents.
   */
  function build_join($select_query, $table, $view_query) {
    $field = field_info_field('endpoints');
    $relation_data_table_name = _field_sql_storage_tablename($field);
    $entity_id_field_name = _field_sql_storage_columnname('endpoints', 'entity_id');
    $entity_type_field_name = _field_sql_storage_columnname('endpoints', 'entity_type');
    $r_index_field_name = _field_sql_storage_columnname('endpoints', 'r_index');
    // Join the left table with the entity type to the relation table.
    $left = $view_query->get_table_info($this->left_table);
    $entity_type_left = $this->definition['entity_type_left'];
    $conditions = "$left[alias].$this->left_field = %alias.$entity_id_field_name AND %alias.$entity_type_field_name = '$entity_type_left' AND %alias.$r_index_field_name = 0";
    // Left join alias.
    $l = $select_query->addJoin($this->type, $relation_data_table_name, NULL, $conditions);
    // entity_id here is the ID of the relation entity.
    $relation_type = $this->definition['relation_type'];
    $conditions = "%alias.rid = $l.entity_id AND %alias.relation_type = '$relation_type'";
    $select_query->addJoin($this->type, $table['table'], $table['alias'], $conditions);
  }
}

class relation_handler_base_right_join extends views_join {
  /**
   * Build the SQL for the join this object represents.
   */
  function build_join($select_query, $table, $view_query) {
    $field = field_info_field('endpoints');
    $relation_table = $view_query->get_table_info($this->left_table);
    $relation_data_table_name = _field_sql_storage_tablename($field);
    $entity_id_field_name = _field_sql_storage_columnname('endpoints', 'entity_id');
    $entity_type_field_name = _field_sql_storage_columnname('endpoints', 'entity_type');
    $r_index_field_name = _field_sql_storage_columnname('endpoints', 'r_index');
    $conditions = "$relation_table[alias].rid = %alias.entity_id AND %alias.$r_index_field_name = 1";
    $l = $select_query->addJoin($this->type, $relation_data_table_name, NULL, $conditions);
    $entity_type_right = $this->definition['entity_type_right'];
    $conditions = "$l.$entity_type_field_name = '$entity_type_right' AND %alias.$this->field = $l.$entity_id_field_name";
    $select_query->addJoin($this->type, $table['table'], $table['alias'], $conditions);
  }
}
