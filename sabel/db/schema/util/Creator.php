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
    foreach ($lines as $line) {
      $vo    = new Sabel_DB_Schema_Column();
      $split = explode(' ', $line);
      $name  = $split[0];
      $rem   = trim(substr($line, strlen($name)));

      $vo->name = $name;

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

    if ($constLine !== '') $columns = $this->setConstraint($columns, $constLine);
    return $columns;
  }

  protected function setDataType($vo, $rem)
  {
    $tmp = substr($rem, 0, strpos($rem, ' '));
    $type = ($tmp === '') ? $rem : $tmp;
    $this->colInfo = trim(substr($rem, strlen($type)));

    Sabel_DB_Schema_TypeSetter::send($vo, $type);

    $pri  = (strpos($rem, 'integer primary key') !== false);
    $pri2 = (strpos($rem, 'integer not null primary key') !== false);

    $vo->increment = ($pri || $pri2);
  }

  protected function setNotNull($vo)
  {
    $colInfo = $this->colInfo;

    $vo->notNull   = (strpos($colInfo, 'not null') !== false);
    $this->colInfo = str_replace('not null', '', $colInfo);
  }

  protected function setPrimary($vo)
  {
    $colInfo = $this->colInfo;

    if ($colInfo === '') {
      $vo->primary = false;
      $vo->default = null;
      return true;
    } else {
      $vo->primary   = (strpos($colInfo, 'primary key') !== false);
      $this->colInfo = str_replace('primary key', '', $colInfo);
      return false;
    }
  }

  protected function setDefault($vo)
  {
    $colInfo = $this->colInfo;

    if (strpos($colInfo, 'default') !== false) {
      $default = trim(substr($colInfo, 8));
      if (ctype_digit($default) || $default === 'false' || $default === 'true') {
        $vo->default = $default;
      } else {
        $vo->default = substr($default, 1, strlen($default) - 2);
      }
    } else {
      $vo->default = null;
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
