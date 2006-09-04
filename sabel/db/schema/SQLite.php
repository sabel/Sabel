<?php

class Sabel_DB_Schema_SQLite
{
  const TABLE_LIST    = "SELECT name FROM sqlite_master WHERE type = 'table'";
  const TABLE_COLUMNS = "SELECT * FROM sqlite_master WHERE name = '%s'";

  protected $constraint = '';

  public function __construct($connectName, $schema = null)
  {
    $this->connectName = $connectName;
    $this->recordObj   = new Sabel_DB_Basic();
    $this->recordObj->setDriver($connectName);
  }

  public function getTables()
  {
    $tables = array();
    foreach ($this->recordObj->execute(self::TABLE_LIST) as $val) {
      $columns = $this->createColumns($val->name);
      $tables[$val->name] = new Sabel_DB_Schema_Table($val->name, $columns);
    }
    return $tables;
  }

  public function getTable($name)
  {
    return new Sabel_DB_Schema_Table($name, $this->createColumns($name));
  }

  public function createColumn($table, $column)
  {
    $columns = $this->getSchemaColumn($table);
    return (array_key_exists($column, $columns)) ? $columns[$column] : null;
  }

  protected function createColumns($table)
  {
    $columns = array();
    $columns = $this->getSchemaColumn($table);
    foreach ($columns as $column) {
      $columns[$column->name] = $column;
    }
    return $columns;
  }

  protected function getSchemaColumn($table)
  {
    $res   = $this->recordObj->execute(sprintf(self::TABLE_COLUMNS, $table));
    $maker = new Maker($res[0]->sql);

    $columns = Parser::create($maker->getColumns());
    return $columns;
  }
}
