<?php

class Schema_Util_Creator
{
  protected $colInfo = '';

  public function create($table, $createSQL)
  {
    $sql   = substr(strpbrk($createSQL, '('), 0);
    $lines = explode(',', substr($sql, 1, strlen($sql) - 2));
    $lines = array_map('strtolower', array_map('trim', $lines));

    $constLine = '';

    $columns = array();
    foreach ($lines as $line) {
      $vo    = new ValueObject();
      $split = explode(' ', $line);
      $name  = $split[0];
      $rem   = trim(substr($line, strlen($name)));

      if ($name === 'constraint') {
        $constLine = $line;
        continue;
      }

      $this->setDataType($vo, $rem);

      if ($this->colInfo === '') {
        $vo->notNull = false;
        $vo->primary = false;
        $vo->default = null;
      } else {
        $this->setNotNull($vo);
        if (!$this->setPrimary($vo)) $this->setDefault($vo);
      }

      $columns[$name] = $vo;
    }

    if ($constLine !== '')
      $columns = $this->setConstraint($columns, $constLine);

    return new Sabel_DB_Schema_Table($table, $columns);
  }

  protected function setDataType($co, $rem)
  {
    $tmp = substr($rem, 0, strpos($rem, ' '));
    $type = ($tmp === '') ? $rem : $tmp;
    $this->colInfo = trim(substr($rem, strlen($type)));

    Sabel_DB_Schema_TypeSetter::send($co, $type);

    $auto   = (strpos($rem, 'auto_increment') !== false);
    $seq    = (strpos($rem, 'serial') !== false);
    $intpri = (strpos($rem, 'integer primary key') !== false);

    $co->increment = ($auto || $seq || $intpri);
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
      $line   = strpbrk($line, '(');
      $priCol = substr($line, 1, strlen($line) - 2);

      $columns[$priCol]->primary = true;
      return $columns;
    }
  }
}
