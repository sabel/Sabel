<?php

class Dummy extends Sabel_DB_Model
{
  protected $connectName = 'default';

  public function __construct($mdlName)
  {
    $this->initialize($mdlName);
  }
}
