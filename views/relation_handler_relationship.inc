<?php

/**
 * @file
 * Views relationship support.
 */

abstract class relation_handler_relationship_base extends views_handler_relationship {
  function init(&$view, &$options) {
    parent::init($view, $options);
    $field = field_info_field('endpoints');
    $this->relation_data_table_name = _field_sql_storage_tablename($field);
    $this->entity_id_field_name = _field_sql_storage_columnname('endpoints', 'entity_id');
    $this->entity_type_field_name = _field_sql_storage_columnname('endpoints', 'entity_type');
    $this->r_index_field_name = _field_sql_storage_columnname('endpoints', 'r_index');
  }

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
}

class relation_handler_relationship extends relation_handler_relationship_base {
  function query() {
    $this->ensure_my_table();
    $type = empty($this->options['required']) ? 'LEFT' : 'INNER';

    // Join the left table with the entity type to the endpoints field data table.
    $join = new views_join();
    $join->definition = array(
      'left_table' => $this->table_alias,
      'left_field' => $this->real_field,
      'table'      => $this->relation_data_table_name,
      'field'      => $this->entity_id_field_name,
      'type'       => $type,
      'extra'      => array(
        array(
          'field' => 'bundle',
          'value' => $this->definition['relation_type'],
        ),
        array(
          'field' => $this->entity_type_field_name,
          'value' => $this->definition['entity_type_left'],
        ),
      ),
    );
    if ($this->definition['directional'] && $this->options['r_index'] > -1) {
      $join->definition['extra'][] = array(
        'field' => $this->r_index_field_name,
        'value' => $this->options['r_index'],
      );
    }
    $join->construct();
    $join->adjusted = TRUE;
    $l = $this->query->add_table($this->relation_data_table_name, $this->relationship, $join);

    // Execute a self-join.
    $join = new views_join();
    $join->definition = array(
      'left_table' => $l,
      'left_field' => 'entity_id',
      'table'      => $this->relation_data_table_name,
      'field'      => 'entity_id',
      'type'       => $type,
      'extra'      => array(
        array(
          'field' => $this->entity_type_field_name,
          'value' => $this->definition['entity_type_right'],
        ),
      ),
    );
    $join->construct();
    $join->adjusted = TRUE;
    $r = $this->query->add_table($this->relation_data_table_name, $this->relationship, $join);

    $join = new views_join();
    $join->definition = array(
      'left_table' => $r,
      'left_field' => $this->entity_id_field_name,
      'table'      => $this->definition['base'],
      'field'      => $this->definition['base field'],
      'type'       => $type,
    );
    // This technically should be on an earlier extra but this works just
    // fine.
    if ($this->definition['entity_type_left'] == $this->definition['entity_type_right']) {
      $join->definition['extra'] = "$l.$this->r_index_field_name != $r.$this->r_index_field_name";
    }
    $join->construct();
    $join->adjusted = TRUE;
    // use a short alias for this:
    $alias = $this->definition['base'] . '_' . $this->table;
    $this->alias = $this->query->add_relationship($alias, $join, $this->definition['base'], $this->relationship);
  }
}

class relation_handler_relationship_left extends relation_handler_relationship_base {
}

class relation_handler_relationship_right extends relation_handler_relationship_base {
}
