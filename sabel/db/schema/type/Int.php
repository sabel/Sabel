<?php

class Sabel_DB_Schema_Type_Int implements Sabel_DB_Schema_Type_Sender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $types = array('integer', 'int', 'bigint', 'serial' , 'bigserial', 'int4',
                   'int8', 'smallint', 'tinyint', 'int3', 'int2', 'mediumint');

    if (in_array($type, $types)) {
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
