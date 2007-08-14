<?php

abstract class Sabel_Bus_Processor
{
  public $name;
  
  public function __construct($name = null)
  {
    if ($name === null || $name === "") {
      throw new Sabel_Exception_Runtime("name must be set");
    }
    
    $this->name = $name;
  }
  
  abstract public function execute($bus);
}
