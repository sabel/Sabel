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

    if (in_array($type, $tArray)) {
      foreach ($tArray as $val) {
        if (stripos($type, $val) !== false) {
          $co->type = Sabel_DB_Schema_Type::STRING;
          $co->max  = ($text = strpbrk($type, '(')) ? substr($text, 1, strlen($text) - 2) : 256;
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

class CreateSchemaColumn
{
  protected $constraint = '';

  public function __construct($createSQL)
  {
    $sql   = substr(strpbrk($createSQL, '('), 0);
    $lines = explode(',', substr($sql, 1, strlen($sql) - 2));
    $lines = array_map('trim', $lines);

    $columns = array();
    foreach ($lines as $line) {
      $co = new Sabel_DB_Schema_Column();

      $split = explode(' ', $line);
      $name  = $split[0];

      $line = strtolower($line);

      $co->name = $name;
      $rem = trim(str_replace($name, '', $line));

      $this->setDataType($co, $rem);

      $this->setNotNull($co);
      $this->setPrimary($co);
      $this->setDefault($co);
      
      $columns[] = $co;
    }
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

    if (!isset($ts->increment)) {
      $co->increment = (strpos($rem, 'auto_increment') || strpos($rem, 'integer primary key'));
    }
  }

  protected function getType($co, $rem)
  {
    $tmp = substr($rem, 0, strpos($rem, ' '));
    $type = ($tmp === '') ? $rem : $tmp;
    $this->constraint = trim(str_replace($type, '', $rem));

    return $type;
  }

  protected function setNotNull($co)
  {
    if ($this->constraint === '') {
      $co->notNull = false;
    } else {
      $co->notNull = (strpos($this->constraint, 'not null') !== false);
      $this->constraint = str_replace('not null', '', $this->constraint);
    }
  }

  protected function setPrimary($co)
  {
    if ($this->constraint === '') {
      $co->primary = false;
    } else {
      $co->primary = (strpos($this->constraint, 'primary key') !== false);
      $this->constraint = str_replace('primary key', '', $this->constraint);
    }
  }

  protected function setDefault($co)
  {
    if ($this->constraint === '') {
      $co->default = null;
    } else {
      if (strpos($this->constraint, 'default') !== false) {
        $default = trim(substr($this->constraint, 8));
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
}

?>