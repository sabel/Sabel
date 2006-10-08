<?php

class Schema_Util_Creator
{
  protected $colInfo = '';

  public function create($createSQL)
  {
    $sql   = substr(strpbrk($createSQL, '('), 0);
    $lines = explode(',', substr($sql, 1, strlen($sql) - 2));
    $lines = array_map('strtolower', array_map('trim', $lines));

    $constLine = '';

    $columns = array();
    foreach ($lines as $key => $line) {
      $co    = new Sabel_DB_Schema_Column();
      $split = explode(' ', $line);
      $name  = $split[0];
      $rem   = trim(substr($line, strlen($name)));

      $co->name = $name;

      if (strpos($line, 'primary key') !== false && strpbrk($line, '(') !== false) {
        $constLine = $line . ',' . $lines[$key + 1];
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

      $columns[$name] = $co;
    }

    if ($constLine !== '') $columns = $this->setConstraint($columns, $constLine);
    return $columns;
  }

  protected function setDataType($co, $rem)
  {
    $tmp = substr($rem, 0, strpos($rem, ' '));
    $type = ($tmp === '') ? $rem : $tmp;
    $this->colInfo = trim(substr($rem, strlen($type)));

    Sabel_DB_Schema_TypeSetter::send($co, $type);

    $pri  = (strpos($rem, 'integer primary key') !== false);
    $pri2 = (strpos($rem, 'integer not null primary key') !== false);

    $co->increment = ($pri || $pri2);
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
      if (is_numeric($default)) {
        $co->default = (int)$default;
      } else if ($default === 'false' || $default === 'true') {
        $co->default = ($default === 'true');
      } else {
        $co->default = substr($default, 1, strlen($default) - 2);
      }
    } else {
      $co->default = null;
    }
  }

  protected function setConstraint($columns, $line)
  {
    $line = strpbrk($line, '(');
    if (strpbrk($line, ',') !== false) {
      $parts = explode(',', $line);
      foreach ($parts as $key => $part) $parts[$key] = str_replace(array('(', ')'), '', $part);
      foreach ($parts as $key) $columns[$key]->primary = true;
    } else {
      $priCol = substr($line, 1, strlen($line) - 2);
      $columns[$priCol]->primary = true;
    }
    return $columns;
  }
}
