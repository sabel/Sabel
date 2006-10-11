<?php

class Sabel_DB_Schema_Type_Time implements Sabel_DB_Schema_Type_Sender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $types = array('timestamp', 'timestamp without time zone',
                   'datetime' , 'timestamp with time zone');

    if (in_array($type, $types)) {
      $co->type = Sabel_DB_Schema_Const::TIMESTAMP;
    } else {
      $this->next->send($co, $type);
    }
  }
}
