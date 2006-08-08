<?php

require_once "RecordObject.php";

class Edo_InformationSchema extends Sabel_Edo_RecordObject
{
  protected $edo, $is, $database;

  public function __construct($database = null)
  {
    $this->database = $database;
  }

  public function dbinit($dbuser, $useEdo)
  {
    $db = Sabel_Edo_DBConnection::getDB($dbuser);
    $className = 'Edo_'. $db .'_informationSchema';

    $this->is  = new $className();
    $this->setEDO($dbuser, $useEdo);
  }

  public function getTableList()
  {
    $sql = "select * from information_schema.tables where table_schema = '{$this->database}'";
    $res = $this->execute($sql);
    $res = array_change_key_case($res);

    foreach ($res as $val) $tableList[] = $val->table_name;
    return $tableList;
  }

  public function getColumnsInfo($table)
  {
    $sql = "select * from information_schema.columns where table_name = '{$table}'";
    $res = $this->execute($sql);

    foreach ($res as $val) {
      $data = array_change_key_case($val->toArray()); 
      $columns[] = $this->getColumnInfo($table, $data['column_name']);
    }
    return $columns;
  }

  public function getColumnInfo($table, $column = null)
  {
    if (is_null($column)) return $this->getColumnsInfo($table);

    $sql  = "select * from information_schema.columns ";
    $sql .= "where table_name = '{$table}' and column_name = '{$column}'";

    $res = $this->execute($sql);
    return $this->makeColumnValueObject(array_change_key_case($res[0]->toArray()));
  }

  protected function makeColumnValueObject($columnRecord)
  {
    $co = new ColumnObject();
    $co->name    = $columnRecord['column_name'];
    $co->default = $columnRecord['column_default'];
    $co->notNull = ($columnRecord['is_nullable'] == 'NO') ? true : false;

    $this->is->addIncrementInfo($co, $columnRecord);

    foreach ($this->is->getNumericTypes() as $val) {
      if ($val == $columnRecord['data_type']) {
        $co->type = 'int';
        $co->convertToEdoInteger($columnRecord['data_type']);
        break;
      }
    }

    foreach ($this->is->getStringTypes() as $val) {
      if ($val == $columnRecord['data_type']) {
        $co->type = 'string';
        $this->is->addStringLength($co, $columnRecord);
        break;
      }
    }
    return $co;
  }
}

class Edo_Mysql_InformationSchema extends Edo_InformationSchema
{
  public function __construct()
  {

  }

  public function getNumericTypes()
  {
    return array('tinyint', 'smallint', 'mediumint', 'int', 'bigint');
  }

  public function getStringTypes()
  {
    return array('text', 'mediumtext', 'varchar', 'char', 'blob', 'mediumblob', 'longblob');
  }

  public function addIncrementInfo($co, $columnRecord)
  {
    $co->increment = ($columnRecord->extra == 'auto_increment') ? true : false;
  }

  public function addStringLength($co, $columnRecord)
  {
    $co->maxLength = $columnRecord['character_maximum_length'];
  }
}

class Edo_Pgsql_InformationSchema extends Edo_InformationSchema
{
  public function __construct()
  {

  }

  public function getNumericTypes()
  {
    return array('smallint', 'integer', 'bigint');
  }

  public function getStringTypes()
  {
    return array('text', 'character varying', 'varchar', 'character', 'char');
  }

  public function addIncrementInfo($co, $columnRecord)
  {
    //$sql = "select * from seq_id_seq limit 1";
    //$res = $this->execute($sql);
    //$co->increment = ($columnRecord->EXTRA == 'auto_increment') ? true : false;
  }

  public function addStringLength($co, $columnRecord)
  {
    if (is_null($columnRecord['character_maximum_length'])) {
      $co->maxLength = 65535;
    } else {
      $co->maxLength = $columnRecord['character_maximum_length'];
    }
  }
}

class ColumnObject
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
    if ($type == 'tinyint') {
      $this->data['maxValue'] =  127;
      $this->data['minValue'] = -128;
    } elseif ($type == 'smallint') {
      $this->data['maxValue'] =  32767;
      $this->data['minValue'] = -32768;
    } elseif ($type == 'mediumint') {
      $this->data['maxValue'] =  8388607;
      $this->data['minValue'] = -8388608;
    } elseif ($type == 'int' || $type == 'integer') {
      $this->data['maxValue'] =  2147483647;
      $this->data['minValue'] = -2147483648;
    } elseif ($type == 'bigint') {
      $this->data['maxValue'] =  9223372036854775807;
      $this->data['minValue'] = -9223372036854775808;
    }
  }

  public function convertToEdoString($type)
  {

  }
}

?>
