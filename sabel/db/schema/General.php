<?php

class Sabel_DB_Schema_General extends Sabel_DB_Schema_Common
{
  protected $schema = '';

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
    $sql    = sprintf($this->tableList, $this->schema);

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
    $sql     = sprintf($this->tableColumns, $this->schema, $table);

    foreach ($this->recordObj->execute($sql) as $val) {
      $row = array_change_key_case($val->toArray());
      $colName = $row['column_name'];
      $columns[$colName] = $this->makeColumnValueObject($row);
    }
    return $columns;
  }

  protected function makeColumnValueObject($row)
  {
    $co = new Sabel_DB_Schema_Column();
    $co->name    = $row['column_name'];
    $co->notNull = ($row['is_nullable'] === 'NO');

    $type = $row['data_type'];

    if ($this->isBoolean($type, $row)) {
      $co->type = Sabel_DB_Const::BOOL;
    } else {
      Sabel_DB_Schema_Type_Setter::send($co, $type);
    }

    $this->setDefault($co, $row);
    $this->setIncrement($co, $row);
    $this->setPrimaryKey($co, $row);

    if ($co->type === Sabel_DB_Const::STRING) $this->setLength($co, $row);
    return $co;
  }

  protected function execute($sql)
  {
    return $this->recordObj->execute($sql);
  }
}
