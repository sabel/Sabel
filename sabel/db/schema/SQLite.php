<?php

class Sabel_DB_Schema_SQLite
{
  protected $constraint = '';

  public function __construct($connectName, $schema = null)
  {
    $this->connectName = $connectName;
    $this->recordObj   = new Sabel_DB_Basic();
    $this->recordObj->setDriver($connectName);
  }

  public function getTables()
  {
    $sql = "SELECT name FROM sqlite_master WHERE type = 'table'";

    $tables = array();
    foreach ($this->recordObj->execute($sql) as $val) {
      $tables[$val->name] = new Sabel_DB_Schema_Table($val->name, $this->createColumns($val->name));
    }
    return $tables;
  }

  public function getTable($name)
  {
    return new Sabel_DB_Schema_Table($name, $this->createColumns($name));
  }

  protected function createColumns($table)
  {
    $columns = array();
    $lines = $this->getColumnsLine($table);
    foreach ($lines as $line) {
      $co = $this->makeColumnValueObject($line);
      $columns[$co->name] = $co;
    }
    return $columns;
  }

  protected function createColumn($table, $column)
  {
    //@todo
  }

  protected function makeColumnValueObject($line)
  {
    $co = new Sabel_DB_Schema_Column();
    $co->name = substr($line, 0, strpos($line, ' '));

    $rem = trim(str_replace($co->name, '', $line));
    $this->setColumnType($co, $rem);
    $this->setNotNull($co);
    $this->setPrimaryKey($co);
    $this->setDefault($co);

    return $co;
  }

  protected function getColumnsLine($table)
  {
    $sql = "SELECT * FROM sqlite_master WHERE name = '{$table}'";
    $res = $this->recordObj->execute($sql);

    $createSQL = substr(strpbrk($res[0]->sql, '('), 0);
    $createSQL = explode(',', substr($createSQL, 1, strlen($createSQL) - 2));

    return array_map('trim', $createSQL);
  }

  protected function setColumnType($co, $rem)
  {
    $tmp = substr($rem, 0, strpos($rem, ' '));
    $type = ($tmp === '') ? $rem : $tmp;
    $this->constraint = trim(str_replace($type, '', $rem));

    $co->increment = (stripos($rem, 'INTEGER PRIMARY KEY') !== false);

    if (stripos($type, 'INT') !== false) {
      $co->type = Sabel_DB_Schema_Type::INT;
      $co->setNumericRange(strtolower($type));
    } else {
      if ($text = strpbrk($type, '(')) {
        $co->type = Sabel_DB_Schema_Type::STRING;
        $co->max = substr($text, 1, strlen($text) - 2);
      } else {
        $this->setOtherTypes($co, $type);
      }
    }
  }

  protected function setOtherTypes($co, $type)
  {
    if (stripos($type, 'TEXT') !== false) {
      $co->type = Sabel_DB_Schema_Type::TEXT;
      $co->max = 65535;
    } else if (stripos($type, 'BOOLEAN') !== false) {
      $co->type = Sabel_DB_Schema_Type::BOOL;
    } else if (stripos($type, 'TIMESTAMP') !== false) {
      $co->type = Sabel_DB_Schema_Type::TIMESTAMP;
    } else if (stripos($type, 'DATE') !== false) {
      $co->type = Sabel_DB_Schema_Type::DATE;
    }
  }

  protected function setNotNull($co)
  {
    if ($this->constraint === '') {
      $co->notNull = false;
    } else {
      $co->notNull = (stripos($this->constraint, 'NOT NULL') !== false);
      $this->constraint = str_ireplace('NOT NULL', '', $this->constraint);
    }
  }

  protected function setPrimaryKey($co)
  {
    if ($this->constraint === '') {
      $co->primary = false;
    } else {
      $co->primary = (stripos($this->constraint, 'PRIMARY KEY') !== false);
      $this->constraint = str_ireplace('PRIMARY KEY', '', $this->constraint);
    }
  }

  protected function setDefault($co)
  {
    if ($this->constraint === '') {
      $co->default = null;
    } else {
      if (stripos($this->constraint, 'DEFAULT') !== false) {
        $default = trim(substr($this->constraint, 8));
        $co->default = (ctype_digit($default)) ? $default : substr($default, 1, strlen($default) - 2);
      } else {
        $co->default = null;
      }
    }
  }
}
