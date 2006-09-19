<?php

/**
 * Sabel_DB_Schema_Table
 *
 * @package org.sabel.db.schema
 * @author Ebine Yutaka <ebine.yutaka@gmail.com>
 */
class Sabel_DB_Schema_Table
{
  protected $tableName = '';
  protected $columns   = array();

  public function __construct($name, $columns = null)
  {
    $this->tableName = $name;
    if (isset($columns)) $this->columns = $columns;
  }

  public function __get($key)
  {
    return (isset($this->columns[$key])) ? $this->columns[$key] : null;
  }

  public function getTableName()
  {
    return $this->tableName;
  }

  public function getColumns()
  {
    return $this->columns;
  }

  public function getColumnByName($name)
  {
    return $this->columns[$name];
  }

  public function setColumns($columns)
  {
    if (!is_array($columns))
      throw new Exception('Error: Schema_Table::setColumns() argument must be an array.');

    $array = array();

    foreach ($columns as $cName => $values) {
      $vo = new ValueObject();

      $vo->name = $cName;
      $vo->type = $values['type'];

      if ($vo->type === Sabel_DB_Const::INT) {
        $vo->max = $values['max'];
        $vo->min = $values['min'];
      } else if ($vo->type === Sabel_DB_Const::STRING) {
        $vo->max = $values['max'];
      }

      $vo->increment = $values['increment'];
      $vo->notNull   = $values['notNull'];
      $vo->primary   = $values['primary'];
      $vo->default   = $values['default'];

      $array[$vo->name] = $vo;
    }
    $this->columns = $array;
  }
}
