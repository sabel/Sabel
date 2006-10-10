<?php

class Sabel_DB_Basic extends Sabel_DB_Mapper
{
  public function __construct($table = null)
  {
    if (isset($table)) $this->table = $table;
    parent::__construct();
  }
}