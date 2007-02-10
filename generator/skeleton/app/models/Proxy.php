<?php

class Proxy extends Sabel_DB_Model
{
  public function __construct($mdlName)
  {
    $this->initialize($mdlName);
  }
}
