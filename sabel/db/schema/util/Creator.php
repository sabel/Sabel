<?php

class ColumnValueObject
{
  private $data = array();

  public function __set($key, $val)
  {
    $this->data[$key] = $val;
  }

  public function __get($key)
  {
    return $this->data[$key];
  }
}

class CreateSchemaColumn
{
  protected $colInfo = '';

  public function __construct($createSQL)
  {
    $sql   = substr(strpbrk($createSQL, '('), 0);
    $lines = explode(',', substr($sql, 1, strlen($sql) - 2));
    $lines = array_map('trim', $lines);

    $columns = array();
    foreach ($lines as $line) {
      $co = new ColumnValueObject();

      $split = explode(' ', $line);
      $name  = $split[0];

      if ($name === 'constraint') {
        $constraint = true;
        $constLine  = $line;
        continue;        
      }

      $line = strtolower($line);

      $co->name = $name;
      $rem = trim(str_replace($name, '', $line));

      $this->setDataType($co, $rem);

      $this->setNotNull($co);
      $this->setPrimary($co);
      $this->setDefault($co);
      
      $columns[$name] = $co;
    }
    if ($constraint) $this->setConstraint($columns, $constLine);
    $this->columns = $columns;
  }

  public function getColumns()
  {
    return $this->columns;
  }

  protected function setDataType($co, $rem)
  {
    $type = $this->getType($co, $rem);
    $ts = new Sabel_DB_Schema_TypeSet($co, $type);

    $co->increment = (strpos($rem, 'auto_increment') || strpos($rem, 'serial') ||
                      strpos($rem, 'bigserial') || strpos($rem, 'integer primary key'));
  }

  protected function getType($co, $rem)
  {
    $tmp = substr($rem, 0, strpos($rem, ' '));
    $type = ($tmp === '') ? $rem : $tmp;
    $this->colInfo = trim(str_replace($type, '', $rem));

    return $type;
  }

  protected function setNotNull($co)
  {
    if ($this->colInfo === '') {
      $co->notNull = false;
    } else {
      $co->notNull = (strpos($this->colInfo, 'not null') !== false);
      $this->colInfo = str_replace('not null', '', $this->colInfo);
    }
  }

  protected function setPrimary($co)
  {
    if ($this->colInfo === '') {
      $co->primary = false;
    } else {
      $co->primary = (strpos($this->colInfo, 'primary key') !== false);
      $this->colInfo = str_replace('primary key', '', $this->colInfo);
    }
  }

  protected function setDefault($co)
  {
    if ($this->colInfo === '') {
      $co->default = null;
    } else {
      if (strpos($this->colInfo, 'default') !== false) {
        $default = trim(substr($this->colInfo, 8));
        if (ctype_digit($default) || $default === 'false' || $default === 'true') {
          $co->default = $default;
        } else {
          $co->default = substr($default, 1, strlen($default) - 2);
        }
      } else {
        $co->default = null;
      }
    }
  }

  protected function setConstraint($columns, $line)
  {
    if (strpos($line, 'primary key') !== false) {
      $line    = strpbrk($line, '(');
      $colName = substr($line, 1, strlen($line) - 2);
      $columns[$colName]->primary = true;
    }
  }
}
