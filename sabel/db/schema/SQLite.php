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
      $tables[$val->name] = $this->getTable($val->name);
    }
    return $tables;
  }

  public function getTable($name)
  {
    return new Sabel_DB_Schema_Table($name, $this->createColumns($name));
  }

  public function createColumn($table, $column)
  {
    $columns = $this->getSchema($table)->getColumns();
    return (array_key_exists($column, $columns)) ? $columns[$column] : null;
  }

  protected function createColumns($table)
  {
    return $this->getSchema($table)->getColumns();
  }

  protected function getSchema($table)
  {
    $res    = $this->recordObj->execute(sprintf(self::TABLE_COLUMNS, $table));
    $parser = new Schema_Parser();

    $tableObj = Schema_Creator::create($parser->parse($res[0]->sql));
    return $tableObj;
  }
}
