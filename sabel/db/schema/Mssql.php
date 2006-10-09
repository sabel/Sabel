<?php

class Sabel_DB_Schema_Mssql extends Sabel_DB_Schema_General
{
  const TABLE_LIST    = "SELECT table_name FROM information_schema.tables WHERE table_catalog = '%s'";
  const TABLE_COLUMNS = "SELECT * FROM information_schema.columns WHERE table_catalog = '%s' AND table_name = '%s'"; 

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

    $type = $columnRecord['data_type'];

    if ($this->isBoolean($type, $columnRecord)) {
      $co->type = Sabel_DB_Const::BOOL;
    } else {
      Sabel_DB_Schema_TypeSetter::send($co, $type);
    }

    $this->addDefaultInfo($co, $columnRecord['column_default']);
    $this->addIncrementInfo($co, $columnRecord);
    $this->addPrimaryKeyInfo($co, $columnRecord);

    if ($co->type === Sabel_DB_Const::STRING) $this->addStringLength($co, $columnRecord);
    return $co;
  }

  protected function isBoolean($type, $columnRecord)
  {
    return ($type === 'bit');
  }

  protected function addDefaultInfo($co, $default)
  {
    if (is_null($default)) {
      $co->default = null;
    } else {
      $default = substr($default, 2, -2);
      if ($co->type === Sabel_DB_Const::BOOL) {
        $co->default = ($default === 'true');
      } else {
        $co->default = (is_numeric($default)) ? (int)$default : $default;
      }
    }
  }

  protected function addIncrementInfo($co, $columnRecord)
  {
    $sql  = "SELECT * from sys.objects obj, sys.identity_columns ident ";
    $sql .= "WHERE obj.name = '{$columnRecord['table_name']}' AND ident.name = '{$co->name}' AND ";
    $sql .= "obj.object_id = ident.object_id";

    $co->increment = ($this->recordObj->execute($sql) !== false);
  }

  protected function addPrimaryKeyInfo($co, $columnRecord)
  {
    $sql  = "SELECT const.type FROM information_schema.constraint_column_usage col, sys.key_constraints const ";
    $sql .= "WHERE col.table_catalog = '{$this->schema}' AND col.table_name = '{$columnRecord['table_name']}' AND ";
    $sql .= "col.column_name = '{$co->name}' AND col.constraint_name = const.name";

    if ($result = $this->recordObj->execute($sql)) {
      $co->primary = ($result[0]->type === 'PK');
    } else {
      $co->primary = false;
    }
  }

  protected function addStringLength($co, $columnRecord)
  {
    $co->max = $columnRecord['character_octet_length'];
  }
}
