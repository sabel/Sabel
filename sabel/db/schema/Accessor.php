<?php

require_once "Mysql.php";
require_once "Pgsql.php";

class Sabel_DB_Schema_Accessor
{
  protected $is = null;

  public function __construct($connectName, $schema)
  {
    $dbName    = Sabel_DB_Connection::getDB($connectName);
    $className = "Sabel_DB_Schema_{$dbName}";
    $this->is  = new $className($connectName, $schema);
  }

  public function getTables()
  {
    return $this->is->getTables();
  }

  public function getTable($name)
  {
    return $this->is->getTable($name);
  }

  protected function createColumns($table)
  {
    return $this->is->createColumns($table);
  }

  protected function createColumn($table, $column = null)
  {
    return $this->is->createColumn($table, $column);
  }
}

class Sabel_DB_Schema_Table
{
  protected $tableName = '';
  protected $columns   = array();

  public function __construct($name, $columns)
  {
    $this->tableName = $name;
    $this->columns   = $columns;
  }

  public function getTableName()
  {
    return $this->tableName;
  }

  public function getColumns()
  {
    return $this->columns;
  }

  public function getColumnByName($name)
  {
    return $this->columns[$name];
  }
}

class Sabel_DB_Schema_Column
{
  protected $data = array();

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
  }

  public function __get($key)
  {
    return $this->data[$key];
  }

  public function convertToEdoInteger($type)
  {
    $data =& $this->data;

    switch($type) {
      case 'tinyint':
        $data['maxValue'] =  127;
        $data['minValue'] = -128;
        break;
      case 'smallint':
        $data['maxValue'] =  32767;
        $data['minValue'] = -32768;
        break;
      case 'mediumint':
        $data['maxValue'] =  8388607;
        $data['minValue'] = -8388608;
        break;
      case 'int':
      case 'integer':
        $data['maxValue'] =  2147483647;
        $data['minValue'] = -2147483648;
        break;
      case 'bigint':
        $data['maxValue'] =  9223372036854775807;
        $data['minValue'] = -9223372036854775808;
        break;
    }
  }
}
