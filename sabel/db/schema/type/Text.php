<?php

class Sabel_DB_Schema_Type_Text implements Sabel_DB_Schema_Type_Sender
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