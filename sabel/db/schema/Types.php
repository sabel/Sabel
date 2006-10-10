<?php

interface Sabel_DB_Schema_TypeSender
{
  public function add($chain);
  public function send($columnObj, $type);
}

class Sabel_DB_Schema_TypeInt implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $tArray = array('integer', 'int', 'bigint', 'serial' , 'bigserial', 'int4',
                    'int8', 'smallint', 'tinyint', 'int3', 'int2', 'mediumint');

    if (in_array($type, $tArray)) {
      $co->type = Sabel_DB_Const::INT;

      switch($type) {
        case 'tinyint':
          $co->max =  127;
          $co->min = -128;
          break;
        case 'int2':
        case 'smallint':
          $co->max =  32767;
          $co->min = -32768;
          break;
        case 'int3':
        case 'mediumint':
          $co->max =  8388607;
          $co->min = -8388608;
          break;
        case 'int':
        case 'int4':
        case 'integer':
        case 'serial':
          $co->max =  2147483647;
          $co->min = -2147483648;
          break;
        case 'int8':
        case 'bigint':
        case 'bigserial':
          $co->max =  9223372036854775807;
          $co->min = -9223372036854775808;
          break;
      }
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeStr implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $tArray = array('varchar', 'char', 'character varying' , 'character');

    if (in_array($type, $tArray)) {
      $co->type = Sabel_DB_Const::STRING;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeText implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $tArray = array('text', 'mediumtext', 'tinytext');

    if (in_array($type, $tArray)) {
      $co->type = Sabel_DB_Const::TEXT;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeTime implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $tArray = array('timestamp', 'timestamp without time zone',
                    'datetime' , 'timestamp with time zone');

    if (in_array($type, $tArray)) {
      $co->type = Sabel_DB_Const::TIMESTAMP;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeFloat implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $tArray = array('float', 'double', 'double precision', 'real', 'float4', 'float8');

    if (in_array($type, $tArray)) {
      $co->type = Sabel_DB_Const::FLOAT;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeByte implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $tArray = array('blob', 'bytea', 'longblob', 'mediumblob');

    if (in_array($type, $tArray)) {
      $co->type = Sabel_DB_Const::BLOB;
    } else {
      $this->next->send($co, $type);
    }
  }
}

class Sabel_DB_Schema_TypeOther implements Sabel_DB_Schema_TypeSender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $co->type = 'undefined';
  }
}
