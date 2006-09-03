<?php

class Sabel_DB_Schema_TypeSet
{
  public function __construct($co, $type)
  {
    if ($type === 'boolean' || $type === 'bool') {
      $co->type = Sabel_DB_Schema_Type::BOOL;
    } else if ($type === 'date') {
      $co->type = Sabel_DB_Schema_Type::DATE;
    } else if ($type === 'time') {
      $co->type = Sabel_DB_Schema_Type::TIME;
    } else {
      $tInt   = new Sabel_DB_Schema_TypeInt();
      $tStr   = new Sabel_DB_Schema_TypeStr();
      $tText  = new Sabel_DB_Schema_TypeText();
      $tTime  = new Sabel_DB_Schema_TypeTime();
      $tByte  = new Sabel_DB_Schema_TypeByte();
      $tOther = new Sabel_DB_Schema_TypeOther();

      $tInt->add($tStr);
      $tStr->add($tText);
      $tText->add($tTime);
      $tTime->add($tByte);
      $tByte->add($tOther);

      $tInt->send($co, $type);
    }
  }
}

interface Sabel_DB_Schema_TypeSender
{
  public function add($chainObj);
  public function send($columnObj, $type);
}

class Sabel_DB_Schema_TypeInt implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chainObj)
  {
    $this->next = $chainObj;
  }

  public function send($co, $type)
  {
    $tArray = array('integer', 'bigint', 'serial' , 'bigserial', 'int4',
                    'int8', 'smallint', 'tinyint', 'int3', 'int2', 'mediumint');

    if (in_array($type, $tArray)) {
      $co->type = Sabel_DB_Schema_Type::INT;
      Sabel_DB_Schema_Type::setRange($co, $type);
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeStr implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chainObj)
  {
    $this->next = $chainObj;
  }

  public function send($co, $type)
  {
    $tArray = array('varchar', 'char', 'character varying' , 'character');
    $text   = strpbrk($type, '(');

    if (in_array($type, $tArray) || $text !== false) {
      foreach ($tArray as $val) {
        if (stripos($type, $val) !== false) {
          $co->type = Sabel_DB_Schema_Type::STRING;
          $co->max  = ($text !== false) ? substr($text, 1, strlen($text) - 2) : 256;
          break;
        }
      }
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeText implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chainObj)
  {
    $this->next = $chainObj;
  }

  public function send($co, $type)
  {
    $tArray = array('text', 'mediumtext', 'tinytext');

    if (in_array($type, $tArray)) {
      $co->type = Sabel_DB_Schema_Type::TEXT;
      $co->max  = 65536;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeTime implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chainObj)
  {
    $this->next = $chainObj;
  }

  public function send($co, $type)
  {
    $tArray = array('timestamp', 'timestamp without time zone',
                    'datetime' , 'timestamp with time zone');

    if (in_array($type, $tArray)) {
      $co->type = Sabel_DB_Schema_Type::TIMESTAMP;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeByte implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chainObj)
  {
    $this->next = $chainObj;
  }

  public function send($co, $type)
  {
    $tArray = array('blob', 'bytea', 'longblob', 'mediumblob');

    if (in_array($type, $tArray)) {
      $co->type = Sabel_DB_Schema_Type::BLOB;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeOther implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chainObj)
  {
    $this->next = $chainObj;
  }

  public function send($co, $type)
  {
    $co->type = 'undefined';
  }
}

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
      //$co = new Sabel_DB_Schema_Column();
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

    if (!isset($co->increment)) {
      $co->increment = (strpos($rem, 'auto_increment') || strpos($rem, 'integer primary key'));
    }
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

?>