<?php

class Maker
{
  protected
    $colInfo = '',
    $lines   = '';

  public function __construct($createSQL)
  {
    $sql   = substr(strpbrk($createSQL, '('), 0);
    $lines = explode(',', substr($sql, 1, strlen($sql) - 2));
    $lines = array_map('trim', $lines);

    $this->lines = $lines;
  }

  public function getColumns()
  {
    $lines     = $this->lines;
    $constLine = '';

    $columns = array();
    foreach ($lines as $line) {
      $co = new ColumnLine();

      $line  = strtolower($line);
      $split = explode(' ', $line);
      $name  = $split[0];

      $rem = trim(substr($line, strlen($name)));

      if ($name === 'constraint') {
        $constLine  = $line;
        continue;
      }

      $this->setDataType($co, $rem);

      if ($this->colInfo === '') {
        $co->notNull = false;
        $co->primary = false;
        $co->default = null;
      } else {
        $this->setNotNull($co);
        if (!$this->setPrimary($co)) $this->setDefault($co);
      }

      $columns[$name] = $co->get();
    }

    if ($constLine !== '')
      $columns = $this->setConstraint($columns, $constLine);

    return $columns;
  }

  protected function setDataType($co, $rem)
  {
    $tmp = substr($rem, 0, strpos($rem, ' '));
    $type = ($tmp === '') ? $rem : $tmp;
    $this->colInfo = trim(substr($rem, strlen($type)));

    Setter::set($co, $type);

    $co->increment = (strpos($rem, 'auto_increment') || strpos($rem, 'serial') ||
                      strpos($rem, 'bigserial') || strpos($rem, 'integer primary key'));
  }

  protected function setNotNull($co)
  {
    $colInfo = $this->colInfo;

    $co->notNull   = (strpos($colInfo, 'not null') !== false);
    $this->colInfo = str_replace('not null', '', $colInfo);
  }

  protected function setPrimary($co)
  {
    $colInfo = $this->colInfo;

    if ($colInfo === '') {
      $co->primary = false;
      $co->default = null;
      return true;
    } else {
      $co->primary   = (strpos($colInfo, 'primary key') !== false);
      $this->colInfo = str_replace('primary key', '', $colInfo);
      return false;
    }
  }

  protected function setDefault($co)
  {
    $colInfo = $this->colInfo;

    if (strpos($colInfo, 'default') !== false) {
      $default = trim(substr($colInfo, 8));
      if (ctype_digit($default) || $default === 'false' || $default === 'true') {
        $co->default = $default;
      } else {
        $co->default = substr($default, 1, strlen($default) - 2);
      }
    } else {
      $co->default = null;
    }
  }

  protected function setConstraint($columns, $line)
  {
    if (strpos($line, 'primary key') !== false) {
      $line     = strpbrk($line, '(');
      $colName  = substr($line, 1, strlen($line) - 2);
      $column   = explode(',', $columns[$colName]);
      $position = count($column) - 2;

      $column[$position] = 'true';
      $columns[$colName] = join(',', $column);
      return $columns;
    }
  }
}

class ColumnLine
{
  protected $data = array();

  public function __set($key, $val)
  {
    if ($val === false) {
      $val = 'false';
    } else if (is_null($val)) {
      $val = 'null';
    } else if ($val === true) {
      $val = 'true';
    }

    $this->data[$key] = $val;
  }

  public function get()
  {
    return join(',', $this->data);
  }
}
