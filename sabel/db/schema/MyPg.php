<?php

class Sabel_DB_Schema_MyPg
{
  protected
    $recordObj   = null,
    $connectName = '',
    $schema      = '';

  public function __construct($connectName, $schema)
  {
    $this->schema      = $schema;
    $this->connectName = $connectName;
    $this->recordObj   = new Sabel_DB_Basic();
    $this->recordObj->setDriver($connectName);
  }

  public function getTables()
  {
    $sql = "SELECT * FROM information_schema.tables WHERE table_schema = '{$this->schema}'";

    $tables = array();
    foreach ($this->recordObj->execute($sql) as $val) {
      $data      = array_change_key_case($val->toArray());
      $tableName = $data['table_name'];
      $tables[]  = new Sabel_DB_Schema_Table($tableName, $this->createColumns($tableName));
    }
    return $tables;
  }

  public function getTable($name)
  {
    return new Sabel_DB_Schema_Table($name, $this->createColumns($name));
  }

  protected function createColumns($table)
  {
    $sql  = "SELECT * FROM information_schema.columns ";
    $sql .= "WHERE table_schema = '{$this->schema}' AND table_name = '{$table}'";

    $columns = array();
    foreach ($this->recordObj->execute($sql) as $val) {
      $data = array_change_key_case($val->toArray());
      $columnName = $data['column_name'];
      $columns[$columnName] = $this->makeColumnValueObject($data);
    }
    return $columns;
  }

  protected function createColumn($table, $column = null)
  {
    if (is_null($column)) return $this->createColumns($table);

    $sql  = "SELECT * FROM information_schema.columns ";
    $sql .= "WHERE table_schema = '{$this->schema}' AND table_name = '{$table}' AND column_name = '{$column}'";

    $res = $this->recordObj->execute($sql);
    return $this->makeColumnValueObject(array_change_key_case($res[0]->toArray()));
  }

  protected function makeColumnValueObject($columnRecord)
  {
    $co = new Sabel_DB_Schema_Column();
    $co->name    = $columnRecord['column_name'];
    $co->default = $columnRecord['column_default'];
    $co->notNull = ($columnRecord['is_nullable'] === 'NO');

    if (is_string($sql = $this->addIncrementInfo($co, $columnRecord))) {
      $co->increment = (count($this->recordObj->execute($sql)) > 0);
    }

    if (is_array($sqls = $this->addCommentInfo($co, $columnRecord))) {
      $oid = $this->recordObj->execute($sqls[0]);
      $pos = $this->recordObj->execute($sqls[1]);

      $comment = $this->recordObj->execute(sprintf($sqls[2], $oid[0]->relfilenode, $pos[0]->ordinal_position));
      $co->comment = $comment[0]->col_description;
    }

    $type = $columnRecord['data_type'];

    if (in_array($type, $this->getNumericTypes())) {
      $co->type = Sabel_DB_Schema_Type::INT;
      $co->convertToEdoInteger($columnRecord['data_type']);
      return $co;
    }

    if (in_array($type, $this->getStringTypes())) {
      $co->type = Sabel_DB_Schema_Type::STRING;
      $this->addStringLength($co, $columnRecord);
      return $co;
    }

    if (in_array($type, $this->getTextTypes())) {
      $co->type = Sabel_DB_Schema_Type::TEXT;
      return $co;
    }
  }
}
