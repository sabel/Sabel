<?php

class Sabel_DB_Schema_SQLite extends Sabel_DB_Schema_General
{
  const TABLE_LIST    = "SELECT name FROM sqlite_master WHERE type = 'table'";
  const TABLE_COLUMNS = "SELECT * FROM sqlite_master WHERE name = '%s'";

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
      $table = $val->name;
      $tables[$table] = $this->getTable($table);
    }
    return $tables;
  }

  protected function createColumns($table)
  {
    $result  = $this->recordObj->execute(sprintf(self::TABLE_COLUMNS, $table));
    $creator = new Schema_Util_Creator();
    return $creator->create($result[0]->sql);
  }
}
