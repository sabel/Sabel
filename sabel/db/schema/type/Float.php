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
    if ($type === 'float') {
      $co->type = Sabel_DB_Schema_Const::FLOAT;
      $co->max  =  3.4028235E38;
      $co->min  = -3.4028235E38;
    } else {
      $this->next->send($co, $type);
    }
  }
}
