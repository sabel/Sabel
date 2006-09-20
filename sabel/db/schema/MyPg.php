<?php

class Sabel_DB_Schema_MyPg extends Sabel_DB_Schema_General
{
  const TABLE_LIST    = "SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'";
  const TABLE_COLUMNS = "SELECT * FROM information_schema.columns WHERE table_schema = '%s' AND table_name = '%s'";

  protected $schema   = '';

  public function __construct($connectName, $schema)
  {
    $this->schema      = $schema;
    $this->connectName = $connectName;
    $this->recordObj   = new Sabel_DB_Basic();
    $this->recordObj->setDriver($connectName);
  }

  public function getTables()
  {
    $tables = array();
    $sql    = sprintf(self::TABLE_LIST, $this->schema);

    foreach ($this->recordObj->execute($sql) as $val) {
      $data  = array_change_key_case($val->toArray());
      $table = $data['table_name'];
      $tables[$table] = $this->getTable($table);
    }
    return $tables;
  }

  protected function createColumns($table)
  {
    $columns = array();
    $sql     = sprintf(self::TABLE_COLUMNS, $this->schema, $table);

    foreach ($this->recordObj->execute($sql) as $val) {
      $data = array_change_key_case($val->toArray());
      $columnName = $data['column_name'];
      $columns[$columnName] = $this->makeColumnValueObject($data);
    }
    return $columns;
  }

  protected function makeColumnValueObject($columnRecord)
  {
    $co = new Sabel_DB_Schema_Column();
    $co->name    = $columnRecord['column_name'];
    $co->notNull = ($columnRecord['is_nullable'] === 'NO');

    $this->addDefaultInfo($co, $columnRecord['column_default']);

    if (is_string($sql = $this->addIncrementInfo($co, $columnRecord)))
      $co->increment = (count($this->recordObj->execute($sql)) > 0);

    if (is_string($sql = $this->addPrimaryKeyInfo($co, $columnRecord)))
      $co->primary = (count($this->recordObj->execute($sql)) > 0);

    Sabel_DB_Schema_TypeSetter::send($co, $columnRecord['data_type']);

    if ($co->type === Sabel_DB_Const::STRING)
      $this->addStringLength($co, $columnRecord);

    return $co;
  }
}
