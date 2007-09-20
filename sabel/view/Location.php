<?php

abstract class Sabel_View_Location
{
  protected $name = "";
  protected $destination = null;
  
  public function __construct($name, Sabel_Destination $destination)
  {
    $this->name = $name;
    $this->destination = $destination;
  }
  
  public function getName()
  {
    return $this->name;
  }
}
