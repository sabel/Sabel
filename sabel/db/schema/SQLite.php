<?php

class Sabel_DB_Schema_SQLite
{
  const TABLE_LIST    = "SELECT name FROM sqlite_master WHERE type = 'table'";
  const TABLE_COLUMNS = "SELECT * FROM sqlite_master WHERE name = '%s'";

  protected $constraint = '';

  public function __construct($connectName, $schema = null)
  {
    $this->connectName = $connectName;
    $this->recordObj   = new Sabel_DB_Basic();
    $this->recordObj->setDriver($connectName);
  }

  public function getTables()
  {
    $tables = array();
    foreach ($this->recordObj->execute(self::TABLE_LIST) as $val) {
      $tables[$val->name] = new Sabel_DB_Schema_Table($val->name, $this->createColumns($val->name));
    }
    return $tables;
  }

  public function getTable($name)
  {
    return new Sabel_DB_Schema_Table($name, $this->createColumns($name));
  }

  public function createColumn($table, $column)
  {
    $lines = $this->getCreatesqlLines($table);
    foreach ($lines as $line) {
      $name = $this->getName($line);
      if ($name === $column) return $this->makeColumnValueObject($line);
    }
  }

  protected function createColumns($table)
  {
    $columns = array();
    $lines = $this->getCreatesqlLines($table);
    foreach ($lines as $line) {
      $co = $this->makeColumnValueObject($line);
      $columns[$co->name] = $co;
    }
    return $columns;
  }

  protected function makeColumnValueObject($line)
  {
    $co = new Sabel_DB_Schema_Column();
    $co->name = $this->getName($line);
    $rem = trim(str_replace($co->name, '', $line));

    $this->setDataType($co, $rem);

    $this->setNotNull($co);
    $this->setPrimary($co);
    $this->setDefault($co);
    return $co;
  }

  protected function getCreatesqlLines($table)
  {
    $res  = $this->recordObj->execute(sprintf(self::TABLE_COLUMNS, $table));

    $sql  = substr(strpbrk($res[0]->sql, '('), 0);
    $sqls = explode(',', substr($sql, 1, strlen($sql) - 2));
    return array_map('trim', $sqls);
  }

  protected function setDataType($co, $rem)
  {
    $type = $this->getType($co, $rem);

    if (stripos($type, 'INT') !== false) {
      $co->type = Sabel_DB_Schema_Type::INT;
      $co->setNumericRange(strtolower($type));
    } else if ($text = strpbrk($type, '(')) {
      $co->type = Sabel_DB_Schema_Type::STRING;
      $co->max = substr($text, 1, strlen($text) - 2);
    } else if (stripos($type, 'TEXT') !== false) {
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

  protected function getName($line)
  {
    return substr($line, 0, strpos($line, ' '));
  }

  protected function getType($co, $rem)
  {
    $tmp = substr($rem, 0, strpos($rem, ' '));
    $type = ($tmp === '') ? $rem : $tmp;
    $this->constraint = trim(str_replace($type, '', $rem));

    $co->increment = (stripos($rem, 'INTEGER PRIMARY KEY') !== false);
    return $type;
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

  protected function setPrimary($co)
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
