<?php

class Sabel_DB_Schema_Type_Other implements Sabel_DB_Schema_Type_Sender
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
