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
      $vo->type = $values[0];

      if ($vo->type === Sabel_DB_Schema_Type::INT) {
        $vo->max = $values[1];
        $vo->min = $values[2];
      } else if ($vo->type === Sabel_DB_Schema_Type::STRING) {
        $vo->max = (int)$values[1];
      }

      $c = count($values) - 4;
      for ($i = 0; $i < $c; $i++) unset($values[$i]);

      $values = array_values($values);

      $vo->increment = $values[0];
      $vo->notNull   = $values[1];
      $vo->primary   = $values[2];
      $vo->default   = $values[3];

      $array[$vo->name] = $vo;
    }
    $this->columns = $array;
  }
}
