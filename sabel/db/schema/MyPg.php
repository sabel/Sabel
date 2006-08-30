<?php

class Sabel_DB_Schema_MyPg
{
  const TABLE_LIST    = "SELECT table_name FROM information_schema.tables WHERE table_schema = '%s'";
  const TABLE_COLUMNS = "SELECT * FROM information_schema.columns WHERE table_schema = '%s' AND table_name = '%s'";
  const COLUMN        = "SELECT * FROM information_schema.columns WHERE table_schema = '%s' AND table_name = '%s' AND column_name = '%s'";

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
    $tables = array();
    $sql    = sprintf(self::TABLE_LIST, $this->schema);

    foreach ($this->recordObj->execute($sql) as $val) {
      $data  = array_change_key_case($val->toArray());
      $table = $data['table_name'];
      $tables[$table]  = new Sabel_DB_Schema_Table($table, $this->createColumns($table));
    }
    return $tables;
  }

  public function getTable($name)
  {
    return new Sabel_DB_Schema_Table($name, $this->createColumns($name));
  }

  public function createColumn($table, $column)
  {
    $sql = sprintf(self::COLUMN, $this->schema, $table, $column);
    $res = $this->recordObj->execute($sql);
    return $this->makeColumnValueObject(array_change_key_case($res[0]->toArray()));
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

    if (is_array($sqls = $this->addCommentInfo($co, $columnRecord))) {
      $oid = $this->recordObj->execute($sqls[0]);
      $pos = $columnRecord['ordinal_position'];

      $comment = $this->recordObj->execute(sprintf($sqls[1], $oid[0]->relfilenode, $pos));
      if (isset($comment)) $co->comment = $comment[0]->col_description;
    }

    return $this->setColumnType($co, $columnRecord);
  }

  protected function setColumnType($co, $columnRecord)
  {
    $type = $columnRecord['data_type'];

    if ($this->isBoolean($type, $columnRecord)) {
      $co->type = Sabel_DB_Schema_Type::BOOL;
    } else if (in_array($type, Sabel_DB_Schema_Type::$INTS)) {
      $co->type = Sabel_DB_Schema_Type::INT;
      Sabel_DB_Schema_Type::setRange($co, $type);
    } else if (in_array($type, Sabel_DB_Schema_Type::$STRS)) {
      $co->type = Sabel_DB_Schema_Type::STRING;
      $this->addStringLength($co, $columnRecord);
    } else if (in_array($type, Sabel_DB_Schema_Type::$TEXTS)) {
      $co->type = Sabel_DB_Schema_Type::TEXT;
    } else if (in_array($type, Sabel_DB_Schema_Type::$TIMES)) {
      $co->type = Sabel_DB_Schema_Type::TIMESTAMP;
    } else if ($type === 'date') {
      $co->type = Sabel_DB_Schema_Type::DATE;
    }

    return $co;
  }
}
