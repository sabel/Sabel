<?php

class Sabel_DB_Schema_Accessor
{
  protected $is = null;

  public function __construct($connectName, $schema)
  {
    $dbName    = Sabel_DB_Connection::getDB($connectName);
    $className = "Sabel_DB_Schema_{$dbName}";
    $this->is  = new $className($connectName, $schema);
  }

  public function getTables()
  {
    return $this->is->getTables();
  }

  public function getTable($name)
  {
    return $this->is->getTable($name);
  }

  protected function createColumns($table)
  {
    return $this->is->createColumns($table);
  }

  protected function createColumn($table, $column = null)
  {
    if (is_null($column)) return $this->is->createColumns($table);

    return $this->is->createColumn($table, $column);
  }
}
