<?php

class Sabel_DB_Schema_SQLite extends Sabel_DB_Schema_Common
{
  protected
    $tableList    = "SELECT name FROM sqlite_master WHERE type = 'table'",
    $tableColumns = "SELECT * FROM sqlite_master WHERE name = '%s'";

  public function __construct($connectName, $schema = null)
  {
    $this->connectName = $connectName;
    $this->recordObj   = new Sabel_DB_Basic();
    $this->recordObj->setDriver($connectName);
  }

  public function getTables()
  {
    $tables = array();
    foreach ($this->recordObj->execute($this->tableList) as $val) {
      $tables[$val->name] = $this->getTable($val->name);
    }
    return $tables;
  }

  protected function createColumns($table)
  {
    $result  = $this->recordObj->execute(sprintf($this->tableColumns, $table));
    $creator = new Schema_Util_Creator();
    return $creator->create($result[0]->sql);
  }
}
