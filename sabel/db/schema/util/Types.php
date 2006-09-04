<?php

interface Sender
{
  public function add($chainObj);
  public function send($columnObj, $type);
}

class TypeInt implements Sender
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
      $co->type = Type::INT;
      Type::setRange($co, $type);
    } else {
      $this->next->send($co, $type);
    }
  }
}

class TypeStr implements Sender
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
          $co->type = Type::STRING;
          $co->max  = ($text !== false) ? substr($text, 1, strlen($text) - 2) : 256;
          break;
        }
      }
    } else {
      $this->next->send($co, $type);
    }
  }
}

class TypeText implements Sender
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
      $co->type = Type::TEXT;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class TypeTime implements Sender
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
      $co->type = Type::TIMESTAMP;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class TypeByte implements Sender
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
      $co->type = Type::BLOB;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class TypeOther implements Sender
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
