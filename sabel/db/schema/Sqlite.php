<?php

class Sabel_DB_Schema_SQLite extends Sabel_DB_Schema_Common
{
  protected
    $tableList    = "SELECT name FROM sqlite_master WHERE type = 'table'",
    $tableColumns = "SELECT * FROM sqlite_master WHERE name = '%s'";

  public function __construct($connectName, $schema = null)
  {
    $this->driver = Sabel_DB_Connection::createDBDriver($connectName);
  }

  public function getTables()
  {
    $tables = array();

    $this->driver->execute($this->tableList);
    foreach ($this->driver->getResultSet() as $row) {
      $tblName = $row['name'];
      $tables[$tblName] = $this->getTable($tblName);
    }
    return $tables;
  }

  protected function createColumns($table)
  {
    $this->driver->execute(sprintf($this->tableColumns, $table));
    $row = $this->driver->getResultSet()->fetch();
    $creator = new Sabel_DB_Schema_Util_Creator();
    return $creator->create($result['sql']);
  }
}
