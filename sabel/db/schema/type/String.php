<?php

class Sabel_DB_Schema_Type_String implements Sabel_DB_Schema_Type_Sender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $types = array('varchar', 'char', 'character varying' , 'character');

    if (in_array($type, $types)) {
      $co->type = Sabel_DB_Const::STRING;
    } else {
      $this->next->send($co, $type);
    }
  }
}
