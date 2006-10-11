<?php

class Sabel_DB_Schema_Type_Byte implements Sabel_DB_Schema_Type_Sender
{
  private $next = null;

  public function add($chain)
  {
    $this->next = $chain;
  }

  public function send($co, $type)
  {
    $types = array('blob', 'bytea', 'longblob', 'mediumblob');

    if (in_array($type, $types)) {
      $co->type = Sabel_DB_Schema_Const::BLOB;
    } else {
      $this->next->send($co, $type);
    }
  }
}
