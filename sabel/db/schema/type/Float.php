<?php

class Sabel_DB_Schema_Type_Float implements Sabel_DB_Schema_Type_Sender
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