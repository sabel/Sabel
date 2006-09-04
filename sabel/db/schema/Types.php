<?php

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
    $tArray = array('integer', 'int', 'bigint', 'serial' , 'bigserial', 'int4',
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
