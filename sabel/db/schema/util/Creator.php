<?php

class Schema_Util_Creator
{
  protected $colLine = '';

  public function create($createSQL)
  {
    $lines = $this->splitCreateSQL($createSQL);

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
        break;
      }

      $this->setDataType($co, $rem);

      if ($this->colLine === '') {
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

  protected function splitCreateSQL($sql)
  {
    $sql   = substr(strpbrk($sql, '('), 0);
    $lines = explode(',', substr($sql, 1, -1));
    return array_map('strtolower', array_map('trim', $lines));
  }

  protected function setDataType($co, $attributes)
  {
    $tmp  = substr($attributes, 0, strpos($attributes, ' '));
    $type = ($tmp === '') ? $attributes : $tmp;
    $this->colLine = substr($attributes, strlen($type));

    if (!$this->isString($co, $type)) Sabel_DB_Schema_TypeSetter::send($co, $type);

    $pri  = (strpos($attributes, 'integer primary key') !== false);
    $pri2 = (strpos($attributes, 'integer not null primary key') !== false);

    $co->increment = ($pri || $pri2);
  }

  protected function isString($co, $type)
  {
    $types = array('varchar', 'char', 'character');

    foreach ($types as $sType) {
      if (strpos($type, $sType) !== false) {
        $length   = strpbrk($type, '(');
        $co->type = Sabel_DB_Const::STRING;
        $co->max  = ($length!== false) ? (int)substr($length, 1, -1) : 255;
        return true;
      }
    }
    return false;
  }

  protected function setNotNull($co)
  {
    $colLine = $this->colLine;

    $co->notNull   = (strpos($colLine, 'not null') !== false);
    $this->colLine = str_replace('not null', '', $colLine);
  }

  protected function setPrimary($co)
  {
    $colLine = $this->colLine;

    if ($colLine === '') {
      $co->primary = false;
      $co->default = null;
      return true;
    } else {
      $co->primary   = (strpos($colLine, 'primary key') !== false);
      $this->colLine = str_replace('primary key', '', $colLine);
      return false;
    }
  }

  protected function setDefault($co)
  {
    $colLine = $this->colLine;

    if (strpos($colLine, 'default') !== false) {
      $default = trim(substr($colLine, 8));
      if (is_numeric($default)) {
        $co->default = (int)$default;
      } else if ($default === 'false' || $default === 'true') {
        $co->default = ($default === 'true');
      } else {
        $co->default = substr($default, 1, -1);
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
      $priCol = substr($line, 1, -1);
      $columns[$priCol]->primary = true;
    }
    return $columns;
  }
}
