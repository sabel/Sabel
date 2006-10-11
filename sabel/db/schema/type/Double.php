<?php

class Sabel_DB_Schema_Type_Double implements Sabel_DB_Schema_Type_Sender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    if ($type === 'double') {
      $co->type = Sabel_DB_Schema_Const::DOUBLE;
      $co->max  =  1.79769E308;
      $co->min  = -1.79769E308;
    } else {
      $this->next->send($co, $type);
    }
  }
}
