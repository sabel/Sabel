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
}
